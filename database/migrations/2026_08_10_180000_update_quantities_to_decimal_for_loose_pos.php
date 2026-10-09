<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ddc_inventories')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->default(0)->change();
                if (Schema::hasColumn('ddc_inventories', 'sold_quantity')) {
                    $table->decimal('sold_quantity', 12, 3)->default(0)->change();
                }
                if (Schema::hasColumn('ddc_inventories', 'returned_quantity')) {
                    $table->decimal('returned_quantity', 12, 3)->default(0)->change();
                }
                if (Schema::hasColumn('ddc_inventories', 'reserved_quantity')) {
                    $table->decimal('reserved_quantity', 12, 3)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('ddc_pos_order_items')) {
            Schema::table('ddc_pos_order_items', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->change();
            });
        }

        if (Schema::hasTable('ddc_pos_orders')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                $table->decimal('total_items', 12, 3)->default(0)->change();
            });
        }

        if (Schema::hasTable('adc_inventories')) {
            Schema::table('adc_inventories', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->default(0)->change();
                if (Schema::hasColumn('adc_inventories', 'sold_quantity')) {
                    $table->decimal('sold_quantity', 12, 3)->default(0)->change();
                }
                if (Schema::hasColumn('adc_inventories', 'returned_quantity')) {
                    $table->decimal('returned_quantity', 12, 3)->default(0)->change();
                }
                if (Schema::hasColumn('adc_inventories', 'reserved_quantity')) {
                    $table->decimal('reserved_quantity', 12, 3)->default(0)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert schema if needed
    }
};
