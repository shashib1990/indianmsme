<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Determine which guard is being used and redirect to the correct login page
            $routeName = $request->route()?->getName() ?? '';

            if (str_starts_with($routeName, 'admin.') || str_contains($request->getHost(), 'admin.')) {
                return route('admin.login');
            }
            if (str_starts_with($routeName, 'vendor.') || str_contains($request->getHost(), 'vendor.')) {
                return route('vendor.login');
            }
            if (str_starts_with($routeName, 'ddc.') || str_contains($request->getHost(), 'ddc.')) {
                return route('ddc.login');
            }
            if (str_starts_with($routeName, 'adc.') || str_contains($request->getHost(), 'adc.')) {
                return route('adc.login');
            }
            if (str_starts_with($routeName, 'store.') || str_contains($request->getHost(), 'store.')) {
                return route('store.login');
            }

            return route('admin.login');
        }

        return null;
    }
}
