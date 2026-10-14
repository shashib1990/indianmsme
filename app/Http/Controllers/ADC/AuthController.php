<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the ADC Portal Login Page.
     */
    public function showLogin()
    {
        if (Auth::guard('adc')->check()) {
            return redirect()->route('adc.dashboard');
        }
        return view('adc.auth.login');
    }

    /**
     * Handle ADC authentication request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $adcCode = trim($request->login_id);

        // Find user with role 'adc' strictly by adc_code field only
        $user = User::where('role', 'adc')
            ->whereHas('adcProfile', function ($q) use ($adcCode) {
                $q->where('adc_code', $adcCode);
            })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->status === '0' || $user->status === 0 || $user->is_active === '0' || $user->is_active === 0) {
                return back()->withErrors(['login_id' => 'Your account has been deactivated. Please contact administrator.'])->onlyInput('login_id');
            }

            Auth::guard('adc')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('adc.dashboard'))->with('success', 'Welcome back to ADC Portal!');
        }

        return back()->withErrors(['login_id' => 'Invalid credentials. Please enter a valid ADC Code and Password.'])->onlyInput('login_id');
    }

    /**
     * Log out the ADC user.
     */
    public function logout(Request $request)
    {
        Auth::guard('adc')->logout();

        if (Session::has('impersonating')) {
            Session::forget('impersonating');
        }

        return redirect()->route('adc.login')->with('success', 'Logged out successfully.');
    }

    /**
     * Generate SSO token and bridge the ADC session to the main shopping website.
     */
    public function shoppingBridge(Request $request)
    {
        $user = Auth::guard('adc')->user();
        if (!$user) {
            return redirect(main_website_url('login'));
        }

        $pincode = $user->adcProfile?->pincode ?? $user->profile?->pincode ?? session('user_pincode');

        $token = \Illuminate\Support\Str::random(48);
        \Illuminate\Support\Facades\Cache::put("adc_shopping_token_{$token}", [
            'user_id'       => $user->id,
            'pincode'       => $pincode,
            'impersonating' => Session::get('impersonating'),
        ], now()->addMinutes(2));

        return redirect(main_website_url("shopping/auth?token={$token}"));
    }
}
