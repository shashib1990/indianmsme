<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('ddc')->check()) {
            return redirect()->route('ddc.dashboard');
        }
        return view('ddc.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginId = trim($request->login_id);

        // Find DDC user by ddc_code, email, or mobile number
        $user = \App\Models\User::where('role', 'ddc')
            ->where(function ($query) use ($loginId) {
                $query->where('email', $loginId)
                    ->orWhere('mobile', $loginId)
                    ->orWhereHas('ddcProfile', function ($q) use ($loginId) {
                        $q->where('ddc_code', $loginId);
                    });
            })->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            if (!$user->status || $user->status === '0') {
                return back()->withErrors(['login_id' => 'Your account has been deactivated. Please contact admin.']);
            }

            Auth::guard('ddc')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('ddc.dashboard'));
        }

        return back()->withErrors(['login_id' => 'Invalid credentials. Please enter a valid DDC Code and Password.'])->onlyInput('login_id');
    }

    public function logout(Request $request)
    {
        Auth::guard('ddc')->logout();
        
        return redirect()->route('ddc.login');
    }
}
