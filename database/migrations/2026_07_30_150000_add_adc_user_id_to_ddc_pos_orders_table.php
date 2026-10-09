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
        if (Schema::hasTable('ddc_pos_orders') && !Schema::hasColumn('ddc_pos_orders', 'adc_user_id')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('adc_user_id')->nullable()->after('customer_id')->index();
                $table->foreign('adc_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_pos_orders') && Schema::hasColumn('ddc_pos_orders', 'adc_user_id')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                $table->dropForeign(['adc_user_id']);
                $table->dropColumn('adc_user_id');
            });
        }
    }
};
