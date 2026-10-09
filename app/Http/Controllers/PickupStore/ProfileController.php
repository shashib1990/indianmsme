<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $store = Auth::guard('pickup_store')->user();
        $store->load(['stateRelation', 'districtRelation', 'owner']);
        $states = State::active()->orderBy('name')->get();
        $districts = District::where('state_id', $store->state_id)->active()->orderBy('name')->get();

        return view('pickup-store.profile', compact('store', 'states', 'districts'));
    }

    public function updateProfile(Request $request)
    {
        $store = Auth::guard('pickup_store')->user();

        $request->validate([
            'pickup_store_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:15'],
            'address' => ['required', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'state' => ['required', 'exists:states,id'],
            'district' => ['required', 'exists:districts,id'],
            'pincode' => ['required', 'string', 'max:10'],
        ]);

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
        $store->save();

        return redirect()->route('store.profile')->with('success', 'Profile updated successfully.');
    }

    public function showChangePassword()
    {
        return view('pickup-store.change-password');
    }

    public function changePassword(Request $request)
    {
        $store = Auth::guard('pickup_store')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($request->current_password, $store->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match our records.']);
        }

        $store->password = Hash::make($request->password);
        $store->save();

        return back()->with('success', 'Password changed successfully.');
    }
}
