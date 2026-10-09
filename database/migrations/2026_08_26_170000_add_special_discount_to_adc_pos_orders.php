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
        if (Schema::hasTable('adc_pos_orders')) {
            Schema::table('adc_pos_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('adc_pos_orders', 'special_discount_amount')) {
                    $table->decimal('special_discount_amount', 12, 2)->default(0.00)->after('discount_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_pos_orders')) {
            Schema::table('adc_pos_orders', function (Blueprint $table) {
                if (Schema::hasColumn('adc_pos_orders', 'special_discount_amount')) {
                    $table->dropColumn('special_discount_amount');
                }
            });
        }
    }
};
