<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = config('database.default');
        if ($connection !== 'mysql') {
            return;
        }

        $dbName = DB::connection()->getDatabaseName();

        // 1. Enforce utf8mb4 and utf8mb4_unicode_ci at database level
        try {
            DB::statement("ALTER DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            // Ignore if permission denied on database-level ALTER
        }

        // Disable foreign key checks during batch alter
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            // 2. Fetch all base tables in current schema
            $tables = DB::select("
                SELECT TABLE_NAME, ENGINE, TABLE_COLLATION 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ? 
                  AND TABLE_TYPE = 'BASE TABLE'
            ", [$dbName]);

            foreach ($tables as $table) {
                $tableName = $table->TABLE_NAME;
                $engine = strtoupper($table->ENGINE ?? '');
                $collation = strtolower($table->TABLE_COLLATION ?? '');

                // Convert MyISAM or any non-InnoDB engine to InnoDB
                if ($engine !== 'INNODB') {
                    DB::statement("ALTER TABLE `{$tableName}` ENGINE = InnoDB");
                }

                // Convert any non-utf8mb4_unicode_ci table (e.g. latin1, latin1_swedish_ci) to utf8mb4_unicode_ci
                if ($collation !== 'utf8mb4_unicode_ci') {
                    DB::statement("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
            }

            // 3. Scan for any specific columns that still have latin1 collation and convert them
            $latin1Columns = DB::select("
                SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = ? 
                  AND (COLLATION_NAME LIKE '%latin1%' OR CHARACTER_SET_NAME LIKE '%latin1%')
            ", [$dbName]);

            foreach ($latin1Columns as $col) {
                $nullSql = ($col->IS_NULLABLE === 'YES') ? 'NULL' : 'NOT NULL';
                $defaultSql = ($col->COLUMN_DEFAULT !== null) ? "DEFAULT " . DB::getPdo()->quote($col->COLUMN_DEFAULT) : '';
                if ($col->IS_NULLABLE === 'YES' && $col->COLUMN_DEFAULT === null) {
                    $defaultSql = 'DEFAULT NULL';
                }

                try {
                    DB::statement("
                        ALTER TABLE `{$col->TABLE_NAME}` 
                        MODIFY `{$col->COLUMN_NAME}` {$col->COLUMN_TYPE} 
                        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci {$nullSql} {$defaultSql}
                    ");
                } catch (\Throwable $e) {
                    // Fallback to table convert
                    DB::statement("ALTER TABLE `{$col->TABLE_NAME}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
            }
        } finally {
            // Always re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Conversion to InnoDB & utf8mb4 is a permanent upgrade and does not need reversal.
    }
};
