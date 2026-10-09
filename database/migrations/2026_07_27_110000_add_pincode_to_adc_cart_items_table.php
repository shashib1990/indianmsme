<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adc_cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_cart_items', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('product_id');
            }
        });

        // Drop previous unique index if exists and add new unique index including pincode
        try {
            Schema::table('adc_cart_items', function (Blueprint $table) {
                $table->dropUnique('adc_cart_unique');
            });
        } catch (\Exception $e) {
            // Unique key might not exist or have different name
        }

        try {
            Schema::table('adc_cart_items', function (Blueprint $table) {
                $table->unique(['adc_id', 'product_id', 'pincode'], 'adc_cart_pincode_unique');
            });
        } catch (\Exception $e) {
            // Index might already exist
        }
    }

    public function down(): void
    {
        Schema::table('adc_cart_items', function (Blueprint $table) {
            try {
                $table->dropUnique('adc_cart_pincode_unique');
            } catch (\Exception $e) {}

            if (Schema::hasColumn('adc_cart_items', 'pincode')) {
                $table->dropColumn('pincode');
            }

            try {
                $table->unique(['adc_id', 'product_id'], 'adc_cart_unique');
            } catch (\Exception $e) {}
        });
    }
};
