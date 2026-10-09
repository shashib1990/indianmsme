<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SellerProfile;
use App\Models\Product;
use App\Models\SubSellerProduct;
use App\Models\Category;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubSellerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 15);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');

        $query = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->with(['sellerProfile']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('sellerProfile', function ($sq) use ($search) {
                        $sq->where('company_name', 'like', "%{$search}%")
                            ->orWhere('seller_id', 'like', "%{$search}%");
                    });
            });
        }

        $subSellers = $query->orderBy($sort, $order)->paginate($limit)->withQueryString();

        return view('vendor.sub-sellers.index', compact('subSellers', 'search', 'limit', 'sort', 'order'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $states = State::active()->orderBy('name')->get();
        return view('vendor.sub-sellers.create', compact('categories', 'states'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'in:Company,LLP,Proprietorship Firm,Other'],
            'gst_number' => ['nullable', 'string', 'min:15', 'max:15'],
            'category_id' => ['required', 'exists:categories,id'],
            'authorized_rep_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:15'],
            'state' => ['required', 'exists:states,id'],
            'district' => ['required', 'exists:districts,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Generate a unique seller ID for sub-seller using same sequence
        $sellerId = \App\Models\SellerProfile::generateSellerId();

        DB::beginTransaction();
        try {
            // Create user
            $subSeller = new User();
            $subSeller->name = $request->company_name;
            $subSeller->email = $request->email;
            $subSeller->mobile = $request->mobile;
            $subSeller->password = Hash::make($request->password);
            $subSeller->role = 'seller';
            $subSeller->status = '1';
            $subSeller->parent_id = auth('vendor')->id(); // Link to parent

            // Hold details for hook
            $subSeller->company_name = $request->company_name;
            $subSeller->company_type = $request->company_type;
            $subSeller->gst_number = $request->gst_number;
            $subSeller->authorized_rep_name = $request->authorized_rep_name;
            $subSeller->state_id = $request->state;
            $subSeller->district_id = $request->district;
            $subSeller->state = null;
            $subSeller->district = null;
            $subSeller->category_id = $request->category_id;
            $subSeller->seller_id = $sellerId;
            $subSeller->kyc_status = 'PENDING';
            $subSeller->allow_own_products = 'No';

            // Bank details
            $subSeller->account_holder_name = $request->account_holder_name;
            $subSeller->bank_account_number = $request->bank_account_number;
            $subSeller->ifsc_code = $request->ifsc_code;
            $subSeller->bank_name = $request->bank_name;
            $subSeller->branch_name = $request->branch_name;

            $subSeller->save();

            // Role attachment removed per USER_REQUEST

            DB::commit();
            return redirect()->route('vendor.sub-sellers.index')->with('success', 'Sub-seller created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating sub-seller: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);
        $categories = Category::active()->orderBy('name')->get();
        $states = State::active()->orderBy('name')->get();
        return view('vendor.sub-sellers.edit', compact('subSeller', 'categories', 'states'));
    }

    public function update(Request $request, $id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'in:Company,LLP,Proprietorship Firm,Other'],
            'gst_number' => ['nullable', 'string', 'min:15', 'max:15'],
            'category_id' => ['required', 'exists:categories,id'],
            'authorized_rep_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:15'],
            'state' => ['required', 'exists:states,id'],
            'district' => ['required', 'exists:districts,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $subSeller->name = $request->company_name;
            $subSeller->email = $request->email;
            $subSeller->mobile = $request->mobile;
            if ($request->password) {
                $subSeller->password = Hash::make($request->password);
            }

            // Update profile fields
            $profile = $subSeller->sellerProfile;
            $profile->company_name = $request->company_name;
            $profile->company_type = $request->company_type;
            $profile->gst_number = $request->gst_number;
            $profile->authorized_rep_name = $request->authorized_rep_name;
            $profile->state_id = $request->state;
            $profile->district_id = $request->district;
            $profile->state = null;
            $profile->district = null;
            $profile->category_id = $request->category_id;
            $profile->allow_own_products = 'No';

            // Bank details
            $profile->account_holder_name = $request->account_holder_name;
            $profile->bank_account_number = $request->bank_account_number;
            $profile->ifsc_code = $request->ifsc_code;
            $profile->bank_name = $request->bank_name;
            $profile->branch_name = $request->branch_name;

            $profile->save();
            $subSeller->save();

            DB::commit();
            return redirect()->route('vendor.sub-sellers.index')->with('success', 'Sub-seller details updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating sub-seller: ' . $e->getMessage());
        }
    }

    public function products(Request $request, $id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        $search = $request->get('search');

        // Parent's own products that are APPROVED
        $productQuery = Product::where('seller_id', auth('vendor')->id())
            ->where('status', 'Approved')
            ->orderBy('product_name');

        if ($search) {
            $productQuery->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $productQuery->paginate(15)->withQueryString();

        // Fetch this sub-seller's custom margin overrides, keyed by product_id
        $customizations = \Illuminate\Support\Facades\DB::table('sub_seller_products')
            ->where('sub_seller_id', $subSeller->id)
            ->get()
            ->keyBy('product_id');

        return view('vendor.sub-sellers.products', compact('subSeller', 'products', 'search', 'customizations'));
    }

    public function assignProduct(Request $request, $id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'custom_margin_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Verify product belongs to parent and is approved
        $product = Product::where('seller_id', auth('vendor')->id())
            ->where('status', 'Approved')
            ->findOrFail($request->product_id);

        $margin = $request->custom_margin_percentage;
        $finalPrice = $product->seller_price_incl_gst + ($product->seller_price_incl_gst * $margin / 100);

        if ($finalPrice > $product->mrp) {
            return back()->withInput()->with('error', 'The calculated price (₹' . number_format($finalPrice, 2) . ') with the specified margin exceeds the MRP (₹' . number_format($product->mrp, 2) . ').');
        }

        // Run pricing calculations
        $pricing = \App\Services\PricingService::calculate(
            (float) $finalPrice,
            (float) $product->gst_rate,
            (float) $product->mrp,
            $product->admin_margin_percentage,
            $product->ddc_margin_percentage,
            $product->mbp_percentage
        );

        SubSellerProduct::updateOrCreate(
            [
                'sub_seller_id' => $subSeller->id,
                'product_id' => $product->id,
            ],
            array_merge([
                'margin' => $margin,
                'final_price' => $finalPrice,
            ], $pricing)
        );

        return back()->with('success', 'Product assigned successfully with custom margin.');
    }

    public function bulkAssignProducts(Request $request, $id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'custom_margin_percentage' => 'required|numeric|min:0|max:100',
        ]);

        // Pre-validate all selected products
        foreach ($request->product_ids as $pId) {
            $product = Product::where('seller_id', auth('vendor')->id())
                ->where('status', 'Approved')
                ->find($pId);

            if ($product) {
                $margin = $request->custom_margin_percentage;
                $finalPrice = $product->seller_price_incl_gst + ($product->seller_price_incl_gst * $margin / 100);

                if ($finalPrice > $product->mrp) {
                    return back()->withInput()->with('error', 'Calculated price for "' . $product->product_name . '" (₹' . number_format($finalPrice, 2) . ') exceeds its MRP (₹' . number_format($product->mrp, 2) . '). Bulk assignment aborted.');
                }
            }
        }

        foreach ($request->product_ids as $pId) {
            $product = Product::where('seller_id', auth('vendor')->id())
                ->where('status', 'Approved')
                ->find($pId);

            if ($product) {
                $margin = $request->custom_margin_percentage;
                $finalPrice = $product->seller_price_incl_gst + ($product->seller_price_incl_gst * $margin / 100);

                // Run pricing calculations
                $pricing = \App\Services\PricingService::calculate(
                    (float) $finalPrice,
                    (float) $product->gst_rate,
                    (float) $product->mrp,
                    $product->admin_margin_percentage,
                    $product->ddc_margin_percentage,
                    $product->mbp_percentage
                );

                SubSellerProduct::updateOrCreate(
                    [
                        'sub_seller_id' => $subSeller->id,
                        'product_id' => $product->id,
                    ],
                    array_merge([
                        'margin' => $margin,
                        'final_price' => $finalPrice,
                    ], $pricing)
                );
            }
        }

        return back()->with('success', 'Selected products assigned successfully.');
    }

    public function removeProduct($id, $productId)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        SubSellerProduct::where('sub_seller_id', $subSeller->id)
            ->where('product_id', $productId)
            ->delete();

        return back()->with('success', 'Product assignment removed.');
    }

    public function toggleActiveStatus($id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        if ($subSeller->status === '1') {
            $subSeller->status = '0';
            $subSeller->save();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => '0',
                    'status_label' => 'Inactive',
                    'badge_class' => 'bg-danger-subtle text-danger',
                    'message' => 'Sub-seller deactivated successfully.'
                ]);
            }

            return back()->with('success', 'Sub-seller deactivated successfully.');
        } else {
            $subSeller->status = '1';
            $subSeller->save();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'status' => '1',
                    'status_label' => 'Active',
                    'badge_class' => 'bg-success-subtle text-success',
                    'message' => 'Sub-seller activated successfully.'
                ]);
            }

            return back()->with('success', 'Sub-seller activated successfully.');
        }
    }

    public function destroy($id)
    {
        $subSeller = User::whereHas('sellerProfile', function ($q) {
            $q->where('parent_id', auth('vendor')->id());
        })->findOrFail($id);

        // Remove product assignments
        SubSellerProduct::where('sub_seller_id', $subSeller->id)->delete();

        // Remove profile (soft delete or simply delete user if safe)
        $subSeller->sellerProfile()->delete();
        $subSeller->delete();

        return back()->with('success', 'Sub-seller account deleted successfully.');
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string|in:activate,deactivate,delete',
        ]);

        $vendorId = auth('vendor')->id();
        $subSellers = User::whereHas('sellerProfile', function ($q) use ($vendorId) {
            $q->where('parent_id', $vendorId);
        })->whereIn('id', $request->ids)->get();

        foreach ($subSellers as $subSeller) {
            if ($request->action === 'activate') {
                $subSeller->update(['status' => '1']);
            } elseif ($request->action === 'deactivate') {
                $subSeller->update(['status' => '0']);
            } elseif ($request->action === 'delete') {
                $subSeller->sellerProfile()?->delete();
                $subSeller->delete();
            }
        }

        return redirect()->back()->with('success', 'Bulk action executed successfully.');
    }
}
