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
        if (Schema::hasTable('ddc_pos_orders')) {
            if (Schema::hasColumn('ddc_pos_orders', 'payment_mode')) {
                DB::statement("ALTER TABLE `ddc_pos_orders` MODIFY COLUMN `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'CASH'");
            }
            if (Schema::hasColumn('ddc_pos_orders', 'payment_status')) {
                DB::statement("ALTER TABLE `ddc_pos_orders` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'PAID'");
            }
        }

        if (Schema::hasTable('adc_pos_orders')) {
            if (Schema::hasColumn('adc_pos_orders', 'payment_mode')) {
                DB::statement("ALTER TABLE `adc_pos_orders` MODIFY COLUMN `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'CASH'");
            }
            if (Schema::hasColumn('adc_pos_orders', 'payment_status')) {
                DB::statement("ALTER TABLE `adc_pos_orders` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'PAID'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_pos_orders')) {
            if (Schema::hasColumn('ddc_pos_orders', 'payment_mode')) {
                DB::statement("ALTER TABLE `ddc_pos_orders` MODIFY COLUMN `payment_mode` ENUM('cash', 'card', 'upi', 'credit', 'split') NOT NULL DEFAULT 'cash'");
            }
            if (Schema::hasColumn('ddc_pos_orders', 'payment_status')) {
                DB::statement("ALTER TABLE `ddc_pos_orders` MODIFY COLUMN `payment_status` ENUM('paid', 'unpaid', 'partial', 'credit') NOT NULL DEFAULT 'unpaid'");
            }
        }

        if (Schema::hasTable('adc_pos_orders')) {
            if (Schema::hasColumn('adc_pos_orders', 'payment_mode')) {
                DB::statement("ALTER TABLE `adc_pos_orders` MODIFY COLUMN `payment_mode` ENUM('cash', 'card', 'upi', 'credit', 'split') NOT NULL DEFAULT 'cash'");
            }
            if (Schema::hasColumn('adc_pos_orders', 'payment_status')) {
                DB::statement("ALTER TABLE `adc_pos_orders` MODIFY COLUMN `payment_status` ENUM('paid', 'unpaid', 'partial', 'credit') NOT NULL DEFAULT 'unpaid'");
            }
        }
    }
};
