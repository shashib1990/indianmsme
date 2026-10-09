<?php

namespace App\Http\Controllers\PickupStore;

use App\Http\Controllers\Controller;
use App\Models\PickupStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('pickup_store')->check()) {
            return redirect()->route('store.dashboard');
        }
        return view('pickup-store.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_field' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = $request->input('login_field');
        $password = $request->input('password');

        // Check if rate limited
        $throttleKey = $request->ip() . '|' . strtolower($loginField);
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login_field' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Find store strictly by Pickup Store Code
        $store = PickupStore::where('pickup_store_code', $loginField)->first();

        if (!$store || empty($store->password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withInput($request->only('login_field'))->withErrors([
                'login_field' => 'Invalid credentials. Please enter a valid Pickup Store Code and Password.',
            ]);
        }

        $isValidPassword = false;
        $hashInfo = Hash::info($store->password);
        $isHashed = !empty($hashInfo['algo']) && $hashInfo['algo'] !== 0;

        if ($isHashed) {
            $isValidPassword = Hash::check($password, $store->password);
        } else {
            if ($password === $store->password) {
                $isValidPassword = true;
                $store->password = Hash::make($password);
                $store->save();
            }
        }

        if (!$isValidPassword) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withInput($request->only('login_field'))->withErrors([
                'login_field' => 'Invalid credentials. Please enter a valid Pickup Store Code and Password.',
            ]);
        }

        if ($store->status !== 'Active') {
            return back()->withInput($request->only('login_field'))->withErrors([
                'login_field' => 'Your account is currently inactive.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('pickup_store')->login($store, $request->filled('remember'));

        return redirect()->route('store.dashboard')->with('success', 'Logged in successfully.');
    }

    public function logout(Request $request)
    {
        Auth::guard('pickup_store')->logout();
        
        return redirect()->route('store.login')->with('success', 'Logged out successfully.');
    }
}
