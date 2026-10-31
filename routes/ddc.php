<?php

use App\Http\Controllers\DDC\AuthController;
use App\Http\Controllers\DDC\DashboardController;
use App\Http\Controllers\DDC\ProfileController;
use App\Http\Controllers\DDC\DepartmentController;
use App\Http\Controllers\DDC\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DDC Panel Routes
| Domain: ddc.localhost / ddc.indianmsme.org
|--------------------------------------------------------------------------
*/

// ── Location API (no auth needed — used by AJAX dropdowns) ──
Route::prefix('api/locations')->name('ddc.api.locations.')->group(function () {
    Route::get('/states', [\App\Http\Controllers\Api\LocationApiController::class, 'states'])->name('states');
    Route::get('/districts', [\App\Http\Controllers\Api\LocationApiController::class, 'districts'])->name('districts');
    Route::get('/pincodes', [\App\Http\Controllers\Api\LocationApiController::class, 'pincodes'])->name('pincodes');
});

Route::get('/api/ama-codes', [\App\Http\Controllers\Web\AuthController::class, 'getAmaCodes'])->name('ddc.api.ama-codes');

// ── Impersonation SSO Handlers ──
Route::get('/impersonate/auth', [\App\Http\Controllers\Admin\ImpersonationController::class, 'authenticateWithToken'])->name('ddc.impersonate.auth');
Route::post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stopImpersonation'])->name('ddc.impersonate.stop');

Route::name('ddc.')->group(function () {
    Route::middleware(['guest:ddc'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    });

    Route::middleware(['auth:ddc', 'force.password:ddc', 'role.ddc'])->group(function () {

        Route::post('staff/bulk', [StaffController::class, 'bulk'])->name('staff.bulk');
        Route::resource('staff', StaffController::class)->except(['show']);
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics/sales-profit-chart', [DashboardController::class, 'salesProfitChartData'])->name('analytics.sales-profit-chart');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])
            ->name('profile.change-password')
            ->withoutMiddleware(['force.password:ddc']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])
            ->name('profile.change-password.post')
            ->withoutMiddleware(['force.password:ddc']);

        Route::post('departments/bulk', [DepartmentController::class, 'bulk'])->name('departments.bulk');
        Route::resource('departments', DepartmentController::class)->except(['create', 'edit', 'show']);

        // Roles and permissions routes removed per USER_REQUEST

        Route::get('/wallet', [\App\Http\Controllers\DDC\WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/request', [\App\Http\Controllers\DDC\WalletController::class, 'storeRequest'])->name('wallet.request.store');
        Route::post('/wallet/upload-receipt', [\App\Http\Controllers\DDC\WalletController::class, 'uploadReceipt'])->name('wallet.upload-receipt');

        // ADC Orders (Placed by ADCs in this DDC area)
        Route::get('/adc-orders', [\App\Http\Controllers\DDC\AdcOrderController::class, 'index'])->name('adc-orders.index');
        Route::get('/adc-orders/{id}', [\App\Http\Controllers\DDC\AdcOrderController::class, 'show'])->name('adc-orders.show');
        Route::get('/adc-orders/{id}/invoice', [\App\Http\Controllers\DDC\AdcOrderController::class, 'printInvoice'])->name('adc-orders.invoice');
        Route::post('/adc-orders/{id}/dispatch', [\App\Http\Controllers\DDC\AdcOrderController::class, 'dispatch'])->name('adc-orders.dispatch');
        Route::post('/adc-orders/{id}/cancel', [\App\Http\Controllers\DDC\AdcOrderController::class, 'cancel'])->name('adc-orders.cancel');

        // Canteen Network (ADCs assigned to this DDC)
        Route::get('/canteens', [\App\Http\Controllers\DDC\CanteenController::class, 'index'])->name('canteens.index');
        Route::get('/canteens/{id}', [\App\Http\Controllers\DDC\CanteenController::class, 'show'])->name('canteens.show');

        // ADC Wallet Requests (Assigned to this DDC)
        Route::get('/adc-wallet-requests', [\App\Http\Controllers\DDC\AdcWalletRequestController::class, 'index'])->name('adc-wallet-requests.index');
        Route::post('/adc-wallet-requests/{id}/approve', [\App\Http\Controllers\DDC\AdcWalletRequestController::class, 'approve'])->name('adc-wallet-requests.approve');
        Route::post('/adc-wallet-requests/{id}/reject', [\App\Http\Controllers\DDC\AdcWalletRequestController::class, 'reject'])->name('adc-wallet-requests.reject');

        // Purchase Orders
        Route::get('/purchase-orders/create', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders/cart/add', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'addToCart'])->name('purchase-orders.cart.add');
        Route::get('/purchase-orders/cart', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'viewCart'])->name('purchase-orders.cart.view');
        Route::post('/purchase-orders/cart/update', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'updateCart'])->name('purchase-orders.cart.update');
        Route::post('/purchase-orders/cart/remove', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'removeFromCart'])->name('purchase-orders.cart.remove');
        Route::post('/purchase-orders/cart/clear', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'clearCart'])->name('purchase-orders.cart.clear');
        Route::post('/purchase-orders/cart/clear-seller', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'clearSellerCart'])->name('purchase-orders.cart.clear-seller');
        Route::post('/purchase-orders/store', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/pending', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'pendingOrders'])->name('purchase-orders.pending');
        Route::get('/purchase-orders/accepted', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'acceptedOrders'])->name('purchase-orders.accepted');
        Route::get('/purchase-orders/processing', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'processingOrders'])->name('purchase-orders.processing');
        Route::get('/purchase-orders/dispatched', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'dispatchedOrders'])->name('purchase-orders.dispatched');
        Route::get('/purchase-orders/received-orders', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'receivedOrdersList'])->name('purchase-orders.received-orders');
        Route::get('/purchase-orders/rejected', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'rejectedOrders'])->name('purchase-orders.rejected');
        Route::get('/purchase-orders/cancelled', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'cancelledOrders'])->name('purchase-orders.cancelled');
        Route::get('/purchase-orders/{id}', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{id}/invoice', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('purchase-orders.invoice');
        Route::post('/purchase-orders/{id}/pay', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'pay'])->name('purchase-orders.pay');
        Route::post('/purchase-orders/{id}/cancel', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::post('/purchase-orders/{id}/received', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'markReceived'])->name('purchase-orders.received');
        Route::get('/purchase-orders/{id}/receive', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'receiveForm'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/items/{itemId}/stock-in', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'stockInItem'])->name('purchase-orders.items.stock-in');
        Route::post('/purchase-orders/{id}/ddc-invoice', [\App\Http\Controllers\DDC\PurchaseOrderController::class, 'updateDdcInvoice'])->name('purchase-orders.ddc-invoice');

        // DDC Debit & Credit Notes
        Route::get('/purchase-orders/{id}/debit-note/create', [\App\Http\Controllers\DDC\DebitNoteController::class, 'create'])->name('purchase-orders.debit-note.create');
        Route::get('/debit-notes/create/{poId}', [\App\Http\Controllers\DDC\DebitNoteController::class, 'create'])->name('debit-notes.create');
        Route::post('/purchase-orders/{id}/debit-note/store', [\App\Http\Controllers\DDC\DebitNoteController::class, 'store'])->name('purchase-orders.debit-note.store');
        Route::get('/debit-notes', [\App\Http\Controllers\DDC\DebitNoteController::class, 'index'])->name('debit-notes.index');
        Route::get('/debit-notes/{id}', [\App\Http\Controllers\DDC\DebitNoteController::class, 'show'])->name('debit-notes.show');

        // DDC Excess Stock Invoices
        Route::get('/excess-invoices', [\App\Http\Controllers\DDC\ExcessInvoiceController::class, 'index'])->name('excess-invoices.index');
        Route::get('/purchase-orders/{id}/excess-invoice/create', [\App\Http\Controllers\DDC\ExcessInvoiceController::class, 'create'])->name('purchase-orders.excess-invoice.create');
        Route::post('/purchase-orders/{id}/excess-invoice/store', [\App\Http\Controllers\DDC\ExcessInvoiceController::class, 'store'])->name('purchase-orders.excess-invoice.store');
        Route::get('/excess-invoices/{id}', [\App\Http\Controllers\DDC\ExcessInvoiceController::class, 'show'])->name('excess-invoices.show');

        Route::get('/credit-notes', [\App\Http\Controllers\DDC\CreditNoteController::class, 'index'])->name('credit-notes.index');
        Route::get('/credit-notes/{id}', [\App\Http\Controllers\DDC\CreditNoteController::class, 'show'])->name('credit-notes.show');

        // ADC Debit Notes, Credit Notes & Excess Invoices for DDC
        Route::get('/adc-debit-notes/{id}', [\App\Http\Controllers\DDC\DebitNoteController::class, 'showAdcDebitNote'])->name('adc-debit-notes.show');
        Route::post('/adc-debit-notes/{id}/respond', [\App\Http\Controllers\DDC\DebitNoteController::class, 'respondAdcDebitNote'])->name('adc-debit-notes.respond');
        Route::get('/adc-excess-invoices/{id}', [\App\Http\Controllers\DDC\ExcessInvoiceController::class, 'showAdcExcessInvoice'])->name('adc-excess-invoices.show');
        Route::get('/adc-credit-notes/{id}', [\App\Http\Controllers\DDC\CreditNoteController::class, 'showAdcCreditNote'])->name('adc-credit-notes.show');
        Route::get('/inventory', [\App\Http\Controllers\DDC\InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/export', [\App\Http\Controllers\DDC\InventoryController::class, 'export'])->name('inventory.export');
        Route::get('/inventory/expired-products', [\App\Http\Controllers\DDC\InventoryController::class, 'expiredProducts'])->name('inventory.expired-products');
        Route::get('/inventory/missing-barcodes', [\App\Http\Controllers\DDC\InventoryController::class, 'missingBarcodes'])->name('inventory.missing-barcodes');
        Route::post('/inventory/{id}/update-barcode', [\App\Http\Controllers\DDC\InventoryController::class, 'updateBarcode'])->name('inventory.update-barcode');
        Route::post('/inventory/bulk-update-barcodes', [\App\Http\Controllers\DDC\InventoryController::class, 'bulkUpdateBarcodes'])->name('inventory.bulk-update-barcodes');
        Route::put('/inventory/{id}', [\App\Http\Controllers\DDC\InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/{id}/toggle-pos', [\App\Http\Controllers\DDC\InventoryController::class, 'togglePosStatus'])->name('inventory.toggle-pos');

        // ── Local Product & Purchase Module ──
        Route::get('/local-products/search-suggestions', [\App\Http\Controllers\DDC\LocalProductController::class, 'searchSuggestions'])->name('local-products.search-suggestions');
        Route::get('/local-products/category-hsn', [\App\Http\Controllers\DDC\LocalProductController::class, 'getCategoryHsn'])->name('local-products.category-hsn');
        Route::get('/local-products-comparison', [\App\Http\Controllers\DDC\LocalProductController::class, 'comparison'])->name('local-products.comparison');
        Route::post('/local-products/brands/quick-store', [\App\Http\Controllers\DDC\LocalProductController::class, 'quickStoreBrand'])->name('local-products.brands.quick-store');
        Route::get('/local-products/sample-template', [\App\Http\Controllers\DDC\LocalProductController::class, 'sampleTemplate'])->name('local-products.sample-template');
        Route::post('/local-products/bulk-upload', [\App\Http\Controllers\DDC\LocalProductController::class, 'bulkUpload'])->name('local-products.bulk-upload');
        Route::resource('local-products', \App\Http\Controllers\DDC\LocalProductController::class);

        Route::resource('local-purchases', \App\Http\Controllers\DDC\LocalPurchaseController::class)->only(['index', 'create', 'store', 'show']);

        // Reports
        Route::get('/reports/local-purchases', [\App\Http\Controllers\DDC\LocalPurchaseReportController::class, 'index'])->name('reports.local-purchases');
        Route::get('/reports/local-purchases/export', [\App\Http\Controllers\DDC\LocalPurchaseReportController::class, 'exportCsv'])->name('reports.local-purchases.export');
        Route::get('/reports/local-purchases/print', [\App\Http\Controllers\DDC\LocalPurchaseReportController::class, 'print'])->name('reports.local-purchases.print');

        // POS Billing Routes
        Route::get('/pos', [\App\Http\Controllers\DDC\PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/retail', [\App\Http\Controllers\DDC\PosController::class, 'retail'])->name('pos.retail');
        Route::get('/pos/wholesale', [\App\Http\Controllers\DDC\PosController::class, 'wholesale'])->name('pos.wholesale');
        Route::get('/pos/cart', [\App\Http\Controllers\DDC\PosController::class, 'getCart'])->name('pos.cart.get');
        Route::post('/pos/cart/sync', [\App\Http\Controllers\DDC\PosController::class, 'syncCart'])->name('pos.cart.sync');
        Route::post('/pos/cart/clear', [\App\Http\Controllers\DDC\PosController::class, 'clearCartDb'])->name('pos.cart.clear');
        Route::get('/pos/products', [\App\Http\Controllers\DDC\PosController::class, 'getProducts'])->name('pos.products');
        Route::get('/pos/customers', [\App\Http\Controllers\DDC\PosController::class, 'customers'])->name('pos.customers');
        Route::get('/pos/customers/search', [\App\Http\Controllers\DDC\PosController::class, 'searchCustomer'])->name('pos.customers.search');
        Route::post('/pos/customers/store', [\App\Http\Controllers\DDC\PosController::class, 'storeCustomer'])->name('pos.customers.store');
        Route::post('/pos/checkout', [\App\Http\Controllers\DDC\PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/held-carts/hold', [\App\Http\Controllers\DDC\PosController::class, 'holdCart'])->name('pos.held-carts.hold');
        Route::get('/pos/held-carts', [\App\Http\Controllers\DDC\PosController::class, 'getHeldCarts'])->name('pos.held-carts.index');
        Route::get('/pos/held-carts/check-customer', [\App\Http\Controllers\DDC\PosController::class, 'checkCustomerHeldCart'])->name('pos.held-carts.check-customer');
        Route::post('/pos/held-carts/{id}/resume', [\App\Http\Controllers\DDC\PosController::class, 'resumeHeldCart'])->name('pos.held-carts.resume');
        Route::delete('/pos/held-carts/{id}', [\App\Http\Controllers\DDC\PosController::class, 'deleteHeldCart'])->name('pos.held-carts.delete');
        Route::get('/pos/orders', [\App\Http\Controllers\DDC\PosController::class, 'orders'])->name('pos.orders');
        Route::get('/pos/orders/{id}', [\App\Http\Controllers\DDC\PosController::class, 'showOrder'])->name('pos.orders.show');
        Route::get('/pos/orders/{id}/print', [\App\Http\Controllers\DDC\PosController::class, 'printReceipt'])->name('pos.orders.print');

        // Sales Returns
        Route::get('/pos/returns', [\App\Http\Controllers\DDC\PosController::class, 'salesReturnsList'])->name('pos.returns.index');
        Route::post('/pos/returns/store', [\App\Http\Controllers\DDC\PosController::class, 'processSalesReturn'])->name('pos.returns.store');
        Route::get('/pos/returns/{id}/print', [\App\Http\Controllers\DDC\PosController::class, 'printReturnReceipt'])->name('pos.returns.print');

        // Sales Reports
        Route::get('/reports/sales', [\App\Http\Controllers\DDC\SalesReportController::class, 'index'])->name('reports.sales');
        Route::get('/reports/sales/export-csv', [\App\Http\Controllers\DDC\SalesReportController::class, 'exportCsv'])->name('reports.sales.export-csv');
        Route::get('/reports/sales/print', [\App\Http\Controllers\DDC\SalesReportController::class, 'print'])->name('reports.sales.print');

        // HSN-wise Sales Reports
        Route::get('/reports/hsn-sales', [\App\Http\Controllers\DDC\HsnSalesReportController::class, 'index'])->name('reports.hsn-sales');
        Route::get('/reports/hsn-sales/export-csv', [\App\Http\Controllers\DDC\HsnSalesReportController::class, 'exportCsv'])->name('reports.hsn-sales.export-csv');
        Route::get('/reports/hsn-sales/print', [\App\Http\Controllers\DDC\HsnSalesReportController::class, 'print'])->name('reports.hsn-sales.print');

        // Sales Returns Reports
        Route::get('/reports/sales-returns', [\App\Http\Controllers\DDC\SalesReturnReportController::class, 'index'])->name('reports.sales-returns');
        Route::get('/reports/sales-returns/export-csv', [\App\Http\Controllers\DDC\SalesReturnReportController::class, 'exportCsv'])->name('reports.sales-returns.export-csv');
        Route::get('/reports/sales-returns/print', [\App\Http\Controllers\DDC\SalesReturnReportController::class, 'print'])->name('reports.sales-returns.print');

        // Purchase Reports
        Route::get('/reports/purchases', [\App\Http\Controllers\DDC\PurchaseReportController::class, 'index'])->name('reports.purchases');
        Route::get('/reports/purchases/export-csv', [\App\Http\Controllers\DDC\PurchaseReportController::class, 'exportCsv'])->name('reports.purchases.export-csv');
        Route::get('/reports/purchases/print', [\App\Http\Controllers\DDC\PurchaseReportController::class, 'print'])->name('reports.purchases.print');

        // Product-wise Purchase Reports
        Route::get('/reports/product-purchases', [\App\Http\Controllers\DDC\ProductPurchaseReportController::class, 'index'])->name('reports.product-purchases');
        Route::get('/reports/product-purchases/export-csv', [\App\Http\Controllers\DDC\ProductPurchaseReportController::class, 'exportCsv'])->name('reports.product-purchases.export-csv');
        Route::get('/reports/product-purchases/print', [\App\Http\Controllers\DDC\ProductPurchaseReportController::class, 'print'])->name('reports.product-purchases.print');

        // Profit & Loss (P&L) Reports
        Route::get('/reports/pnl', [\App\Http\Controllers\DDC\ProfitLossReportController::class, 'index'])->name('reports.pnl');
        Route::get('/reports/pnl/export-csv', [\App\Http\Controllers\DDC\ProfitLossReportController::class, 'exportCsv'])->name('reports.pnl.export-csv');
        Route::get('/reports/pnl/print', [\App\Http\Controllers\DDC\ProfitLossReportController::class, 'print'])->name('reports.pnl.print');

        // Manage Rack / Shelf Module (Unit-Based)
        Route::get('/racks', [\App\Http\Controllers\DDC\RackManagementController::class, 'index'])->name('racks.index');
        Route::post('/racks', [\App\Http\Controllers\DDC\RackManagementController::class, 'store'])->name('racks.store');
        Route::post('/racks/{id}/toggle-status', [\App\Http\Controllers\DDC\RackManagementController::class, 'toggleStatus'])->name('racks.toggle-status');
        Route::post('/racks/upload-images', [\App\Http\Controllers\DDC\RackManagementController::class, 'uploadDepotImages'])->name('racks.upload-images');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
