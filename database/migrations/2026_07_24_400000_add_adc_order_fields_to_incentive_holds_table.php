<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incentive_holds', function (Blueprint $table) {
            if (!Schema::hasColumn('incentive_holds', 'adc_order_id')) {
                $table->foreignId('adc_order_id')->nullable()->constrained('adc_orders')->onDelete('cascade')->after('purchase_order_item_id');
            }
            if (!Schema::hasColumn('incentive_holds', 'adc_order_item_id')) {
                $table->foreignId('adc_order_item_id')->nullable()->constrained('adc_order_items')->onDelete('cascade')->after('adc_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('incentive_holds', function (Blueprint $table) {
            if (Schema::hasColumn('incentive_holds', 'adc_order_item_id')) {
                $table->dropForeign(['adc_order_item_id']);
                $table->dropColumn('adc_order_item_id');
            }
            if (Schema::hasColumn('incentive_holds', 'adc_order_id')) {
                $table->dropForeign(['adc_order_id']);
                $table->dropColumn('adc_order_id');
            }
        });
    }
};
