<?php

use App\Http\Controllers\PickupStore\AuthController;
use App\Http\Controllers\PickupStore\DashboardController;
use App\Http\Controllers\PickupStore\ProfileController;
use Illuminate\Support\Facades\Route;

Route::name('store.')->group(function () {
    Route::middleware(['guest:pickup_store'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    });

    Route::middleware(['auth:pickup_store'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');

        Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password.post');

        Route::get('/purchase-orders', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/all', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'allOrders'])->name('purchase-orders.all');
        Route::get('/purchase-orders/verify', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'verifyOrders'])->name('purchase-orders.verify');
        Route::get('/purchase-orders/ready', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'readyOrders'])->name('purchase-orders.ready');
        Route::get('/purchase-orders/dispatched', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'dispatchedOrders'])->name('purchase-orders.dispatched');
        Route::get('/purchase-orders/received', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'receivedOrders'])->name('purchase-orders.received');
        Route::get('/purchase-orders/{id}', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-orders/{id}/invoice', [\App\Http\Controllers\InvoiceController::class, 'printInvoice'])->name('purchase-orders.invoice');
        Route::post('/purchase-orders/{id}/invoice/transporter', [\App\Http\Controllers\InvoiceController::class, 'updateTransporter'])->name('purchase-orders.invoice.transporter');
        Route::post('/purchase-orders/{id}/invoice/upload', [\App\Http\Controllers\InvoiceController::class, 'uploadInvoiceFile'])->name('purchase-orders.invoice.upload');
        Route::post('/purchase-orders/{id}/verify-payment', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'verifyPayment'])->name('purchase-orders.verify-payment');
        Route::post('/purchase-orders/{id}/dispatch', [\App\Http\Controllers\PickupStore\PurchaseOrderController::class, 'dispatchOrder'])->name('purchase-orders.dispatch');

        // Authorized Pickup Store Wallet Routes
        Route::get('/wallets', [\App\Http\Controllers\PickupStore\WalletController::class, 'index'])->name('wallets.index');
        Route::post('/wallets/recharge', [\App\Http\Controllers\PickupStore\WalletController::class, 'recharge'])->name('wallets.recharge');
        Route::post('/wallets/topup-request', [\App\Http\Controllers\PickupStore\WalletController::class, 'storeTopupRequest'])->name('wallets.topup-request');
        Route::post('/wallets/withdrawal-request', [\App\Http\Controllers\PickupStore\WalletController::class, 'storeWithdrawalRequest'])->name('wallets.withdrawal-request');
        Route::post('/wallets/requests/{id}/verify-payout', [\App\Http\Controllers\PickupStore\WalletController::class, 'verifyPayout'])->name('wallets.verify-payout');
        Route::post('/wallets/upload-receipt', [\App\Http\Controllers\PickupStore\WalletController::class, 'uploadReceipt'])->name('wallets.upload-receipt');

        // Authorized Pickup Store Debit Notes, Credit Notes & Excess Invoices
        Route::get('/debit-notes', [\App\Http\Controllers\PickupStore\DebitNoteController::class, 'index'])->name('debit-notes.index');
        Route::get('/debit-notes/{id}', [\App\Http\Controllers\PickupStore\DebitNoteController::class, 'show'])->name('debit-notes.show');
        Route::post('/debit-notes/{id}/respond', [\App\Http\Controllers\PickupStore\DebitNoteController::class, 'respond'])->name('debit-notes.respond');
        Route::get('/adc-debit-notes/{id}', [\App\Http\Controllers\PickupStore\DebitNoteController::class, 'showAdcDebitNote'])->name('adc-debit-notes.show');
        Route::post('/adc-debit-notes/{id}/respond', [\App\Http\Controllers\PickupStore\DebitNoteController::class, 'respondAdcDebitNote'])->name('adc-debit-notes.respond');

        Route::get('/credit-notes', [\App\Http\Controllers\PickupStore\CreditNoteController::class, 'index'])->name('credit-notes.index');
        Route::get('/credit-notes/{id}', [\App\Http\Controllers\PickupStore\CreditNoteController::class, 'show'])->name('credit-notes.show');
        Route::get('/adc-credit-notes/{id}', [\App\Http\Controllers\PickupStore\CreditNoteController::class, 'showAdcCreditNote'])->name('adc-credit-notes.show');

        Route::get('/excess-invoices', [\App\Http\Controllers\PickupStore\ExcessInvoiceController::class, 'index'])->name('excess-invoices.index');
        Route::get('/excess-invoices/{id}', [\App\Http\Controllers\PickupStore\ExcessInvoiceController::class, 'show'])->name('excess-invoices.show');
        Route::get('/adc-excess-invoices/{id}', [\App\Http\Controllers\PickupStore\ExcessInvoiceController::class, 'showAdcExcessInvoice'])->name('adc-excess-invoices.show');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
