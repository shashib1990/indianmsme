<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PickupStore;
use App\Models\State;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PickupStoreController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $search = $request->get('search');
        $status = $request->get('status');
        $state_id = $request->get('state_id');
        $district_id = $request->get('district_id');
        $owner_type_filter = $request->get('owner_type'); // Seller or Sub Seller

        $query = PickupStore::query()->with(['stateRelation', 'districtRelation', 'owner']);

        // Both Seller and Sub Seller can only see/manage their own stores
        $query->where('owner_id', $user->id);

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pickup_store_code', 'like', "%{$search}%")
                  ->orWhere('pickup_store_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($state_id) {
            $query->where('state_id', $state_id);
        }

        if ($district_id) {
            $query->where('district_id', $district_id);
        }

        if (!$isSubSeller && $owner_type_filter) {
            $query->where('owner_type', $owner_type_filter);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $pickupStores = $query->orderBy($sort, $order)->paginate(15)->withQueryString();

        $states = State::active()->orderBy('name')->get();
        // Load districts if a state is selected to populate filter
        $districts = $state_id ? District::where('state_id', $state_id)->active()->orderBy('name')->get() : collect();

        return view('vendor.pickup-stores.index', compact(
            'pickupStores',
            'search',
            'status',
            'state_id',
            'district_id',
            'owner_type_filter',
            'states',
            'districts',
            'isSubSeller',
            'sort',
            'order'
        ));
    }

    public function create()
    {
        $states = State::active()->orderBy('name')->get();
        return view('vendor.pickup-stores.create', compact('states'));
    }

    public function store(Request $request)
    {
        $rules = [
            'pickup_store_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:15'],
            'address' => ['required', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'state' => ['required', 'exists:states,id'],
            'district' => ['required', 'exists:districts,id'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\StoreStatus::class)],
            'can_receive_payment_and_dispatch' => ['required', 'in:0,1'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->input('can_receive_payment_and_dispatch') == '1') {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $rules['account_holder_name'] = ['required', 'string', 'max:255'];
            $rules['bank_account_number'] = ['required', 'string', 'max:255'];
            $rules['ifsc_code'] = ['required', 'string', 'max:20'];
            $rules['bank_name'] = ['required', 'string', 'max:255'];
            $rules['branch_name'] = ['required', 'string', 'max:255'];
        }

        $messages = [
            'mobile.regex' => 'The mobile number must be exactly 10 numeric digits.',
            'pincode.regex' => 'The pincode must be exactly 6 numeric digits.',
        ];

        $request->validate($rules, $messages);

        $user = Auth::guard('vendor')->user();
        $ownerType = $user->parent_id === null ? 'Seller' : 'Sub Seller';

        DB::beginTransaction();
        try {
            $code = PickupStore::generatePickupStoreCode();

            $store = new PickupStore();
            $store->owner_type = $ownerType;
            $store->owner_id = $user->id;
            $store->pickup_store_code = $code;
            $store->pickup_store_name = $request->pickup_store_name;
            $store->contact_person = $request->contact_person;
            $store->mobile = $request->mobile;
            $store->email = $request->email;
            $store->gst_number = $request->gst_number;
            $store->address = $request->address;
            $store->landmark = $request->landmark;
            $store->state_id = $request->state;
            $store->district_id = $request->district;
            $store->pincode = $request->pincode;
            $store->status = $request->status;
            
            $canReceive = $request->input('can_receive_payment_and_dispatch') == '1';
            $store->can_receive_payment_and_dispatch = $canReceive;
            
            if ($request->filled('password')) {
                $store->password = Hash::make($request->password);
            } else {
                $store->password = null;
            }

            if ($canReceive) {
                $store->account_holder_name = $request->account_holder_name;
                $store->bank_account_number = $request->bank_account_number;
                $store->ifsc_code = $request->ifsc_code;
                $store->bank_name = $request->bank_name;
                $store->branch_name = $request->branch_name;
            } else {
                $store->account_holder_name = null;
                $store->bank_account_number = null;
                $store->ifsc_code = null;
                $store->bank_name = null;
                $store->branch_name = null;
            }

            $store->save();

            DB::commit();
            return redirect()->route('vendor.pickup-stores.index')->with('success', 'Pickup Store created successfully. Code: ' . $code);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating Pickup Store: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $store = PickupStore::with(['stateRelation', 'districtRelation', 'owner'])->findOrFail($id);

        // Security check: Only the store owner can access
        if ($store->owner_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('vendor.pickup-stores.show', compact('store'));
    }

    public function edit($id)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $store = PickupStore::findOrFail($id);

        // Security check: Only the store owner can access
        if ($store->owner_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $states = State::active()->orderBy('name')->get();
        $districts = District::where('state_id', $store->state_id)->active()->orderBy('name')->get();

        return view('vendor.pickup-stores.edit', compact('store', 'states', 'districts'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $store = PickupStore::findOrFail($id);

        // Security check: Only the store owner can access
        if ($store->owner_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'pickup_store_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:15'],
            'address' => ['required', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'state' => ['required', 'exists:states,id'],
            'district' => ['required', 'exists:districts,id'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\StoreStatus::class)],
            'can_receive_payment_and_dispatch' => ['required', 'in:0,1'],
        ];

        $canReceive = $request->input('can_receive_payment_and_dispatch') == '1';

        if ($canReceive) {
            if (!$store->password) {
                $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            } else {
                $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
            }
            $rules['account_holder_name'] = ['required', 'string', 'max:255'];
            $rules['bank_account_number'] = ['required', 'string', 'max:255'];
            $rules['ifsc_code'] = ['required', 'string', 'max:20'];
            $rules['bank_name'] = ['required', 'string', 'max:255'];
            $rules['branch_name'] = ['required', 'string', 'max:255'];
        }

        $messages = [
            'mobile.regex' => 'The mobile number must be exactly 10 numeric digits.',
            'pincode.regex' => 'The pincode must be exactly 6 numeric digits.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $store->pickup_store_name = $request->pickup_store_name;
            $store->contact_person = $request->contact_person;
            $store->mobile = $request->mobile;
            $store->email = $request->email;
            $store->gst_number = $request->gst_number;
            $store->address = $request->address;
            $store->landmark = $request->landmark;
            $store->state_id = $request->state;
            $store->district_id = $request->district;
            $store->pincode = $request->pincode;
            $store->status = $request->status;
            
            $store->can_receive_payment_and_dispatch = $canReceive;
            if ($request->filled('password')) {
                $store->password = Hash::make($request->password);
            }

            if ($canReceive) {
                $store->account_holder_name = $request->account_holder_name;
                $store->bank_account_number = $request->bank_account_number;
                $store->ifsc_code = $request->ifsc_code;
                $store->bank_name = $request->bank_name;
                $store->branch_name = $request->branch_name;
            } else {
                $store->account_holder_name = null;
                $store->bank_account_number = null;
                $store->ifsc_code = null;
                $store->bank_name = null;
                $store->branch_name = null;
            }

            $store->save();

            DB::commit();
            return redirect()->route('vendor.pickup-stores.index')->with('success', 'Pickup Store updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating Pickup Store: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $store = PickupStore::findOrFail($id);

        if ($store->owner_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\StoreStatus::class)],
        ]);

        $store->status = $request->status;
        $store->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $store->status
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $store = PickupStore::findOrFail($id);

        if ($store->owner_id !== $user->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        if (!$store->can_receive_payment_and_dispatch) {
            return back()->with('error', 'This store is seller-managed and does not support password resets.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $store->password = Hash::make($request->password);
        $store->save();

        return back()->with('success', 'Password reset successfully for Pickup Store: ' . $store->pickup_store_code);
    }

    public function destroy($id)
    {
        $user = Auth::guard('vendor')->user();
        $isSubSeller = $user->parent_id !== null;

        $store = PickupStore::findOrFail($id);

        if ($store->owner_id !== $user->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        $store->delete();

        return redirect()->route('vendor.pickup-stores.index')->with('success', 'Pickup Store deleted successfully.');
    }
}
