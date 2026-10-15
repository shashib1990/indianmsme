<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $dbName = config('database.connections.mysql.database');

        $columns = DB::select("
            SELECT TABLE_NAME, COLUMN_TYPE, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND COLUMN_NAME = 'id'
            ORDER BY TABLE_NAME ASC
        ", [$dbName]);

        foreach ($columns as $col) {
            $table = $col->TABLE_NAME;
            $colType = $col->COLUMN_TYPE;
            $extra = $col->EXTRA ?? '';

            $hasAutoIncrement = strpos($extra, 'auto_increment') !== false;

            $pkCheck = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
            $hasPrimaryKey = !empty($pkCheck);
            $pkColumn = $hasPrimaryKey ? ($pkCheck[0]->Column_name ?? '') : null;

            if ($hasAutoIncrement && $hasPrimaryKey && $pkColumn === 'id') {
                continue;
            }

            try {
                if (!$hasPrimaryKey) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$colType} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                } elseif ($pkColumn === 'id' && !$hasAutoIncrement) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$colType} NOT NULL AUTO_INCREMENT");
                }
            } catch (\Throwable $e) {
                Log::warning("Migration fix for table `{$table}` failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
