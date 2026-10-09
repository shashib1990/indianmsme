<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::select("SELECT TABLE_NAME, COLUMN_KEY, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id'");

        foreach ($columns as $col) {
            $table = $col->TABLE_NAME;
            $hasPk = ($col->COLUMN_KEY ?? '') === 'PRI';
            $hasAutoInc = strpos($col->EXTRA ?? '', 'auto_increment') !== false;

            if (!$hasAutoInc) {
                try {
                    if (!$hasPk) {
                        DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`), MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                    } else {
                        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                    }
                } catch (\Throwable $e) {
                    // Ignore if already altered or unable to alter
                }
            }
        }
    }

    public function down(): void
    {
        // No-op
    }
};
