<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add ddc_purchase_price to products table (after adc_purchase_price)
        if (!Schema::hasColumn('products', 'ddc_purchase_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('ddc_purchase_price', 10, 2)->nullable()->after('adc_purchase_price');
            });
        }

        // Add ddc_purchase_price to sub_seller_products table
        if (!Schema::hasColumn('sub_seller_products', 'ddc_purchase_price')) {
            Schema::table('sub_seller_products', function (Blueprint $table) {
                $table->decimal('ddc_purchase_price', 10, 2)->nullable()->after('adc_purchase_price');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('ddc_purchase_price');
        });
        Schema::table('sub_seller_products', function (Blueprint $table) {
            $table->dropColumn('ddc_purchase_price');
        });
    }
};
