<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\VendorController;

/*
|--------------------------------------------------------------------------
| Web Routes — Main Ecommerce Website (indianmsme.org / localhost)
|--------------------------------------------------------------------------
*/

// ── Homepage ──────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');

// ── Ecommerce Shop ────────────────────────────────────────────────────────
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'categoryPage'])->name('category.show');
Route::get('/brands', [ShopController::class, 'brands'])->name('brands');

// ── Product Detail ────────────────────────────────────────────────────────
Route::get('/product/{id}/{slug?}', [ProductController::class, 'show'])->name('product.show');

use App\Http\Controllers\Web\PublicFormController;

// ── Static Pages ──────────────────────────────────────────────────────────
Route::get('/about', fn() => view('web.about'))->name('about');
Route::get('/faq', [\App\Http\Controllers\Web\HomeController::class, 'faq'])->name('faq');
Route::get('/contact', fn() => view('web.contact'))->name('contact');
Route::post('/contact', [PublicFormController::class, 'submitContact'])->name('contact.submit');
Route::post('/newsletter/subscribe', [PublicFormController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::get('/vendor-marketplace', fn() => view('web.vendor-marketplace'))->name('vendor-marketplace');
Route::get('/cart-multi-vendor', fn() => view('web.cart-multi-vendor'))->name('cart-multi-vendor');
Route::get('/vendor-account', fn() => view('web.vendor-account'))->name('vendor-account');

// ── AJAX: Pincode Delivery Check ──────────────────────────────────────────
Route::get('/api/check-pincode', [ShopController::class, 'checkPincode'])->name('api.check-pincode');
Route::get('/api/search-autocomplete', [ShopController::class, 'searchAutocomplete'])->name('api.search-autocomplete');

// ── Vendor / Seller Listing & Products ────────────────────────────────────
Route::get('/vendors', [VendorController::class, 'index'])->name('vendors');
Route::get('/vendor/{imv_code}', [VendorController::class, 'products'])->name('vendor.products');
Route::get('/vendor/{imv_code}/ajax', [VendorController::class, 'products'])->name('vendor.products.ajax');
Route::get('/vendor/{imv_code}/sub-sellers', [VendorController::class, 'subSellers'])->name('vendor.sub-sellers');

// ── Public Order / POS Receipt View (Accessible via WhatsApp Invoice Link) ──
Route::get('/receipt/order/{id}', [\App\Http\Controllers\ADC\PosController::class, 'publicReceipt'])->name('pos.receipt.public');
Route::get('/invoices/{filename}', [\App\Http\Controllers\ADC\PosController::class, 'downloadInvoicePdf'])->name('invoice.pdf.public');

// ── Unified Authentication Routes ──
Route::middleware(['guest:adc,web'])->group(function () {
    Route::get('/login', [\App\Http\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Web\AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::post('/register', [\App\Http\Controllers\Web\AuthController::class, 'register'])->name('register.post');

    // ADC Registration OTP Verification Routes
    Route::get('/register/verify-otp', [\App\Http\Controllers\Web\AuthController::class, 'showVerifyOtp'])->name('adc.verify-otp');
    Route::post('/register/verify-otp', [\App\Http\Controllers\Web\AuthController::class, 'verifyOtp'])->name('adc.verify-otp.post');
    Route::post('/register/resend-otp', [\App\Http\Controllers\Web\AuthController::class, 'resendOtp'])->name('adc.resend-otp');
});
Route::match(['get', 'post'], '/logout', [\App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout');
Route::get('/shopping/auth', [\App\Http\Controllers\Web\AuthController::class, 'authenticateAdcShopping'])->name('shop.adc.auth');
Route::post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stopImpersonation'])->name('web.impersonate.stop');

// ── Proxy APIs for registration (public) ──
Route::get('/api/running-store-code/{type}', [\App\Http\Controllers\Web\AuthController::class, 'getRunningStoreCodes'])->name('api.running-store-code');
Route::get('/api/ama-codes', [\App\Http\Controllers\Web\AuthController::class, 'getAmaCodes'])->name('api.ama-codes');
Route::post('/api/adc/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateAbhhyamCode'])->name('web.api.adc.update-abhhyam-code');
Route::post('/api/pos-customer/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateCustomerAbhhyamCode'])->name('web.api.pos-customer.update-abhhyam-code');
Route::post('/api/adc-customer/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateCustomerAbhhyamCode'])->name('web.api.adc-customer.update-abhhyam-code');
Route::post('/api/ddc-customer/update-abhhyam-code', [\App\Http\Controllers\Api\AdcApiController::class, 'updateDdcCustomerAbhhyamCode'])->name('web.api.ddc-customer.update-abhhyam-code');

// ── Controlled Maintenance Testing Wipe Endpoint (GET & POST) ──
Route::match(['get', 'post'], '/maintenance/testing-wipe', [\App\Http\Controllers\MaintenanceWipeController::class, 'execute'])
    ->middleware('throttle:5,1')
    ->name('maintenance.testing-wipe');


