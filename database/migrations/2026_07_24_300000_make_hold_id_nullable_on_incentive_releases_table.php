<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incentive_releases', function (Blueprint $table) {
            $table->foreignId('hold_id')->nullable()->change();
            $table->foreignId('adc_order_id')->nullable()->change();
            $table->foreignId('adc_order_item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('incentive_releases', function (Blueprint $table) {
            $table->foreignId('hold_id')->nullable(false)->change();
            $table->foreignId('adc_order_id')->nullable(false)->change();
            $table->foreignId('adc_order_item_id')->nullable(false)->change();
        });
    }
};
