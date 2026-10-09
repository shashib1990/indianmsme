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
        if (Schema::hasTable('purchase_order_items') && !Schema::hasColumn('purchase_order_items', 'unit_of_measure')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->string('unit_of_measure')->nullable()->after('barcode');
            });
        }

        if (Schema::hasTable('ddc_inventories') && !Schema::hasColumn('ddc_inventories', 'unit_of_measure')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->string('unit_of_measure')->nullable()->after('barcode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_order_items') && Schema::hasColumn('purchase_order_items', 'unit_of_measure')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->dropColumn('unit_of_measure');
            });
        }

        if (Schema::hasTable('ddc_inventories') && Schema::hasColumn('ddc_inventories', 'unit_of_measure')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->dropColumn('unit_of_measure');
            });
        }
    }
};
