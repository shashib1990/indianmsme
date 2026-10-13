<?php

use App\Http\Controllers\ADC\DashboardController;
use App\Http\Controllers\ADC\ProfileController;
use App\Http\Controllers\ADC\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADC Panel Routes
| Domain: adc.localhost / adc.indianmsme.org
|--------------------------------------------------------------------------
*/

// Auth Routes for ADC
Route::get('/login', [AuthController::class, 'showLogin'])->name('adc.login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('adc.login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('adc.logout');

// ── Location API (no auth needed — used by AJAX dropdowns) ──
Route::prefix('api/locations')->name('adc.api.locations.')->group(function () {
    Route::get('/states', [\App\Http\Controllers\Api\LocationApiController::class, 'states'])->name('states');
    Route::get('/districts', [\App\Http\Controllers\Api\LocationApiController::class, 'districts'])->name('districts');
    Route::get('/pincodes', [\App\Http\Controllers\Api\LocationApiController::class, 'pincodes'])->name('pincodes');
});

Route::name('adc.')->group(function () {
    Route::middleware(['auth:adc', 'force.password:adc', 'role.adc'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/analytics/sales-profit-chart', [DashboardController::class, 'salesProfitChartData'])->name('analytics.sales-profit-chart');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/profile/change-password', [ProfileController::class, 'showChangePassword'])
            ->name('profile.change-password')
            ->withoutMiddleware(['force.password:adc']);
        Route::post('/profile/change-password', [ProfileController::class, 'updatePassword'])
            ->name('profile.change-password.post')
            ->withoutMiddleware(['force.password:adc']);

        Route::get('/wallet', [\App\Http\Controllers\ADC\WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/request', [\App\Http\Controllers\ADC\WalletController::class, 'storeRequest'])->name('wallet.request.store');
        Route::post('/wallet/upload-receipt', [\App\Http\Controllers\ADC\WalletController::class, 'uploadReceipt'])->name('wallet.upload-receipt');

        // ── Cart Routes ──
        Route::get('/cart', [\App\Http\Controllers\Web\CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add', [\App\Http\Controllers\Web\CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [\App\Http\Controllers\Web\CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove', [\App\Http\Controllers\Web\CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [\App\Http\Controllers\Web\CartController::class, 'clear'])->name('cart.clear');

        // ── Checkout Routes ──
        Route::get('/checkout', [\App\Http\Controllers\Web\CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [\App\Http\Controllers\Web\CheckoutController::class, 'store'])->name('checkout.store');

        // ── Order Management Routes ──
        Route::get('/orders', [\App\Http\Controllers\ADC\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [\App\Http\Controllers\ADC\OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{id}/receive', [\App\Http\Controllers\ADC\OrderController::class, 'showReceivePage'])->name('orders.receive.show');
        Route::get('/orders/{id}/invoice', [\App\Http\Controllers\ADC\OrderController::class, 'printInvoice'])->name('orders.invoice');
        Route::post('/orders/{id}/upload-received-invoice', [\App\Http\Controllers\ADC\OrderController::class, 'uploadReceivedInvoice'])->name('orders.upload-invoice');
        Route::post('/orders/items/{itemId}/stock-in', [\App\Http\Controllers\ADC\OrderController::class, 'stockInItem'])->name('orders.items.stock-in');
        Route::post('/orders/{id}/received', [\App\Http\Controllers\ADC\OrderController::class, 'markReceived'])->name('orders.received');
        Route::get('/order-success/{id}', [\App\Http\Controllers\Web\CheckoutController::class, 'success'])->name('order.success');

        // ── Inventory Routes ──
        Route::get('/inventory', [\App\Http\Controllers\ADC\InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/export', [\App\Http\Controllers\ADC\InventoryController::class, 'export'])->name('inventory.export');
        Route::put('/inventory/{id}', [\App\Http\Controllers\ADC\InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/{id}/toggle-pos', [\App\Http\Controllers\ADC\InventoryController::class, 'togglePosStatus'])->name('inventory.toggle-pos');

        // ── Local Product & Purchase Module ──
        Route::get('/local-products/search-suggestions', [\App\Http\Controllers\ADC\LocalProductController::class, 'searchSuggestions'])->name('local-products.search-suggestions');
        Route::get('/local-products/category-hsn', [\App\Http\Controllers\ADC\LocalProductController::class, 'getCategoryHsn'])->name('local-products.category-hsn');
        Route::get('/local-products-comparison', [\App\Http\Controllers\ADC\LocalProductController::class, 'comparison'])->name('local-products.comparison');
        Route::post('/local-products/brands/quick-store', [\App\Http\Controllers\ADC\LocalProductController::class, 'quickStoreBrand'])->name('local-products.brands.quick-store');
        Route::resource('local-products', \App\Http\Controllers\ADC\LocalProductController::class);

        Route::resource('local-purchases', \App\Http\Controllers\ADC\LocalPurchaseController::class)->only(['index', 'create', 'store', 'show']);

        // ── Comprehensive ADC Reports Module (5 Core Reports) ──
        Route::get('/reports/sales', [\App\Http\Controllers\ADC\SalesReportController::class, 'index'])->name('reports.sales');
        Route::get('/reports/sales/export-csv', [\App\Http\Controllers\ADC\SalesReportController::class, 'exportCsv'])->name('reports.sales.export-csv');
        Route::get('/reports/sales/print', [\App\Http\Controllers\ADC\SalesReportController::class, 'print'])->name('reports.sales.print');

        Route::get('/reports/hsn-sales', [\App\Http\Controllers\ADC\HsnSalesReportController::class, 'index'])->name('reports.hsn-sales');
        Route::get('/reports/hsn-sales/export-csv', [\App\Http\Controllers\ADC\HsnSalesReportController::class, 'exportCsv'])->name('reports.hsn-sales.export-csv');
        Route::get('/reports/hsn-sales/print', [\App\Http\Controllers\ADC\HsnSalesReportController::class, 'print'])->name('reports.hsn-sales.print');

        Route::get('/reports/purchases', [\App\Http\Controllers\ADC\PurchaseReportController::class, 'index'])->name('reports.purchases');
        Route::get('/reports/purchases/export-csv', [\App\Http\Controllers\ADC\PurchaseReportController::class, 'exportCsv'])->name('reports.purchases.export-csv');
        Route::get('/reports/purchases/print', [\App\Http\Controllers\ADC\PurchaseReportController::class, 'print'])->name('reports.purchases.print');

        Route::get('/reports/sales-returns', [\App\Http\Controllers\ADC\SalesReturnReportController::class, 'index'])->name('reports.sales-returns');
        Route::get('/reports/sales-returns/export-csv', [\App\Http\Controllers\ADC\SalesReturnReportController::class, 'exportCsv'])->name('reports.sales-returns.export-csv');
        Route::get('/reports/sales-returns/print', [\App\Http\Controllers\ADC\SalesReturnReportController::class, 'print'])->name('reports.sales-returns.print');

        Route::get('/reports/pnl', [\App\Http\Controllers\ADC\ProfitLossReportController::class, 'index'])->name('reports.pnl');
        Route::get('/reports/pnl/export-csv', [\App\Http\Controllers\ADC\ProfitLossReportController::class, 'exportCsv'])->name('reports.pnl.export-csv');
        Route::get('/reports/pnl/print', [\App\Http\Controllers\ADC\ProfitLossReportController::class, 'print'])->name('reports.pnl.print');

        // ── Staff & Departments Module ──
        Route::resource('staff', \App\Http\Controllers\ADC\StaffController::class)->except(['show']);
        Route::resource('departments', \App\Http\Controllers\ADC\DepartmentController::class)->except(['create', 'show', 'edit']);

        // Legacy / Secondary Reports
        Route::get('/reports/invoice-wise', [\App\Http\Controllers\ADC\AdcReportController::class, 'invoiceWise'])->name('reports.invoice-wise');
        Route::get('/reports/invoice-wise/export', [\App\Http\Controllers\ADC\AdcReportController::class, 'exportInvoiceWiseCsv'])->name('reports.invoice-wise.export');
        Route::get('/reports/invoice-wise/print', [\App\Http\Controllers\ADC\AdcReportController::class, 'printInvoiceWise'])->name('reports.invoice-wise.print');

        Route::get('/reports/product-wise', [\App\Http\Controllers\ADC\AdcReportController::class, 'productWise'])->name('reports.product-wise');
        Route::get('/reports/product-wise/export', [\App\Http\Controllers\ADC\AdcReportController::class, 'exportProductWiseCsv'])->name('reports.product-wise.export');
        Route::get('/reports/product-wise/print', [\App\Http\Controllers\ADC\AdcReportController::class, 'printProductWise'])->name('reports.product-wise.print');

        Route::get('/reports/local-purchases', [\App\Http\Controllers\ADC\LocalPurchaseReportController::class, 'index'])->name('reports.local-purchases');
        Route::get('/reports/local-purchases/export', [\App\Http\Controllers\ADC\LocalPurchaseReportController::class, 'exportCsv'])->name('reports.local-purchases.export');
        Route::get('/reports/local-purchases/print', [\App\Http\Controllers\ADC\LocalPurchaseReportController::class, 'print'])->name('reports.local-purchases.print');

        // ── Manage Rack / Shelf Module (Unit-Based) ──
        Route::get('/racks', [\App\Http\Controllers\ADC\RackManagementController::class, 'index'])->name('racks.index');
        Route::post('/racks', [\App\Http\Controllers\ADC\RackManagementController::class, 'store'])->name('racks.store');
        Route::post('/racks/{id}/toggle-status', [\App\Http\Controllers\ADC\RackManagementController::class, 'toggleStatus'])->name('racks.toggle-status');
        Route::post('/racks/upload-images', [\App\Http\Controllers\ADC\RackManagementController::class, 'uploadDepotImages'])->name('racks.upload-images');
        // ── POS Billing Routes (Retail) ──
        Route::get('/pos', [\App\Http\Controllers\ADC\PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/retail', [\App\Http\Controllers\ADC\PosController::class, 'retail'])->name('pos.retail');
        Route::get('/pos/cart', [\App\Http\Controllers\ADC\PosController::class, 'getCart'])->name('pos.cart.get');
        Route::post('/pos/cart/sync', [\App\Http\Controllers\ADC\PosController::class, 'syncCart'])->name('pos.cart.sync');
        Route::post('/pos/cart/clear', [\App\Http\Controllers\ADC\PosController::class, 'clearCartDb'])->name('pos.cart.clear');
        Route::get('/pos/products', [\App\Http\Controllers\ADC\PosController::class, 'getProducts'])->name('pos.products');
        Route::get('/pos/customers', [\App\Http\Controllers\ADC\PosController::class, 'customers'])->name('pos.customers');
        Route::get('/pos/customers/search', [\App\Http\Controllers\ADC\PosController::class, 'searchCustomer'])->name('pos.customers.search');
        Route::post('/pos/customers/store', [\App\Http\Controllers\ADC\PosController::class, 'storeCustomer'])->name('pos.customers.store');
        Route::post('/pos/checkout', [\App\Http\Controllers\ADC\PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/held-carts/hold', [\App\Http\Controllers\ADC\PosController::class, 'holdCart'])->name('pos.held-carts.hold');
        Route::get('/pos/held-carts', [\App\Http\Controllers\ADC\PosController::class, 'getHeldCarts'])->name('pos.held-carts.index');
        Route::get('/pos/held-carts/check-customer', [\App\Http\Controllers\ADC\PosController::class, 'checkCustomerHeldCart'])->name('pos.held-carts.check-customer');
        Route::post('/pos/held-carts/{id}/resume', [\App\Http\Controllers\ADC\PosController::class, 'resumeHeldCart'])->name('pos.held-carts.resume');
        Route::delete('/pos/held-carts/{id}', [\App\Http\Controllers\ADC\PosController::class, 'deleteHeldCart'])->name('pos.held-carts.delete');
        Route::get('/pos/orders', [\App\Http\Controllers\ADC\PosController::class, 'orders'])->name('pos.orders');
        Route::get('/pos/orders/{id}', [\App\Http\Controllers\ADC\PosController::class, 'showOrder'])->name('pos.orders.show');
        Route::get('/pos/orders/{id}/print', [\App\Http\Controllers\ADC\PosController::class, 'printReceipt'])->name('pos.orders.print');

        // ── POS Sales Returns ──
        Route::get('/pos/returns', [\App\Http\Controllers\ADC\PosController::class, 'salesReturnsList'])->name('pos.returns.index');
        Route::get('/pos/returns/details/{order_id}', [\App\Http\Controllers\ADC\PosController::class, 'getOrderForReturn'])->name('pos.returns.details');
        Route::post('/pos/returns/store', [\App\Http\Controllers\ADC\PosController::class, 'processSalesReturn'])->name('pos.returns.store');
        Route::get('/pos/returns/{id}/print', [\App\Http\Controllers\ADC\PosController::class, 'printReturnReceipt'])->name('pos.returns.print');

        // ── POS Customer Credit / Udhaar Management ──
        Route::post('/pos/customers/send-message', [\App\Http\Controllers\ADC\PosController::class, 'sendCustomerMessage'])->name('pos.customers.send-message');
        Route::post('/pos/customers/broadcast-message', [\App\Http\Controllers\ADC\PosController::class, 'broadcastCustomerMessage'])->name('pos.customers.broadcast-message');
        Route::post('/pos/customers/receive-payment', [\App\Http\Controllers\ADC\PosController::class, 'receiveCustomerPayment'])->name('pos.customers.receive-payment');
        Route::get('/pos/customers/{id}/ledger', [\App\Http\Controllers\ADC\PosController::class, 'getCustomerLedger'])->name('pos.customers.ledger');
        Route::get('/pos/customers/{id}/ledger/print', [\App\Http\Controllers\ADC\PosController::class, 'printCustomerLedger'])->name('pos.customers.ledger.print');
        Route::get('/pos/payments/{id}/print', [\App\Http\Controllers\ADC\PosController::class, 'printPaymentReceipt'])->name('pos.payments.print');

        // ── Debit Notes, Credit Notes & Excess Invoices ──
        Route::get('/debit-notes/create/{orderId}', [\App\Http\Controllers\ADC\DebitNoteController::class, 'create'])->name('debit-notes.create');
        Route::post('/debit-notes/store/{orderId}', [\App\Http\Controllers\ADC\DebitNoteController::class, 'store'])->name('debit-notes.store');
        Route::get('/debit-notes', [\App\Http\Controllers\ADC\DebitNoteController::class, 'index'])->name('debit-notes.index');
        Route::get('/debit-notes/{id}', [\App\Http\Controllers\ADC\DebitNoteController::class, 'show'])->name('debit-notes.show');

        Route::get('/credit-notes', [\App\Http\Controllers\ADC\CreditNoteController::class, 'index'])->name('credit-notes.index');
        Route::get('/credit-notes/{id}', [\App\Http\Controllers\ADC\CreditNoteController::class, 'show'])->name('credit-notes.show');

        Route::get('/excess-invoices/create/{orderId}', [\App\Http\Controllers\ADC\ExcessInvoiceController::class, 'create'])->name('excess-invoices.create');
        Route::post('/excess-invoices/store/{orderId}', [\App\Http\Controllers\ADC\ExcessInvoiceController::class, 'store'])->name('excess-invoices.store');
        Route::get('/excess-invoices', [\App\Http\Controllers\ADC\ExcessInvoiceController::class, 'index'])->name('excess-invoices.index');
        Route::get('/excess-invoices/{id}', [\App\Http\Controllers\ADC\ExcessInvoiceController::class, 'show'])->name('excess-invoices.show');
        Route::post('/excess-invoices/{id}/pay', [\App\Http\Controllers\ADC\ExcessInvoiceController::class, 'pay'])->name('excess-invoices.pay');
        Route::delete('/excess-invoices/{id}/cancel', [\App\Http\Controllers\ADC\ExcessInvoiceController::class, 'cancel'])->name('excess-invoices.cancel');
    });
});
