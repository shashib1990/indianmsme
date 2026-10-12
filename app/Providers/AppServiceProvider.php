<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Default pagination view for application
        Paginator::defaultView('web.partials.pagination');
        Paginator::defaultSimpleView('web.partials.pagination');

        Blade::if('permission', function ($permission) {
            $guards = ['admin', 'ddc', 'adc', 'vendor'];
            $user = null;
            foreach ($guards as $guard) {
                if (auth($guard)->check()) {
                    $user = auth($guard)->user();
                    break;
                }
            }

            if (!$user) return false;

            // Super Admin check for admin guard
            if (auth('admin')->check() && $user->is_super_admin === '1') {
                return true;
            }

            // Owner check (if not staff, they have all permissions)
            if ($user->parent_id === null) {
                return true;
            }

            return $user->hasPermission($permission);
        });

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // ── ADC Cart View Composer ─────────────────────────────────────────
        // Shares $cartCount and $cartSubtotal with web layout view
        \Illuminate\Support\Facades\View::composer(['web.layouts.app', 'welcome'], function ($view) {
            $cartCount      = 0;
            $cartSubtotal   = 0;
            $cartProductIds = [];

            if (auth('adc')->check()) {
                $pincode = session('user_pincode');
                if ($pincode) {
                    $items = \App\Models\AdcCartItem::where('adc_id', auth('adc')->id())
                        ->where('pincode', $pincode)
                        ->with('product')
                        ->get();

                    $cartCount      = $items->count();
                    $cartSubtotal   = $items->sum(fn($i) =>
                        ($i->product?->adc_purchase_price ?? 0) * $i->quantity
                    );
                    $cartProductIds = $items->pluck('product_id')->toArray();
                }
            }

            // Fetch top 6 active categories for the footer/layout
            $footerCategories = \App\Models\Category::active()->orderBy('name')->take(6)->get();

            $view->with(compact('cartCount', 'cartSubtotal', 'cartProductIds', 'footerCategories'));
        });

        // Dynamically configure session domain based on environment
        // This allows the same session to work across subdomains in production.
        // In local environment or on 127.0.0.1 / localhost, keep it null so browser stores cookies.
        $domain = config('app.domain', 'localhost');
        
        if (!app()->environment('local') && $domain !== 'localhost' && !str_contains(request()->getHost(), '127.0.0.1') && !str_contains(request()->getHost(), 'localhost')) {
            config(['session.domain' => '.' . $domain]);
        } else {
            config(['session.domain' => null]);
        }
    }
}
