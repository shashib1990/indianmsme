<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== AUDITING WALLET LEDGER PARITY IN MYSQL ===\n";

// 1. Seller Wallets
$sellers = DB::table('seller_profiles')->get();
$sellerDiscrepancies = 0;
foreach ($sellers as $s) {
    $stored = (float) ($s->wallet_balance ?? 0);
    $calculated = (float) (DB::table('seller_wallet_transactions')
        ->where('seller_profile_id', $s->id)
        ->selectRaw("SUM(CASE WHEN LOWER(type) IN ('credit', 'add') THEN amount WHEN LOWER(type) IN ('debit', 'deduct') THEN -amount ELSE 0 END) as total")
        ->value('total') ?? 0);
    if (abs($stored - $calculated) > 0.01) {
        $sellerDiscrepancies++;
        echo "[Seller #{$s->id}] Stored: {$stored}, Ledger Sum: {$calculated}\n";
    }
}
echo "Seller Wallet Discrepancies: {$sellerDiscrepancies}\n";

// 2. ADC Wallets
$adcs = DB::table('adc_profiles')->get();
$adcDiscrepancies = 0;
foreach ($adcs as $a) {
    $stored = (float) ($a->wallet_balance ?? 0);
    $calculated = (float) (DB::table('adc_wallets')
        ->where('adc_id', $a->user_id)
        ->selectRaw("SUM(CASE WHEN LOWER(type) IN ('credit', 'add') THEN amount WHEN LOWER(type) IN ('debit', 'deduct') THEN -amount ELSE 0 END) as total")
        ->value('total') ?? 0);
    if (abs($stored - $calculated) > 0.01) {
        $adcDiscrepancies++;
        echo "[ADC Profile #{$a->id}, User #{$a->user_id}] Stored: {$stored}, Ledger Sum: {$calculated}\n";
    }
}
echo "ADC Wallet Discrepancies: {$adcDiscrepancies}\n";

// 3. DDC Wallets
$ddcs = DB::table('ddc_profiles')->get();
$ddcDiscrepancies = 0;
foreach ($ddcs as $d) {
    $stored = (float) ($d->wallet_balance ?? 0);
    $calculated = (float) (DB::table('ddc_wallets')
        ->where('ddc_id', $d->user_id)
        ->selectRaw("SUM(CASE WHEN LOWER(type) IN ('credit', 'add') THEN amount WHEN LOWER(type) IN ('debit', 'deduct') THEN -amount ELSE 0 END) as total")
        ->value('total') ?? 0);
    if (abs($stored - $calculated) > 0.01) {
        $ddcDiscrepancies++;
        echo "[DDC Profile #{$d->id}, User #{$d->user_id}] Stored: {$stored}, Ledger Sum: {$calculated}\n";
    }
}
echo "DDC Wallet Discrepancies: {$ddcDiscrepancies}\n";

// 4. Pickup Store Wallets
$stores = DB::table('pickup_stores')->get();
$storeDiscrepancies = 0;
foreach ($stores as $st) {
    $stored = (float) ($st->wallet_balance ?? 0);
    $calculated = (float) (DB::table('pickup_store_wallets')
        ->where('pickup_store_id', $st->id)
        ->selectRaw("SUM(CASE WHEN LOWER(type) IN ('credit', 'add') THEN amount WHEN LOWER(type) IN ('debit', 'deduct') THEN -amount ELSE 0 END) as total")
        ->value('total') ?? 0);
    if (abs($stored - $calculated) > 0.01) {
        $storeDiscrepancies++;
        echo "[PickupStore #{$st->id}] Stored: {$stored}, Ledger Sum: {$calculated}\n";
    }
}
echo "Pickup Store Wallet Discrepancies: {$storeDiscrepancies}\n";
