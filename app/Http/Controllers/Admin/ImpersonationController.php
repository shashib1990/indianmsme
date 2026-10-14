<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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

        // 3. Add security Audit Log
        ImpersonationLog::create([
            'admin_id'       => $admin->id,
            'target_user_id' => $user->id,
            'target_role'    => $targetRole,
            'login_time'     => now(),
            'ip_address'     => $request->ip(),
        ]);

        // 4. Create one-time secure SSO token (TTL: 2 minutes)
        $token = Str::random(48);
        Cache::put("impersonate_token_{$token}", [
            'admin_id'   => $admin->id,
            'admin_name' => $admin->name,
            'user_id'    => $user->id,
            'user_name'  => $user->name,
            'type'       => $type,
            'guard'      => $guard,
        ], now()->addMinutes(2));

        // Also login locally on this domain as safety net
        Auth::guard($guard)->loginUsingId($user->id);
        Session::put('impersonating', [
            'admin_id'   => $admin->id,
            'admin_name' => $admin->name,
            'type'       => $type,
            'user_id'    => $user->id,
            'user_name'  => $user->name,
            'guard'      => $guard,
        ]);

        $host = $request->getHost();
        $adminSub  = config('app.admin_subdomain') ?: 'admin';
        $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
        $ddcSub    = config('app.ddc_subdomain') ?: 'ddc';

        $isSubdomainRequest = str_starts_with($host, $adminSub . '.') 
                           || str_starts_with($host, $vendorSub . '.') 
                           || str_starts_with($host, $ddcSub . '.');

        // ADC runs under path prefix /adc (e.g. admin.indianmsme.org/adc or localhost:8000/adc)
        // because adc.indianmsme.org is not a configured DNS subdomain.
        if ($isSubdomainRequest && $type !== 'adc') {
            $baseDomain = substr($host, strpos($host, '.') + 1);
            $targetSub = match ($type) {
                'vendor', 'seller' => $vendorSub,
                'ddc'    => $ddcSub,
                default  => $adminSub,
            };
            $port = $request->getPort();
            $portStr = (($request->isSecure() && $port === 443) || (!$request->isSecure() && $port === 80)) ? '' : ":{$port}";
            $targetUrl = "{$request->getScheme()}://{$targetSub}.{$baseDomain}{$portStr}/impersonate/auth?token={$token}";
        } else {
            $prefix = match ($type) {
                'vendor', 'seller' => 'vendor',
                'ddc'    => 'ddc',
                'adc'    => 'adc',
                default  => 'admin',
            };
            $targetUrl = url("{$prefix}/impersonate/auth?token={$token}");
        }

        return redirect($targetUrl);
    }

    public function authenticateWithToken(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect()->to('/login')->with('error', 'Missing impersonation token.');
        }

        $data = Cache::pull("impersonate_token_{$token}");
        if (!$data) {
            return redirect()->to('/login')->with('error', 'Impersonation token has expired or is invalid.');
        }

        $guard  = $data['guard'];
        $userId = $data['user_id'];

        // Clean out any conflicting existing session
        Auth::guard($guard)->logout();
        Session::forget('impersonating');

        // Log into target guard on this domain
        Auth::guard($guard)->loginUsingId($userId);

        Session::put('impersonating', [
            'admin_id'   => $data['admin_id'],
            'admin_name' => $data['admin_name'],
            'type'       => $data['type'],
            'user_id'    => $userId,
            'user_name'  => $data['user_name'],
            'guard'      => $guard,
        ]);

        $host = $request->getHost();
        $isSubdomain = str_starts_with($host, 'vendor.')
                    || str_starts_with($host, 'ddc.')
                    || str_starts_with($host, (config('app.vendor_subdomain') ?: 'vendor') . '.')
                    || str_starts_with($host, (config('app.ddc_subdomain') ?: 'ddc') . '.');

        if ($data['type'] === 'adc') {
            $redirectUrl = '/adc';
        } elseif ($isSubdomain) {
            $redirectUrl = '/';
        } else {
            $redirectUrl = match ($data['type']) {
                'ddc'              => '/ddc',
                'vendor', 'seller' => '/vendor',
                default            => '/',
            };
        }

        return redirect($redirectUrl)
            ->with('info', "You are now impersonating {$data['user_name']}. Click 'Return to Admin' to go back.");
    }

    public function stopImpersonation(Request $request)
    {
        $impersonating = Session::get('impersonating');

        if (!$impersonating) {
            $adminUrl = url('admin');
            return redirect($adminUrl);
        }

        $guard   = $impersonating['guard'] ?? 'ddc';
        $adminId = $impersonating['admin_id'] ?? null;

        // Log out of the impersonated guard
        Auth::guard($guard)->logout();
        Session::forget('impersonating');

        if ($adminId) {
            $returnToken = Str::random(48);
            Cache::put("impersonate_return_{$returnToken}", [
                'admin_id' => $adminId,
            ], now()->addMinutes(2));

            $host = $request->getHost();
            $adminSub  = config('app.admin_subdomain') ?: 'admin';
            $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
            $ddcSub    = config('app.ddc_subdomain') ?: 'ddc';

            $isSubdomainRequest = str_starts_with($host, $adminSub . '.') 
                               || str_starts_with($host, $vendorSub . '.') 
                               || str_starts_with($host, $ddcSub . '.');

            if ($isSubdomainRequest) {
                $baseDomain = substr($host, strpos($host, '.') + 1);
                $port = $request->getPort();
                $portStr = (($request->isSecure() && $port === 443) || (!$request->isSecure() && $port === 80)) ? '' : ":{$port}";
                $adminUrl = "{$request->getScheme()}://{$adminSub}.{$baseDomain}{$portStr}/impersonate/return?token={$returnToken}";
            } elseif (str_contains($host, 'indianmsme.org')) {
                $adminUrl = "https://admin.indianmsme.org/impersonate/return?token={$returnToken}";
            } else {
                $adminUrl = url("admin/impersonate/return?token={$returnToken}");
            }

            return redirect($adminUrl);
        }

        return redirect()->route('admin.dashboard');
    }

    public function returnWithToken(Request $request)
    {
        $token = $request->query('token');
        if ($token) {
            $data = Cache::pull("impersonate_return_{$token}");
            if ($data && !empty($data['admin_id'])) {
                Auth::guard('admin')->loginUsingId($data['admin_id']);
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Returned to admin account.');
            }
        }

        return redirect()->route('admin.dashboard');
    }
}
