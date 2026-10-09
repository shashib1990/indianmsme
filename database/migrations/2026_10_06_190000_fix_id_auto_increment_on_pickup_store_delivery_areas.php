<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('pickup_store_delivery_areas') && Schema::hasColumn('pickup_store_delivery_areas', 'id')) {
            $col = DB::select("SELECT COLUMN_KEY, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pickup_store_delivery_areas' AND COLUMN_NAME = 'id'");
            if (!empty($col)) {
                $hasPk = ($col[0]->COLUMN_KEY ?? '') === 'PRI';
                $hasAutoInc = strpos($col[0]->EXTRA ?? '', 'auto_increment') !== false;

                if (!$hasAutoInc) {
                    try {
                        if (!$hasPk) {
                            DB::statement("ALTER TABLE pickup_store_delivery_areas ADD PRIMARY KEY (id), MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                        } else {
                            DB::statement("ALTER TABLE pickup_store_delivery_areas MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
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
        DB::statement("ALTER TABLE pickup_store_delivery_areas MODIFY COLUMN id BIGINT UNSIGNED NOT NULL");
    }
};
