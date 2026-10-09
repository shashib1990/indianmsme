<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('impersonation_logs') && Schema::hasColumn('impersonation_logs', 'id')) {
            $col = DB::select("SELECT COLUMN_KEY, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'impersonation_logs' AND COLUMN_NAME = 'id'");
            if (!empty($col)) {
                $hasPk = ($col[0]->COLUMN_KEY ?? '') === 'PRI';
                $hasAutoInc = strpos($col[0]->EXTRA ?? '', 'auto_increment') !== false;

                if (!$hasAutoInc) {
                    try {
                        if (!$hasPk) {
                            DB::statement("ALTER TABLE impersonation_logs ADD PRIMARY KEY (id), MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                        } else {
                            DB::statement("ALTER TABLE impersonation_logs MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                        }
                    } catch (\Throwable $e) {
                        // Ignore if already set
                    }
                }
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
