<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "========================================================\n";
echo "       DEEP PROJECT AUDIT & CROSS-VERIFICATION          \n";
echo "========================================================\n";

// ---------------------------------------------------------
// 1. NESTED ROUTE & BLADE LITERAL CROSS-VERIFICATION
// ---------------------------------------------------------
echo "\n[1] AUDITING ROUTES AND ROUTE FILES...\n";

$registeredRoutes = [];
foreach (Route::getRoutes()->getRoutes() as $r) {
    if ($r->getName()) {
        $registeredRoutes[$r->getName()] = [
            'uri' => $r->uri(),
            'methods' => implode('|', $r->methods()),
            'action' => $r->getActionName(),
        ];
    }
}

// Check route files for nested duplicate names (e.g. Route::name('admin.')->group(function() { Route::name('admin.')... }))
$routeFiles = File::allFiles(base_path('routes'));
$routeFilePrefixIssues = [];
foreach ($routeFiles as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());
    
    // Look for nested Route::name() or as => inside group
    if (preg_match_all('/Route::name\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $m)) {
        if (count($m[1]) != count(array_unique($m[1]))) {
            $routeFilePrefixIssues[] = [
                'file' => $relativePath,
                'names' => $m[1]
            ];
        }
    }
}

// Scan all Blade files, Controllers, Services, Requests for route('...') calls
$filesToScan = array_merge(
    File::isDirectory(resource_path('views')) ? File::allFiles(resource_path('views')) : [],
    File::isDirectory(app_path('Http/Controllers')) ? File::allFiles(app_path('Http/Controllers')) : [],
    File::isDirectory(app_path('Services')) ? File::allFiles(app_path('Services')) : [],
    File::isDirectory(app_path('Http/Middleware')) ? File::allFiles(app_path('Http/Middleware')) : [],
    File::isDirectory(app_path('Http/Requests')) ? File::allFiles(app_path('Http/Requests')) : []
);

$invalidRouteReferences = [];
$doublePrefixRoutes = [];

foreach ($filesToScan as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());

    // Matches route('name', ...) or route("name", ...)
    if (preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches)) {
        foreach ($matches[1] as $routeName) {
            // Check double prefix
            if (preg_match('/^(admin\.admin|vendor\.vendor|ddc\.ddc|adc\.adc|api\.api)\./', $routeName)) {
                $doublePrefixRoutes[] = [
                    'route' => $routeName,
                    'file' => $relativePath
                ];
            }

            // Check if route exists in registered routes
            if (!isset($registeredRoutes[$routeName])) {
                $invalidRouteReferences[] = [
                    'route' => $routeName,
                    'file' => $relativePath
                ];
            }
        }
    }
}

echo " - Registered named routes count: " . count($registeredRoutes) . "\n";
echo " - Double prefix route references in code: " . count($doublePrefixRoutes) . "\n";
echo " - Invalid/Missing route references in code: " . count($invalidRouteReferences) . "\n";

// ---------------------------------------------------------
// 2. DATABASE SCHEMA VS ELOQUENT MODEL ALIGNMENT
// ---------------------------------------------------------
echo "\n[2] AUDITING ELOQUENT MODELS & DATABASE SCHEMAS...\n";

$modelFiles = File::allFiles(app_path('Models'));
$missingTables = [];
$fillableColumnMismatch = [];
$relationNullChecksNeeded = [];

foreach ($modelFiles as $file) {
    $relativePath = str_replace(app_path() . '/', '', $file->getRealPath());
    $className = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $relativePath);

    if (class_exists($className)) {
        try {
            $reflection = new ReflectionClass($className);
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) {
                continue;
            }

            $model = new $className();
            $tableName = $model->getTable();

            if (!Schema::hasTable($tableName)) {
                $missingTables[] = [
                    'class' => $className,
                    'table' => $tableName
                ];
                continue;
            }

            $tableColumns = Schema::getColumnListing($tableName);
            $fillable = $model->getFillable();

            $invalidFillable = array_diff($fillable, $tableColumns);
            if (!empty($invalidFillable)) {
                $fillableColumnMismatch[] = [
                    'class' => $className,
                    'table' => $tableName,
                    'invalid_columns' => array_values($invalidFillable)
                ];
            }

        } catch (\Throwable $e) {
            echo "   [ERROR] Checking model $className: " . $e->getMessage() . "\n";
        }
    }
}

echo " - Missing DB tables count: " . count($missingTables) . "\n";
echo " - Fillable column mismatch count: " . count($fillableColumnMismatch) . "\n";

// ---------------------------------------------------------
// 3. USER, ROLE & PROFILE DELEGATION LOGIC
// ---------------------------------------------------------
echo "\n[3] AUDITING PROFILE DELEGATION & AUTHORIZATION LOGIC...\n";

$nullUnsafeProfiles = [];
$validationRuleIssues = [];

foreach ($filesToScan as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());

    // Detect unsafe property access on profile without null checks (e.g., $user->profile->col instead of $user->profile?->col or optional($user->profile)->col)
    // Matches `$var->profile->field` but NOT `$var->profile?->field`
    if (preg_match_all('/\$[a-zA-Z0-9_]+\s*->\s*profile\s*->\s*([a-zA-Z0-9_]+)/', $content, $m)) {
        $nullUnsafeProfiles[] = [
            'file' => $relativePath,
            'access' => array_unique($m[0])
        ];
    }

    // Detect unique validations like 'unique:users' or 'unique:users,email' without proper ignore or multi-guard handling
    if (preg_match_all("/Rule::unique\([^)]+\)|'unique:[^']+'/", $content, $m)) {
        foreach ($m[0] as $rule) {
            if (strpos($rule, 'users') !== false && strpos($rule, 'ignore') === false && strpos($rule, '$') === false) {
                $validationRuleIssues[] = [
                    'file' => $relativePath,
                    'rule' => $rule
                ];
            }
        }
    }
}

echo " - Null-unsafe profile property accesses: " . count($nullUnsafeProfiles) . "\n";
echo " - Hardcoded unique:users rules missing ignore ID: " . count($validationRuleIssues) . "\n";

// ---------------------------------------------------------
// 4. FINANCIAL LEDGER & WALLET ARITHMETIC
// ---------------------------------------------------------
echo "\n[4] AUDITING FINANCIAL LEDGERS & WALLET CALCULATIONS...\n";

// Check DB wallet tables and compare calculated signed ledger balance vs stored balance
$walletTablesToAudit = [
    'seller_profiles' => [
        'profile_table' => 'seller_profiles',
        'balance_col' => 'wallet_balance',
        'id_col' => 'id',
        'trans_table' => 'seller_wallet_transactions',
        'trans_foreign_key' => 'seller_profile_id', // check table columns
    ],
    'adc_profiles' => [
        'profile_table' => 'adc_profiles',
        'balance_col' => 'wallet_balance',
        'id_col' => 'id',
        'trans_table' => 'adc_wallet_transactions',
        'trans_foreign_key' => 'adc_profile_id',
    ],
    'ddc_profiles' => [
        'profile_table' => 'ddc_profiles',
        'balance_col' => 'wallet_balance',
        'id_col' => 'id',
        'trans_table' => 'ddc_wallet_transactions',
        'trans_foreign_key' => 'ddc_profile_id',
    ],
    'pickup_stores' => [
        'profile_table' => 'pickup_stores',
        'balance_col' => 'wallet_balance',
        'id_col' => 'id',
        'trans_table' => 'pickup_store_wallets',
        'trans_foreign_key' => 'pickup_store_id',
    ],
];

$ledgerAuditResults = [];

foreach ($walletTablesToAudit as $key => $cfg) {
    if (Schema::hasTable($cfg['profile_table']) && Schema::hasTable($cfg['trans_table'])) {
        $transCols = Schema::getColumnListing($cfg['trans_table']);
        $fk = in_array($cfg['trans_foreign_key'], $transCols) ? $cfg['trans_foreign_key'] : (in_array('user_id', $transCols) ? 'user_id' : null);
        
        $typeCol = in_array('type', $transCols) ? 'type' : (in_array('transaction_type', $transCols) ? 'transaction_type' : null);
        $amountCol = in_array('amount', $transCols) ? 'amount' : null;

        $ledgerAuditResults[$key] = [
            'trans_table' => $cfg['trans_table'],
            'fk' => $fk,
            'type_col' => $typeCol,
            'amount_col' => $amountCol,
            'cols' => $transCols
        ];

        if ($fk && $typeCol && $amountCol) {
            // Check signed query calculation
            $discrepancies = DB::table($cfg['profile_table'] . ' as p')
                ->leftJoin($cfg['trans_table'] . ' as t', 'p.id', '=', 't.' . $fk)
                ->select(
                    'p.id',
                    'p.' . $cfg['balance_col'] . ' as stored_balance',
                    DB::raw("SUM(CASE WHEN t.{$typeCol} IN ('credit', 'CREDIT', 'add') THEN t.{$amountCol} WHEN t.{$typeCol} IN ('debit', 'DEBIT', 'deduct') THEN -t.{$amountCol} ELSE 0 END) as calculated_balance")
                )
                ->groupBy('p.id', 'p.' . $cfg['balance_col'])
                ->havingRaw("ABS(p.{$cfg['balance_col']} - COALESCE(calculated_balance, 0)) > 0.01")
                ->get();

            $ledgerAuditResults[$key]['discrepancies_count'] = count($discrepancies);
            $ledgerAuditResults[$key]['discrepancies'] = $discrepancies;
        }
    }
}

// ---------------------------------------------------------
// 5. PRODUCT CATALOG & BULK EXCEL IMPORT/EXPORT
// ---------------------------------------------------------
echo "\n[5] AUDITING EXCEL IMPORTS/EXPORTS AND IMAGE DISK RESOLUTION...\n";

$excelFiles = array_merge(
    File::allFiles(app_path('Imports')),
    File::allFiles(app_path('Exports'))
);

$excelAudit = [];
foreach ($excelFiles as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());
    $excelAudit[] = [
        'file' => $relativePath,
        'has_headings' => (strpos($content, 'WithHeadings') !== false),
        'has_mapping' => (strpos($content, 'WithMapping') !== false),
    ];
}

// Check image handling for Storage::disk('public') vs public_path / asset
$imagePathIssues = [];
foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());

    if (preg_match_all('/(Storage::disk\([^)]+\)|asset\(|public_path\([^)]+\))/', $content, $m)) {
        // check for inconsistencies
    }
}

echo " - Excel Import/Export classes count: " . count($excelFiles) . "\n";

// ---------------------------------------------------------
// 6. AJAX, API & FRONTEND VERIFICATION
// ---------------------------------------------------------
echo "\n[6] AUDITING FRONTEND AJAX ENDPOINTS & CSRF HEADERS...\n";

$ajaxCalls = [];
$missingCsrfInAjax = [];

foreach (File::allFiles(resource_path('views')) as $file) {
    $content = file_get_contents($file->getRealPath());
    $relativePath = str_replace(base_path() . '/', '', $file->getRealPath());

    // Detect $.ajax, fetch, or axios calls
    if (preg_match_all('/(\$\.ajax|fetch\(|axios\.(get|post|put|delete))/', $content, $m)) {
        // Check if CSRF token header is present when doing POST/PUT/DELETE
        if (preg_match('/method\s*:\s*[\'"](POST|PUT|DELETE)[\'"]/i', $content) || preg_match('/type\s*:\s*[\'"](POST|PUT|DELETE)[\'"]/i', $content) || strpos($content, 'axios.post') !== false) {
            if (strpos($content, 'X-CSRF-TOKEN') === false && strpos($content, '_token') === false && strpos($content, 'csrf_token') === false) {
                $missingCsrfInAjax[] = $relativePath;
            }
        }
    }
}

echo " - Views with potential missing CSRF in AJAX: " . count($missingCsrfInAjax) . "\n";

// Save everything to scratch/audit_full.json
$auditOutput = [
    'routes' => [
        'invalid_references' => $invalidRouteReferences,
        'double_prefixes' => $doublePrefixRoutes,
        'file_prefix_issues' => $routeFilePrefixIssues,
    ],
    'models' => [
        'missing_tables' => $missingTables,
        'fillable_mismatch' => $fillableColumnMismatch,
    ],
    'profiles' => [
        'null_unsafe' => $nullUnsafeProfiles,
        'validation_issues' => $validationRuleIssues,
    ],
    'financial' => [
        'ledger_audit' => $ledgerAuditResults,
    ],
    'excel' => $excelAudit,
    'ajax' => [
        'missing_csrf_views' => array_unique($missingCsrfInAjax),
    ]
];

file_put_contents(base_path('scratch/audit_full.json'), json_encode($auditOutput, JSON_PRETTY_PRINT));
echo "\nFull Audit output written to scratch/audit_full.json\n";
