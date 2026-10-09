<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip());
        });

        // Login throttle: 10 attempts per minute per IP+email combo
        RateLimiter::for('login', function (Request $request) {
            $key = $request->ip() . '|' . strtolower($request->input('email', ''));
            return [
                Limit::perMinute(10)->by($key),
                Limit::perMinute(30)->by($request->ip()),  // global IP safety
            ];
        });

        // Authenticated admin actions: 300/min per user session (prevents logout on search)
        RateLimiter::for('admin', function (Request $request) {
            $userId = auth('admin')->id() ?: $request->ip();
            return Limit::perMinute(300)->by('admin:' . $userId);
        });

        // Search-specific: 120/min per user to prevent rate-limit triggered session issues
        RateLimiter::for('search', function (Request $request) {
            $userId = auth('admin')->id()
                   ?? auth('ddc')->id()
                   ?? auth('adc')->id()
                   ?? auth('vendor')->id()
                   ?? $request->ip();
            return Limit::perMinute(120)->by('search:' . $userId);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            $appDomain = config('app.domain') ?: 'localhost';
            $adminSub  = config('app.admin_subdomain') ?: 'admin';
            $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
            $ddcSub    = config('app.ddc_subdomain') ?: 'ddc';
            $adcSub    = config('app.adc_subdomain') ?: 'adc';
            $storeSub  = config('app.store_subdomain') ?: 'pickupstore';

            // Subdomain route groups matching {subdomain}.{appDomain}
            Route::middleware('web')->domain("{$adminSub}.{$appDomain}")->group(base_path('routes/admin.php'));
            Route::middleware('web')->domain("{$vendorSub}.{$appDomain}")->group(base_path('routes/vendor.php'));
            Route::middleware('web')->domain("{$ddcSub}.{$appDomain}")->group(base_path('routes/ddc.php'));
            Route::middleware('web')->domain("{$adcSub}.{$appDomain}")->group(base_path('routes/adc.php'));
            Route::middleware('web')->domain("{$storeSub}.{$appDomain}")->group(base_path('routes/pickup_store.php'));

            // Always register prefix fallback routes for universal path access
            Route::middleware('web')->prefix('admin')->group(base_path('routes/admin.php'));
            Route::middleware('web')->prefix('vendor')->group(base_path('routes/vendor.php'));
            Route::middleware('web')->prefix('ddc')->group(base_path('routes/ddc.php'));
            Route::middleware('web')->prefix('adc')->group(base_path('routes/adc.php'));
            Route::middleware('web')->prefix('store')->group(base_path('routes/pickup_store.php'));

            // Main website routes
            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }
}
