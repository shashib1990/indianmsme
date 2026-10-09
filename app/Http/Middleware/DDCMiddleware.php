<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DDCMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('ddc')->user();

        if (!$user) {
            return redirect()->route('ddc.login')->with('error', 'Please log in first.');
        }

        if ($user->role !== 'ddc') {
            Auth::guard('ddc')->logout();
            return redirect()->route('ddc.login')->with('error', 'Unauthorized access.');
        }

        if ((string)$user->status !== '1' && strtolower((string)$user->status) !== 'active') {
            Auth::guard('ddc')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('ddc.login')->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
