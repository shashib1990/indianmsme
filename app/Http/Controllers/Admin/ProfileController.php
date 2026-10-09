<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email'],
            'avatar' => ['nullable', 'image', 'max:5120'],
        ]);

        // Update user table fields (phone maps to mobile column)
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->mobile = $request->phone;
        $admin->save();

        // Update avatar in admin_profiles
        if ($request->hasFile('avatar')) {
            $path = $this->uploadFileToService($request->file('avatar'), 'avatars');
            $profile = $admin->adminProfile;
            if ($profile) {
                // Delete old local file if it exists and is not a URL
                if ($profile->profile_image && !filter_var($profile->profile_image, FILTER_VALIDATE_URL) && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->profile_image)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->profile_image);
                }
                $profile->profile_image = $path;
                $profile->save();
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function showChangePassword()
    {
        return view('admin.profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password on users table
        $admin->password = Hash::make($request->password);
        $admin->save();

        // Reset force_change_password flag on admin_profiles
        $profile = $admin->adminProfile;
        if ($profile) {
            $profile->force_change_password = '0';
            $profile->save();
        }

        return back()->with('success', 'Password changed successfully.');
    }
}
