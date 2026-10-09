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
        if (Schema::hasTable('adc_pos_order_items')) {
            DB::statement("ALTER TABLE `adc_pos_order_items` MODIFY `quantity` DECIMAL(12, 3) NOT NULL DEFAULT 0.000");
        }

        if (Schema::hasTable('adc_pos_orders')) {
            DB::statement("ALTER TABLE `adc_pos_orders` MODIFY `total_items` DECIMAL(12, 3) NOT NULL DEFAULT 0.000");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_pos_order_items')) {
            DB::statement("ALTER TABLE `adc_pos_order_items` MODIFY `quantity` INT NOT NULL DEFAULT 0");
        }

        if (Schema::hasTable('adc_pos_orders')) {
            DB::statement("ALTER TABLE `adc_pos_orders` MODIFY `total_items` INT NOT NULL DEFAULT 0");
        }
    }
};
