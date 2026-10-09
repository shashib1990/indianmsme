<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ddcs = DB::table('ddc_profiles')->where('wallet_balance', '>', 0)->get();
foreach ($ddcs as $d) {
    $hasTrans = DB::table('ddc_wallets')->where('ddc_id', $d->user_id)->exists();
    if (!$hasTrans) {
        DB::table('ddc_wallets')->insert([
            'ddc_id' => $d->user_id,
            'amount' => $d->wallet_balance,
            'type' => 'credit',
            'description' => 'Initial Opening Balance',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Backfilled DDC #{$d->user_id} with opening balance: {$d->wallet_balance}\n";
    }
}
