<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('vendor')->user();

        if (!$user) {
            return redirect()->route('vendor.login')->with('error', 'Please log in first.');
        }

        if ($user->role !== 'seller' && $user->role !== 'vendor') {
            Auth::guard('vendor')->logout();
            return redirect()->route('vendor.login')->with('error', 'Unauthorized access.');
        }

        if ((string)$user->status !== '1' && strtolower((string)$user->status) !== 'active') {
            Auth::guard('vendor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('vendor.login')->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
