<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $guards = ['admin', 'ddc', 'adc', 'vendor'];
        $user = null;
        $activeGuard = null;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $activeGuard = $guard;
                break;
            }
        }

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Super Admin check (only for admin guard)
        if ($activeGuard === 'admin' && $user->is_super_admin === '1') {
            return $next($request);
        }

        // Logic for staff members (all panels)
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // If user is a "Parent" (Owner) but not a staff, they have all permissions in their own panel
        if ($user->parent_id === null) {
             return $next($request);
        }

        abort(403, 'Access denied. You do not have permission to perform this action.');
    }
}
