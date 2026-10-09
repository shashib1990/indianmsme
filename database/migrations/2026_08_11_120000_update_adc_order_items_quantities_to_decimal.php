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
        if (Schema::hasTable('adc_order_items')) {
            Schema::table('adc_order_items', function (Blueprint $table) {
                $table->decimal('quantity', 12, 3)->default(0)->change();
                if (Schema::hasColumn('adc_order_items', 'supply_qty')) {
                    $table->decimal('supply_qty', 12, 3)->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('adc_order_items', 'damage_qty')) {
                    $table->decimal('damage_qty', 12, 3)->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('adc_order_items', 'shortage_qty')) {
                    $table->decimal('shortage_qty', 12, 3)->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('adc_order_items', 'excess_qty')) {
                    $table->decimal('excess_qty', 12, 3)->nullable()->default(0)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
