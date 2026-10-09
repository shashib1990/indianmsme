<?php

use App\Http\Controllers\Vendor\AuthController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\MediaController;
use App\Http\Controllers\Vendor\SubSellerController;
use Illuminate\Support\Facades\Route;

// ── Location API (no auth needed ── used by AJAX dropdowns) ──
Route::prefix('api/locations')->name('vendor.api.locations.')->group(function () {
    Route::get('/states', [\App\Http\Controllers\Api\LocationApiController::class, 'states'])->name('states');
    Route::get('/districts', [\App\Http\Controllers\Api\LocationApiController::class, 'districts'])->name('districts');
    Route::get('/pincodes', [\App\Http\Controllers\Api\LocationApiController::class, 'pincodes'])->name('pincodes');
});

Route::middleware(['guest:vendor'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('vendor.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('vendor.login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('vendor.register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login')->name('vendor.register.post');
    Route::get('/register/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('vendor.verify-otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('vendor.verify-otp.post');
    Route::post('/register/resend-otp', [AuthController::class, 'resendOtp'])->name('vendor.resend-otp');
});

Route::middleware(['auth:vendor', 'force.password:vendor', 'role.seller'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('vendor.dashboard');

    Route::get('/profile', [ProfileController::class, 'index'])->name('vendor.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('vendor.profile.update');
    Route::post('/profile/ddc-subscription', [ProfileController::class, 'updateDdcSubscription'])->name('vendor.profile.ddc-subscription');

    // Smart Sales Incentive (SSI) for DDC Subscribed Sellers
    Route::get('/smart-sales-incentive', [ProfileController::class, 'showSmartSalesIncentive'])->name('vendor.smart-sales-incentive');
    Route::post('/smart-sales-incentive', [ProfileController::class, 'updateSmartSalesIncentive'])->name('vendor.smart-sales-incentive.post');
    Route::delete('/smart-sales-incentive/slabs/{id}', [ProfileController::class, 'destroySlab'])->name('vendor.smart-sales-incentive.slab.destroy');
    Route::get('/smart-sales-incentive/deductions', [ProfileController::class, 'showSmartSalesIncentiveDeductions'])->name('vendor.smart-sales-incentive.deductions');

    Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])
        ->name('vendor.profile.change-password')
        ->withoutMiddleware(['force.password:vendor']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])
        ->name('vendor.profile.change-password.post')
        ->withoutMiddleware(['force.password:vendor']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('vendor.logout');

    // Organization & Access Control
    Route::name('vendor.')->group(function () {
        Route::post('departments/bulk', [\App\Http\Controllers\Vendor\DepartmentController::class, 'bulk'])->name('departments.bulk');
        Route::resource('departments', \App\Http\Controllers\Vendor\DepartmentController::class)->except(['create', 'show', 'edit']);

        Route::post('staff/bulk', [\App\Http\Controllers\Vendor\StaffController::class, 'bulk'])->name('staff.bulk');
        Route::resource('staff', \App\Http\Controllers\Vendor\StaffController::class)->except(['show']);

        // Roles and permissions routes removed per USER_REQUEST

        Route::post('brands/quick-store', [ProductController::class, 'quickStoreBrand'])->name('brands.quick-store');
        Route::get('categories/{id}/hsns', [ProductController::class, 'getHsnsByCategory'])->name('categories.hsns');
        Route::get('sub-categories/ajax', [ProductController::class, 'ajaxSubCategories'])->name('sub-categories.ajax');
        Route::get('child-categories/ajax', [ProductController::class, 'ajaxChildCategories'])->name('child-categories.ajax');
        Route::get('assigned-products', [ProductController::class, 'assignedProducts'])->name('products.assigned');
        Route::post('assigned-products/{productId}/update-markup', [ProductController::class, 'updateMarkup'])->name('products.assigned.update-markup');
        Route::post('assigned-products/{productId}/toggle-status', [ProductController::class, 'toggleAssignedStatus'])->name('products.assigned.toggle-status');
        Route::post('products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
        Route::get('products/bulk-sample', [ProductController::class, 'downloadSample'])->name('products.bulk-sample');
        Route::post('products/bulk-import', [ProductController::class, 'bulkImport'])->name('products.bulk-import');
        Route::get('products/bulk-update-download', [ProductController::class, 'downloadUpdateData'])->name('products.bulk-update-download');
        Route::post('products/bulk-update-upload', [ProductController::class, 'bulkUpdate'])->name('products.bulk-update-upload');
        Route::post('products/{id}/submit', [ProductController::class, 'submitApproval'])->name('products.submit');
        Route::post('products/{id}/toggle-active', [ProductController::class, 'toggleActiveStatus'])->name('products.toggle-active');
        Route::post('products/{id}/update-image', [ProductController::class, 'updateImage'])->name('products.update-image');
        Route::post('vendor/products/{id}/update-image', [ProductController::class, 'updateImage']);
        Route::resource('products', ProductController::class)->except(['show']);

        // Media Library
        Route::get('media/api', [MediaController::class, 'apiList'])->name('media.api');
        Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('media.bulk-delete');
        Route::resource('media', MediaController::class)->except(['create', 'edit', 'show', 'update']);

        // Sub Seller Management
        Route::get('sub-sellers/{id}/products', [SubSellerController::class, 'products'])->name('sub-sellers.products');
        Route::post('sub-sellers/{id}/products', [SubSellerController::class, 'assignProduct'])->name('sub-sellers.products.assign');
        Route::post('sub-sellers/{id}/products/bulk', [SubSellerController::class, 'bulkAssignProducts'])->name('sub-sellers.products.bulk-assign');
        Route::delete('sub-sellers/{id}/products/{productId}', [SubSellerController::class, 'removeProduct'])->name('sub-sellers.products.remove');
        Route::post('sub-sellers/bulk', [SubSellerController::class, 'bulk'])->name('sub-sellers.bulk');
        Route::post('sub-sellers/{id}/toggle-active', [SubSellerController::class, 'toggleActiveStatus'])->name('sub-sellers.toggle-active');
        Route::resource('sub-sellers', SubSellerController::class)->except(['show']);

        // Pickup Store CRUD
        Route::patch('pickup-stores/{id}/status', [\App\Http\Controllers\Vendor\PickupStoreController::class, 'toggleStatus'])->name('pickup-stores.status');
        Route::post('pickup-stores/{id}/reset-password', [\App\Http\Controllers\Vendor\PickupStoreController::class, 'resetPassword'])->name('pickup-stores.reset-password');

        // Delivery Areas
        Route::get('pickup-stores/{id}/delivery-areas', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'index'])->name('pickup-stores.delivery-areas.index');
        Route::post('pickup-stores/{id}/delivery-areas/update-coverage', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'updateCoverage'])->name('pickup-stores.delivery-areas.update-coverage');
        Route::post('pickup-stores/{id}/delivery-areas/store', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'store'])->name('pickup-stores.delivery-areas.store');
        Route::delete('pickup-stores/{id}/delivery-areas/{areaId}', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'destroy'])->name('pickup-stores.delivery-areas.destroy');
        Route::post('pickup-stores/{id}/delivery-areas/bulk-import', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'bulkImport'])->name('pickup-stores.delivery-areas.bulk-import');
        Route::get('pickup-stores/{id}/delivery-areas/sample', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'sampleTemplate'])->name('pickup-stores.delivery-areas.sample');
        Route::get('pickup-stores/{id}/delivery-areas/export', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'export'])->name('pickup-stores.delivery-areas.export');
        Route::patch('pickup-stores/{id}/delivery-areas/{areaId}/status', [\App\Http\Controllers\Vendor\PickupStoreDeliveryAreaController::class, 'toggleStatus'])->name('pickup-stores.delivery-areas.status');

        Route::resource('pickup-stores', \App\Http\Controllers\Vendor\PickupStoreController::class);

        // Purchase Orders
        Route::get('purchase-orders', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('purchase-orders/new', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'newOrders'])->name('purchase-orders.new');
        Route::get('purchase-orders/accepted', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'acceptedOrders'])->name('purchase-orders.accepted');
        Route::get('purchase-orders/verify', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'verifyOrders'])->name('purchase-orders.verify');
        Route::get('purchase-orders/ready', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'readyOrders'])->name('purchase-orders.ready');
        Route::get('purchase-orders/dispatched', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'dispatchedOrders'])->name('purchase-orders.dispatched');
        Route::get('purchase-orders/received', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'receivedOrders'])->name('purchase-orders.received');
        Route::get('purchase-orders/rejected', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'rejectedOrders'])->name('purchase-orders.rejected');
        Route::get('purchase-orders/cancelled', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'cancelledOrders'])->name('purchase-orders.cancelled');
        Route::get('purchase-orders/{id}', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('purchase-orders/{id}/invoice', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('purchase-orders.invoice');
        Route::post('purchase-orders/{id}/invoice/transporter', [\App\Http\Controllers\InvoiceController::class, 'updateTransporter'])->name('purchase-orders.invoice.transporter');
        Route::post('purchase-orders/{id}/invoice/upload', [\App\Http\Controllers\InvoiceController::class, 'uploadInvoiceFile'])->name('purchase-orders.invoice.upload');
        Route::post('purchase-orders/{id}/bulk-respond', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'bulkRespond'])->name('purchase-orders.bulk-respond');
        Route::post('purchase-orders/{id}/verify-payment', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'verifyPayment'])->name('purchase-orders.verify-payment');
        Route::post('purchase-orders/{id}/verify-payout', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'verifySellerPayout'])->name('purchase-orders.verify-payout');
        Route::post('purchase-orders/{id}/dispatch', [\App\Http\Controllers\Vendor\PurchaseOrderController::class, 'dispatchOrder'])->name('purchase-orders.dispatch');

        // Rack & Shelf Bookings (Seller Global Marketplace & Payment Flow)
        Route::get('rack-bookings', [\App\Http\Controllers\Vendor\RackBookingController::class, 'index'])->name('rack-bookings.index');
        Route::get('rack-bookings/marketplace', [\App\Http\Controllers\Vendor\RackBookingController::class, 'marketplace'])->name('rack-bookings.marketplace');
        Route::get('rack-bookings/images/{id}', [\App\Http\Controllers\Vendor\RackBookingController::class, 'getRackImages'])->name('rack-bookings.images');
        Route::post('rack-bookings/store', [\App\Http\Controllers\Vendor\RackBookingController::class, 'storeBooking'])->name('rack-bookings.store');
        Route::get('rack-bookings/{id}/payment', [\App\Http\Controllers\Vendor\RackBookingController::class, 'paymentPage'])->name('rack-bookings.payment');
        Route::post('rack-bookings/{id}/payment', [\App\Http\Controllers\Vendor\RackBookingController::class, 'submitPayment'])->name('rack-bookings.submit-payment');

        // Seller Wallet & Payout Routes
        Route::get('wallet', [\App\Http\Controllers\Vendor\WalletController::class, 'index'])->name('wallet.index');
        Route::post('wallet/request/topup', [\App\Http\Controllers\Vendor\WalletController::class, 'storeTopupRequest'])->name('wallet.request.topup');
        Route::post('wallet/request/withdrawal', [\App\Http\Controllers\Vendor\WalletController::class, 'storeWithdrawalRequest'])->name('wallet.request.withdrawal');
        Route::post('wallet/verify-payout/{id}', [\App\Http\Controllers\Vendor\WalletController::class, 'verifyPayout'])->name('wallet.verify-payout');
        Route::post('wallet/upload-receipt', [\App\Http\Controllers\Vendor\WalletController::class, 'uploadReceipt'])->name('wallet.upload-receipt');

        // Seller Debit Notes Routes
        Route::get('debit-notes', [\App\Http\Controllers\Vendor\DebitNoteController::class, 'index'])->name('debit-notes.index');
        Route::get('debit-notes/{id}', [\App\Http\Controllers\Vendor\DebitNoteController::class, 'show'])->name('debit-notes.show');
        Route::post('debit-notes/{id}/respond', [\App\Http\Controllers\Vendor\DebitNoteController::class, 'respond'])->name('debit-notes.respond');
        Route::get('adc-debit-notes/{id}', [\App\Http\Controllers\Vendor\DebitNoteController::class, 'showAdcDebitNote'])->name('adc-debit-notes.show');
        Route::post('adc-debit-notes/{id}/respond', [\App\Http\Controllers\Vendor\DebitNoteController::class, 'respondAdcDebitNote'])->name('adc-debit-notes.respond');

        // Vendor Excess Stock Invoices
        Route::get('excess-invoices', [\App\Http\Controllers\Vendor\ExcessInvoiceController::class, 'index'])->name('excess-invoices.index');
        Route::get('excess-invoices/{id}', [\App\Http\Controllers\Vendor\ExcessInvoiceController::class, 'show'])->name('excess-invoices.show');
        Route::get('adc-excess-invoices/{id}', [\App\Http\Controllers\Vendor\ExcessInvoiceController::class, 'showAdcExcessInvoice'])->name('adc-excess-invoices.show');

        // Seller Credit Notes Routes
        Route::get('credit-notes', [\App\Http\Controllers\Vendor\CreditNoteController::class, 'index'])->name('credit-notes.index');
        Route::get('credit-notes/{id}', [\App\Http\Controllers\Vendor\CreditNoteController::class, 'show'])->name('credit-notes.show');
        Route::get('adc-credit-notes/{id}', [\App\Http\Controllers\Vendor\CreditNoteController::class, 'showAdcCreditNote'])->name('adc-credit-notes.show');
    });
});
