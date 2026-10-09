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
        if (Schema::hasTable('ddc_pos_orders') && !Schema::hasColumn('ddc_pos_orders', 'adc_order_id')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('adc_order_id')->nullable()->after('adc_user_id')->index();
                $table->foreign('adc_order_id')->references('id')->on('adc_orders')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_pos_orders') && Schema::hasColumn('ddc_pos_orders', 'adc_order_id')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                $table->dropForeign(['adc_order_id']);
                $table->dropColumn('adc_order_id');
            });
        }
    }
};
