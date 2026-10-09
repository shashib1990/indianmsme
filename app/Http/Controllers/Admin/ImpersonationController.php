<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    public function impersonate(Request $request, string $type, int $id)
    {
        // 1. Ensure only admin can impersonate
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->role !== 'admin') {
            return back()->with('error', 'Only administrators can impersonate users.');
        }

        // 2. Find target user and ensure target user role matches the type
        $user = User::where(function ($query) use ($type) {
            if ($type === 'vendor') {
                $query->where('role', 'seller')->orWhere('role', 'vendor');
            } else {
                $query->where('role', $type);
            }
        })->findOrFail($id);

        $targetRole = $user->role;

        // Stop any existing impersonation first
        if (Session::has('impersonating')) {
            $prevGuard = Session::get('impersonating.guard', $type);
            Auth::guard($prevGuard)->logout();
            Session::forget('impersonating');
        }

        $guard = match ($type) {
            'vendor', 'seller' => 'vendor',
            'ddc'    => 'ddc',
            'adc'    => 'adc',
            default  => 'admin',
        };

        // 3. Store original admin ID in session
        Session::put('impersonating', [
            'admin_id'   => $admin->id,
            'admin_name' => $admin->name,
            'type'       => $type,
            'user_id'    => $user->id,
            'user_name'  => $user->name,
            'guard'      => $guard,
        ]);

        // 4. Add security Audit Log
        ImpersonationLog::create([
            'admin_id'       => $admin->id,
            'target_user_id' => $user->id,
            'target_role'    => $targetRole,
            'login_time'     => now(),
            'ip_address'     => $request->ip(),
        ]);

        // 5. Use Auth::loginUsingId() to login target user
        Auth::guard($guard)->loginUsingId($user->id);

        $host = $request->getHost();
        $adminSub = config('app.admin_subdomain') ?: 'admin';
        $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
        $ddcSub = config('app.ddc_subdomain') ?: 'ddc';

        $isSubdomainRequest = str_starts_with($host, $adminSub . '.') 
                           || str_starts_with($host, $vendorSub . '.') 
                           || str_starts_with($host, $ddcSub . '.');

        if ($isSubdomainRequest && $type !== 'adc') {
            $baseDomain = substr($host, strpos($host, '.') + 1);
            $targetSub = match ($type) {
                'vendor', 'seller' => $vendorSub,
                'ddc'    => $ddcSub,
                default  => $adminSub,
            };
            $port = $request->getPort();
            $portStr = (($request->isSecure() && $port === 443) || (!$request->isSecure() && $port === 80)) ? '' : ":{$port}";
            $targetUrl = "{$request->getScheme()}://{$targetSub}.{$baseDomain}{$portStr}/";
        } else {
            $prefix = match ($type) {
                'vendor', 'seller' => 'vendor',
                'ddc'    => 'ddc',
                'adc'    => 'adc',
                default  => 'admin',
            };
            $targetUrl = url($prefix);
        }

        return redirect($targetUrl)
            ->with('info', "You are now impersonating {$user->name}. Click 'Return to Admin' to go back.");
    }

    public function stopImpersonation(Request $request)
    {
        $impersonating = Session::get('impersonating');

        if (!$impersonating) {
            return redirect()->route('admin.dashboard');
        }

        // Log out of the impersonated guard
        Auth::guard($impersonating['guard'])->logout();

        // Restore original admin session properly using loginUsingId()
        $adminId = $impersonating['admin_id'];
        Auth::guard('admin')->loginUsingId($adminId);

        Session::forget('impersonating');

        $host = $request->getHost();
        $adminSub = config('app.admin_subdomain') ?: 'admin';
        $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
        $ddcSub = config('app.ddc_subdomain') ?: 'ddc';

        $isSubdomainRequest = str_starts_with($host, $adminSub . '.') 
                           || str_starts_with($host, $vendorSub . '.') 
                           || str_starts_with($host, $ddcSub . '.');

        if ($isSubdomainRequest) {
            $baseDomain = substr($host, strpos($host, '.') + 1);
            $port = $request->getPort();
            $portStr = (($request->isSecure() && $port === 443) || (!$request->isSecure() && $port === 80)) ? '' : ":{$port}";
            $adminUrl = "{$request->getScheme()}://{$adminSub}.{$baseDomain}{$portStr}/";
        } else {
            $adminUrl = url('admin');
        }

        return redirect($adminUrl)
            ->with('success', "Returned to admin account from {$impersonating['user_name']}.");
    }
}
