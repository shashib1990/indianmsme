<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TestingWipeService
{
    protected DiagnosticsService $diagnosticsService;

    public function __construct(DiagnosticsService $diagnosticsService)
    {
        $this->diagnosticsService = $diagnosticsService;
    }

    /**
     * Execute controlled testing wipe when URL is hit.
     *
     * @param string|null $providedKey
     * @return array
     * @throws Exception
     */
    public function executeWipe(?string $providedKey): array
    {
        @set_time_limit(300);

        // Step 1: Environment & Authorization Verification
        $this->verifyEnvironmentAndKey($providedKey);

        // Step 2: Backup Prerequisite Check / Auto-generation
        $backupResult = $this->ensureBackupPrerequisite();

        $wipeId = 'WIPE-' . date('Ymd-His') . '-' . Str::random(6);
        Log::warning("Initiating maintenance testing wipe execution [{$wipeId}].");

        $stats = [
            'wipe_id'                  => $wipeId,
            'timestamp'                => date('Y-m-d H:i:s'),
            'environment'              => config('app.env'),
            'backup_reference'         => $backupResult['file_name'],
            'backup_path'              => $backupResult['file_path'],
            'removed_counts'           => [],
            'components_removed'       => [],
            'storage_areas_cleaned'    => [],
            'maintenance_self_removed' => [],
        ];

        try {
            // Step 3: Perform Database Cleanup
            $stats['removed_counts'] = $this->cleanupDatabase();

            // Step 4: Perform ADC & DDC Controllers, Middlewares, and Import Component Removals
            $stats['components_removed'] = $this->removeTargetedComponents();

            // Step 5: Clean Test Storage Files & Reset Storage Permissions
            $stats['storage_areas_cleaned'] = $this->cleanupStorageAreas();

            // Step 6: Self-remove Maintenance Route, Controller, Service & Config
            $stats['maintenance_self_removed'] = $this->selfRemoveMaintenanceModule();

            // Step 7: Clear Application Caches
            $this->clearApplicationCaches();

            // Step 8: Verify Application Integrity
            $this->verifyPostWipeIntegrity();

            Log::info("Maintenance testing wipe execution completed successfully [{$wipeId}].");

            return [
                'status'  => 'success',
                'message' => 'Controlled testing wipe executed successfully and maintenance endpoint self-removed.',
                'details' => $stats,
            ];

        } catch (Throwable $e) {
            Log::error("Maintenance testing wipe failed [{$wipeId}]: " . $e->getMessage(), [
                'exception' => $e,
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new Exception("Wipe execution failed: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Safety verification against production execution and secret authorization key.
     */
    protected function verifyEnvironmentAndKey(?string $providedKey): void
    {
        $environment = config('app.env', 'production');
        $wipeEnabled = (bool) config('app.wipe_testing_enabled', false);
        $configuredKey = config('app.wipe_testing_key');

        if (strtolower($environment) === 'production') {
            Log::alert("BLOCKED: Testing wipe execution attempted in PRODUCTION environment!");
            throw new Exception("Destructive operations strictly disabled in production environment.", 403);
        }

        if (!$wipeEnabled) {
            Log::warning("BLOCKED: Testing wipe endpoint hit while WIPE_TESTING_ENABLED is false.");
            throw new Exception("Maintenance testing wipe feature is disabled in application configuration.", 403);
        }

        if (empty($configuredKey) || empty($providedKey) || !hash_equals((string) $configuredKey, (string) $providedKey)) {
            Log::warning("BLOCKED: Unauthorized access attempt on maintenance testing wipe endpoint.");
            throw new Exception("Invalid or missing maintenance wipe authorization key.", 403);
        }
    }

    /**
     * Backup Prerequisite: Verifies recent backup exists or generates a fresh snapshot zip.
     */
    protected function ensureBackupPrerequisite(): array
    {
        $backupDir = base_path('backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $existingBackups = glob($backupDir . '/project_backup_*.zip');
        $now = time();

        foreach ($existingBackups as $backupFile) {
            if (is_file($backupFile) && ($now - filemtime($backupFile)) < 7200 && filesize($backupFile) > 0) {
                return [
                    'file_path' => $backupFile,
                    'file_name' => basename($backupFile),
                ];
            }
        }

        Log::info("Generating fresh backup snapshot prior to testing wipe.");
        return $this->diagnosticsService->generateSnapshot(false);
    }

    /**
     * Database cleanup logic preserving system admin users & master tables.
     */
    protected function cleanupDatabase(): array
    {
        $counts = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // A. User Database Cleanup
            $nonAdminUserIds = DB::table('users')
                ->where('role', '!=', 'admin')
                ->whereNotIn('id', function ($query) {
                    $query->select('user_id')->from('admin_profiles');
                })
                ->pluck('id')
                ->toArray();

            $counts['test_users_removed'] = count($nonAdminUserIds);

            if (!empty($nonAdminUserIds)) {
                DB::table('users')->whereIn('id', $nonAdminUserIds)->delete();
            }

            // User dependent profiles
            DB::table('seller_profiles')->truncate();
            DB::table('ddc_profiles')->truncate();
            DB::table('adc_profiles')->truncate();
            DB::table('pickup_stores')->truncate();
            DB::table('pickup_store_delivery_areas')->truncate();
            DB::table('ddc_codes')->truncate();
            DB::table('ddc_pincodes')->truncate();
            DB::table('ddc_pincode_audit_logs')->truncate();

            // B. Inventory & Product Stock Cleanup
            $tablesToTruncate = [
                'products',
                'sub_seller_products',
                'product_audit_logs',
                'seller_media',
                'depot_racks',
                'depot_rack_images',
                'depot_rack_bookings',
                'ddc_racks',
                'ddc_shelves',
                'ddc_rack_shelf_bookings',
                'adc_inventories',
                'ddc_inventories',
                'adc_local_products',
                'ddc_local_products',
                // C. Orders & Transactions
                'purchase_orders',
                'purchase_order_items',
                'invoices',
                'debit_notes',
                'debit_note_items',
                'credit_notes',
                'adc_orders',
                'adc_order_items',
                'adc_cart_items',
                'ddc_cart_items',
                'adc_pos_orders',
                'adc_pos_order_items',
                'adc_pos_carts',
                'adc_pos_customers',
                'adc_pos_sales_returns',
                'adc_pos_sales_return_items',
                'ddc_pos_orders',
                'ddc_pos_order_items',
                'ddc_pos_carts',
                'ddc_pos_customers',
                'ddc_pos_sales_returns',
                'ddc_pos_sales_return_items',
                'adc_local_purchases',
                'adc_local_purchase_items',
                'ddc_local_purchases',
                'ddc_local_purchase_items',
                // D. Financials, Ledgers & Wallets
                'adc_customer_credit_ledgers',
                'adc_customer_payments',
                'adc_credit_notes',
                'adc_debit_notes',
                'adc_debit_note_items',
                'adc_excess_invoices',
                'seller_wallets',
                'seller_wallet_requests',
                'seller_ssi_slabs',
                'ddc_wallets',
                'ddc_wallet_requests',
                'adc_wallets',
                'adc_wallet_requests',
                'pickup_store_wallets',
                'pickup_store_wallet_requests',
                'adpl_wallets',
                'incentive_holds',
                'incentive_releases',
                // E. System Logs & Operations
                'impersonation_logs',
                'contact_inquiries',
                'newsletter_subscribers',
                'notifications',
                'failed_jobs',
                'order_sequences',
            ];

            foreach ($tablesToTruncate as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $counts[$table] = DB::table($table)->count();
                    DB::table($table)->truncate();
                }
            }

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        return $counts;
    }

    /**
     * Remove ADC & DDC Controllers, ADC & DDC Middlewares, and Import Components on URL trigger.
     */
    protected function removeTargetedComponents(): array
    {
        $removedFiles = [];

        // 1. ADC Controllers Directory Removal
        $adcControllersDir = app_path('Http/Controllers/ADC');
        if (File::exists($adcControllersDir)) {
            File::deleteDirectory($adcControllersDir);
            $removedFiles[] = 'app/Http/Controllers/ADC/ (directory deleted)';
        }

        // 2. DDC Controllers Directory Removal
        $ddcControllersDir = app_path('Http/Controllers/DDC');
        if (File::exists($ddcControllersDir)) {
            File::deleteDirectory($ddcControllersDir);
            $removedFiles[] = 'app/Http/Controllers/DDC/ (directory deleted)';
        }

        // 3. Middlewares Removal (ADCMiddleware.php & DDCMiddleware.php)
        $middlewares = [
            app_path('Http/Middleware/ADCMiddleware.php'),
            app_path('Http/Middleware/DDCMiddleware.php'),
        ];
        foreach ($middlewares as $mwPath) {
            if (File::exists($mwPath)) {
                File::delete($mwPath);
                $removedFiles[] = str_replace(base_path() . '/', '', $mwPath) . ' (file deleted)';
            }
        }

        // 4. Import Controllers Removal
        $importControllers = [
            app_path('Http/Controllers/Admin/BulkImportController.php'),
            app_path('Http/Controllers/Admin/LocationImportController.php'),
        ];
        foreach ($importControllers as $ctrlPath) {
            if (File::exists($ctrlPath)) {
                File::delete($ctrlPath);
                $removedFiles[] = str_replace(base_path() . '/', '', $ctrlPath) . ' (file deleted)';
            }
        }

        // 5. app/Imports Directory Removal
        $importsDir = app_path('Imports');
        if (File::exists($importsDir)) {
            File::deleteDirectory($importsDir);
            $removedFiles[] = 'app/Imports/ (directory deleted)';
        }

        // 6. Import Unit Tests Removal
        $testFiles = glob(base_path('tests/Unit/*Import*.php'));
        foreach ($testFiles as $testFile) {
            if (File::exists($testFile)) {
                File::delete($testFile);
                $removedFiles[] = str_replace(base_path() . '/', '', $testFile) . ' (file deleted)';
            }
        }

        // 7. Import Views Removal
        $viewsPath = resource_path('views/admin/import');
        if (File::exists($viewsPath)) {
            File::deleteDirectory($viewsPath);
            $removedFiles[] = 'resources/views/admin/import/ (directory deleted)';
        }

        // 8. Remove import routes from routes/admin.php
        $adminRoutesPath = base_path('routes/admin.php');
        if (File::exists($adminRoutesPath)) {
            $content = File::get($adminRoutesPath);

            $content = preg_replace('/^use App\\\\Http\\\\Controllers\\\\Admin\\\\(Bulk|Location)ImportController;\s*$/m', '', $content);

            $routePattern = '/\s*\/\/\s*Bulk Account Import Module.*?\n\s*Route::prefix\(\'import\'\)->name\(\'import\.\'\)->group\(function\s*\(\)\s*\{.*?\n\s*\}\);/s';
            if (preg_match($routePattern, $content)) {
                $content = preg_replace($routePattern, '', $content);
            }

            $locationImportPattern = '/\s*\/\/\s*State import.*?\n\s*Route::get\(\'locations\/sample\/pincodes\',.*?\);/s';
            if (preg_match($locationImportPattern, $content)) {
                $content = preg_replace($locationImportPattern, '', $content);
            }

            File::put($adminRoutesPath, $content);
            $removedFiles[] = 'routes/admin.php (Import routes excised)';
        }

        // 9. Remove menu entry from resources/views/layouts/admin.blade.php
        $adminLayoutPath = resource_path('views/layouts/admin.blade.php');
        if (File::exists($adminLayoutPath)) {
            $content = File::get($adminLayoutPath);
            $navPattern = '/\s*<!--\s*Bulk Import\s*-->.*?\/\* Bulk Import End \*\//s';

            if (!preg_match($navPattern, $content)) {
                $navPattern = '/\s*<li class="nav-item">\s*<a class="nav-link menu-arrow [^"]*routeIs\(\'admin\.import\.\*\'\).*?<\/li>/s';
            }

            if (preg_match($navPattern, $content)) {
                $content = preg_replace($navPattern, '', $content);
                File::put($adminLayoutPath, $content);
                $removedFiles[] = 'resources/views/layouts/admin.blade.php (Bulk Import menu section removed)';
            }
        }

        return $removedFiles;
    }

    /**
     * Clean test files in approved storage paths and reset storage permissions & directory structures.
     */
    protected function cleanupStorageAreas(): array
    {
        $cleanedPaths = [];

        $publicDir = storage_path('app/public');
        if (File::exists($publicDir)) {
            $files = File::allFiles($publicDir);
            foreach ($files as $file) {
                if ($file->getFilename() !== '.gitignore') {
                    File::delete($file->getRealPath());
                }
            }
            $cleanedPaths[] = 'storage/app/public/*';
        }

        $tempImportsDir = storage_path('app/temp_imports');
        if (File::exists($tempImportsDir)) {
            File::cleanDirectory($tempImportsDir);
            $cleanedPaths[] = 'storage/app/temp_imports/*';
        }

        $testCsv = public_path('test_pincode_import.csv');
        if (File::exists($testCsv)) {
            File::delete($testCsv);
            $cleanedPaths[] = 'public/test_pincode_import.csv';
        }

        // Ensure essential storage directory structure exists with full permissions
        $requiredDirs = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/temp_imports'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/testing'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($requiredDirs as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true, true);
            }
        }

        // Reset storage & bootstrap permissions
        $storagePath = storage_path();
        $bootstrapCachePath = base_path('bootstrap/cache');

        @exec('chmod -R 0777 ' . escapeshellarg($storagePath));
        @exec('chmod -R 0777 ' . escapeshellarg($bootstrapCachePath));

        $this->chmodRecursive($storagePath, 0777, 0777);
        $this->chmodRecursive($bootstrapCachePath, 0777, 0777);

        $cleanedPaths[] = 'storage/ directory permissions reset to 0777';
        $cleanedPaths[] = 'bootstrap/cache/ directory permissions reset to 0777';

        return $cleanedPaths;
    }

    /**
     * Self-remove maintenance route, controller, service, CSRF exception, and config entry.
     */
    protected function selfRemoveMaintenanceModule(): array
    {
        $removed = [];

        // 1. Remove route from routes/web.php
        $webRoutesPath = base_path('routes/web.php');
        if (File::exists($webRoutesPath)) {
            $content = File::get($webRoutesPath);

            $pattern = '/\s*\/\/\s*──\s*Controlled Maintenance Testing Wipe Endpoint.*?\n\s*Route::match\(\[\'get\',\s*\'post\'\],\s*\'\/maintenance\/testing-wipe\'.*?\);/s';
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '', $content);
            } else {
                $content = preg_replace('/\s*Route::match\(\[\'get\',\s*\'post\'\],\s*\'\/maintenance\/testing-wipe\'.*?\);/s', '', $content);
            }

            File::put($webRoutesPath, $content);
            $removed[] = 'routes/web.php (Maintenance route excised)';
        }

        // 2. Remove CSRF exception from app/Http/Middleware/VerifyCsrfToken.php
        $csrfPath = app_path('Http/Middleware/VerifyCsrfToken.php');
        if (File::exists($csrfPath)) {
            $content = File::get($csrfPath);
            $content = preg_replace('/\s*\'maintenance\/testing-wipe\',?/', '', $content);
            File::put($csrfPath, $content);
            $removed[] = 'app/Http/Middleware/VerifyCsrfToken.php (CSRF exception excised)';
        }

        // 3. Remove maintenance config entries from config/app.php
        $configAppPath = config_path('app.php');
        if (File::exists($configAppPath)) {
            $content = File::get($configAppPath);
            $content = preg_replace('/\s*\/\/\s*Destructive Maintenance Testing Security Config.*?\n\s*\'wipe_testing_enabled\'.*?\n\s*\'wipe_testing_key\'.*?\n/s', "\n", $content);
            File::put($configAppPath, $content);
            $removed[] = 'config/app.php (Wipe testing configuration excised)';
        }

        // 4. Controller file self-deletion
        $controllerPath = app_path('Http/Controllers/MaintenanceWipeController.php');
        if (File::exists($controllerPath)) {
            File::delete($controllerPath);
            $removed[] = 'app/Http/Controllers/MaintenanceWipeController.php (Controller file deleted)';
        }

        // 5. Service file self-deletion
        $servicePath = app_path('Services/TestingWipeService.php');
        if (File::exists($servicePath)) {
            File::delete($servicePath);
            $removed[] = 'app/Services/TestingWipeService.php (Service file deleted)';
        }

        return $removed;
    }

    /**
     * Recursively set permissions on directory and files.
     */
    protected function chmodRecursive(string $path, int $dirPerms = 0777, int $filePerms = 0666): void
    {
        if (!file_exists($path)) {
            return;
        }

        @chmod($path, $dirPerms);

        if (is_dir($path)) {
            $items = @scandir($path);
            if ($items === false) {
                return;
            }
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $fullPath = $path . DIRECTORY_SEPARATOR . $item;
                if (is_dir($fullPath)) {
                    $this->chmodRecursive($fullPath, $dirPerms, $filePerms);
                } else {
                    @chmod($fullPath, $filePerms);
                }
            }
        }
    }

    /**
     * Clear Laravel caches to ensure clean application state.
     */
    protected function clearApplicationCaches(): void
    {
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
    }

    /**
     * Post-wipe validation to confirm application is operational.
     */
    protected function verifyPostWipeIntegrity(): void
    {
        $adminCount = DB::table('users')->where('role', 'admin')->count();
        if ($adminCount === 0) {
            throw new Exception("Post-wipe verification failed: System admin account missing!");
        }

        $statesCount = DB::table('states')->count();
        $categoriesCount = DB::table('categories')->count();

        if ($statesCount === 0 || $categoriesCount === 0) {
            throw new Exception("Post-wipe verification failed: Essential master data (States/Categories) compromised!");
        }
    }
}
