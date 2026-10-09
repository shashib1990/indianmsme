<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Models\User;

echo "========================================================\n";
echo "    FINAL DEEP VERIFICATION & HEALTH CHECK              \n";
echo "========================================================\n\n";

$errors = [];

// 1. Route Verification
echo "1. Testing Named Routes Registration...\n";
$routeCount = count(Route::getRoutes()->getRoutesByName());
echo "   [SUCCESS] Total Registered Named Routes: {$routeCount}\n";

// Check specific newly added routes
$newRoutes = ['admin.wallets.pickup_store', 'admin.wallets.pickup_store.recharge'];
foreach ($newRoutes as $nr) {
    if (!Route::has($nr)) {
        $errors[] = "Route {$nr} is missing from RouteCollection!";
    } else {
        echo "   [SUCCESS] Route '{$nr}' verified successfully.\n";
    }
}

// 2. Database Model Schema Alignment
echo "\n2. Verifying Eloquent Models vs MySQL Schema Alignment...\n";
$models = [
    \App\Models\User::class,
    \App\Models\AdminProfile::class,
    \App\Models\SellerProfile::class,
    \App\Models\DdcProfile::class,
    \App\Models\AdcProfile::class,
    \App\Models\PickupStore::class,
    \App\Models\Product::class,
    \App\Models\AdcOrder::class,
    \App\Models\PurchaseOrder::class,
    \App\Models\Invoice::class,
    \App\Models\SellerWallet::class,
    \App\Models\AdcWallet::class,
    \App\Models\DdcWallet::class,
    \App\Models\PickupStoreWallet::class,
];

foreach ($models as $mClass) {
    if (!class_exists($mClass)) {
        $errors[] = "Model class {$mClass} does not exist!";
        continue;
    }
    $instance = new $mClass();
    $table = $instance->getTable();
    if (!Schema::hasTable($table)) {
        $errors[] = "Table '{$table}' for model {$mClass} missing in MySQL!";
    } else {
        $cols = Schema::getColumnListing($table);
        $fillable = $instance->getFillable();
        $diff = array_diff($fillable, $cols);
        if (!empty($diff)) {
            $errors[] = "Model {$mClass} has invalid fillable columns: " . implode(', ', $diff);
        } else {
            echo "   [SUCCESS] Model {$mClass} -> Table '{$table}' fully aligned (" . count($cols) . " columns).\n";
        }
    }
}

// 3. Wallet Ledger Parity Check
echo "\n3. Verifying Signed Ledger Arithmetic & Wallet Parity...\n";

// DDC Wallets check
$ddcs = DB::table('ddc_profiles')->get();
$ddcErr = 0;
foreach ($ddcs as $d) {
    $stored = (float) ($d->wallet_balance ?? 0);
    $calculated = (float) (DB::table('ddc_wallets')
        ->where('ddc_id', $d->user_id)
        ->selectRaw("SUM(CASE WHEN LOWER(type) IN ('credit', 'add') THEN amount WHEN LOWER(type) IN ('debit', 'deduct') THEN -amount ELSE 0 END) as total")
        ->value('total') ?? 0);
    if (abs($stored - $calculated) > 0.01) {
        $ddcErr++;
    }
}
if ($ddcErr > 0) {
    $errors[] = "DDC wallet balance mismatch found in {$ddcErr} records!";
} else {
    echo "   [SUCCESS] DDC Wallet ledger sum equals stored profile wallet_balance across all records.\n";
}

// Pickup Store Wallets check
$stores = DB::table('pickup_stores')->get();
$storeErr = 0;
foreach ($stores as $st) {
    $stored = (float) ($st->wallet_balance ?? 0);
    $calculated = (float) (DB::table('pickup_store_wallets')
        ->where('pickup_store_id', $st->id)
        ->selectRaw("SUM(CASE WHEN LOWER(type) IN ('credit', 'add') THEN amount WHEN LOWER(type) IN ('debit', 'deduct') THEN -amount ELSE 0 END) as total")
        ->value('total') ?? 0);
    if (abs($stored - $calculated) > 0.01) {
        $storeErr++;
    }
}
if ($storeErr > 0) {
    $errors[] = "Pickup store wallet balance mismatch found in {$storeErr} records!";
} else {
    echo "   [SUCCESS] Pickup Store Wallet ledger sum equals stored wallet_balance across all records.\n";
}

// 4. Blade View Rendering Verification
echo "\n4. Rendering Core Panel Views in Test Context...\n";
$viewErrorBag = new \Illuminate\Support\ViewErrorBag();
View::share('errors', $viewErrorBag);

$testViews = [
    'admin.wallets.pickup_store' => ['stores' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15), 'search' => '', 'limit' => 15],
    'admin.wallets.index' => ['users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15), 'type' => 'ddc'],
    'admin.external.ddc.index' => ['users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15), 'search' => '', 'limit' => 15, 'sort' => 'created_at', 'order' => 'desc', 'type' => ''],
    'admin.external.adc.index' => ['users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15), 'search' => '', 'limit' => 15, 'sort' => 'created_at', 'order' => 'desc', 'type' => ''],
    'admin.external.vendors.index' => ['users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15), 'search' => '', 'limit' => 15, 'sort' => 'created_at', 'order' => 'desc', 'sellerType' => '', 'parentSeller' => null],
];

// Mock super admin user authentication context
$adminUser = User::where('role', 'admin')->first() ?? new User(['id' => 1, 'name' => 'Admin', 'role' => 'admin']);
auth('admin')->setUser($adminUser);

foreach ($testViews as $viewName => $data) {
    try {
        $html = View::make($viewName, $data)->render();
        echo "   [SUCCESS] View '{$viewName}' rendered cleanly (" . strlen($html) . " bytes output).\n";
    } catch (\Throwable $e) {
        $errors[] = "View '{$viewName}' failed to render: " . $e->getMessage();
    }
}

echo "\n========================================================\n";
if (empty($errors)) {
    echo "   ALL AUDIT CHECKS PASSED PERFECTLY WITH ZERO ERRORS!   \n";
} else {
    echo "   AUDIT FAILED WITH THE FOLLOWING ISSUES:              \n";
    foreach ($errors as $err) {
        echo "   - {$err}\n";
    }
}
echo "========================================================\n";
