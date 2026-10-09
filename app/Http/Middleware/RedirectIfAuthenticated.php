<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $redirectRoute = match ($guard) {
                    'admin'  => route('admin.dashboard'),
                    'vendor' => route('vendor.dashboard'),
                    'ddc'    => route('ddc.dashboard'),
                    'adc'    => route('adc.dashboard'),
                    'pickup_store' => route('store.dashboard'),
                    default  => '/',
                };

                return redirect($redirectRoute);
            }
        }

        return $next($request);
    }
}
