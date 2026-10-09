<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ADCMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('adc')->user();

        if (!$user) {
            return redirect()->route('adc.login')->with('error', 'Please log in first.');
        }

        if ($user->role !== 'adc') {
            Auth::guard('adc')->logout();
            return redirect()->route('adc.login')->with('error', 'Unauthorized access.');
        }

        if ((string)$user->status !== '1' && strtolower((string)$user->status) !== 'active') {
            Auth::guard('adc')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('adc.login')->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
