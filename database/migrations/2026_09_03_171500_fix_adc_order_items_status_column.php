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
        if (Schema::hasTable('adc_order_items')) {
            DB::statement("ALTER TABLE adc_order_items MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('adc_orders')) {
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('purchase_order_items')) {
            DB::statement("ALTER TABLE purchase_order_items MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('purchase_orders')) {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_order_items')) {
            DB::statement("ALTER TABLE adc_order_items MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('adc_orders')) {
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('purchase_order_items')) {
            DB::statement("ALTER TABLE purchase_order_items MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('purchase_orders')) {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }
    }
};
