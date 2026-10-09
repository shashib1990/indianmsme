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
                $table->unsignedBigInteger('seller_id')->nullable()->change();
                $table->unsignedBigInteger('product_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_order_items')) {
            Schema::table('adc_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('seller_id')->nullable(false)->change();
                $table->unsignedBigInteger('product_id')->nullable(false)->change();
            });
        }
    }
};
