<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixDatabaseAutoIncrements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-keys {--dry-run : Only show tables to be modified without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect all database tables and permanently fix missing PRIMARY KEY and AUTO_INCREMENT on id columns';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dbName = config('database.connections.mysql.database');
        $isDryRun = $this->option('dry-run');

        $this->info("Scanning database: {$dbName} for missing Primary Keys and Auto-Increments...");

        $columns = DB::select("
            SELECT TABLE_NAME, COLUMN_TYPE, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND COLUMN_NAME = 'id'
            ORDER BY TABLE_NAME ASC
        ", [$dbName]);

        $fixedCount = 0;
        $skippedCount = 0;

        foreach ($columns as $col) {
            $table = $col->TABLE_NAME;
            $colType = $col->COLUMN_TYPE;
            $extra = $col->EXTRA ?? '';

            // Check if already auto_increment
            $hasAutoIncrement = strpos($extra, 'auto_increment') !== false;

            // Check if table has primary key
            $pkCheck = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
            $hasPrimaryKey = !empty($pkCheck);
            $pkColumn = $hasPrimaryKey ? ($pkCheck[0]->Column_name ?? '') : null;

            if ($hasAutoIncrement && $hasPrimaryKey && $pkColumn === 'id') {
                $skippedCount++;
                continue;
            }

            // Build ALTER statement
            $sql = null;
            if (!$hasPrimaryKey) {
                $sql = "ALTER TABLE `{$table}` MODIFY `id` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)";
            } elseif ($pkColumn === 'id' && !$hasAutoIncrement) {
                $sql = "ALTER TABLE `{$table}` MODIFY `id` {$colType} NOT NULL AUTO_INCREMENT";
            } else {
                $this->warn("Skipping table `{$table}`: Existing Primary Key is on column `{$pkColumn}` (not `id`).");
                continue;
            }

            if ($isDryRun) {
                $this->line("[DRY-RUN] Would run: {$sql}");
                $fixedCount++;
            } else {
                try {
                    DB::statement($sql);
                    $this->info("✓ Fixed table `{$table}` (id -> {$colType} AUTO_INCREMENT PRIMARY KEY)");
                    $fixedCount++;
                } catch (\Throwable $e) {
                    $this->error("✗ Failed to fix table `{$table}`: " . $e->getMessage());
                }
            }
        }

        $this->line("---------------------------------------------------------");
        if ($isDryRun) {
            $this->info("Dry-run complete. {$fixedCount} table(s) need fixing, {$skippedCount} table(s) are already fine.");
        } else {
            $this->info("Success! {$fixedCount} table(s) fixed with PRIMARY KEY & AUTO_INCREMENT. {$skippedCount} table(s) already fine.");
        }

        return 0;
    }
}
