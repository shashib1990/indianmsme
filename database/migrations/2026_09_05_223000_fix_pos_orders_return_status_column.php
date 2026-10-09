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
        if (Schema::hasTable('ddc_pos_orders') && Schema::hasColumn('ddc_pos_orders', 'return_status')) {
            DB::statement("ALTER TABLE `ddc_pos_orders` MODIFY COLUMN `return_status` VARCHAR(50) NOT NULL DEFAULT 'None'");
        }

        if (Schema::hasTable('adc_pos_orders') && Schema::hasColumn('adc_pos_orders', 'return_status')) {
            DB::statement("ALTER TABLE `adc_pos_orders` MODIFY COLUMN `return_status` VARCHAR(50) NOT NULL DEFAULT 'None'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_pos_orders') && Schema::hasColumn('ddc_pos_orders', 'return_status')) {
            DB::statement("ALTER TABLE `ddc_pos_orders` MODIFY COLUMN `return_status` VARCHAR(50) NOT NULL DEFAULT 'None'");
        }

        if (Schema::hasTable('adc_pos_orders') && Schema::hasColumn('adc_pos_orders', 'return_status')) {
            DB::statement("ALTER TABLE `adc_pos_orders` MODIFY COLUMN `return_status` VARCHAR(50) NOT NULL DEFAULT 'None'");
        }
    }
};
