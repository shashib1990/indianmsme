<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $user = Auth::guard($guard)->user();

        if ($user && $user->force_change_password) {
            $changePasswordRoute = match ($guard) {
                'admin'  => 'admin.profile.change-password',
                'vendor' => 'vendor.profile.change-password',
                'ddc'    => 'ddc.profile.change-password',
                'adc'    => 'adc.profile.change-password',
                default  => null,
            };

            $allowedRoutes = [
                $changePasswordRoute,
                $guard . '.logout',
                $guard . '.profile.change-password.post', // Ensure POST works too
            ];

            if ($changePasswordRoute && !in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route($changePasswordRoute)
                    ->with('warning', 'You must change your password before continuing.');
            }
        }

        return $next($request);
    }
}
