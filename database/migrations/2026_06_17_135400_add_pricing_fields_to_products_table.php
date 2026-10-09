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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('seller_price_incl_gst', 12, 2)->nullable()->after('mrp');
            $table->decimal('base_cost', 12, 2)->nullable()->after('seller_price_incl_gst');
            $table->decimal('company_margin_percentage', 5, 2)->nullable()->after('base_cost');
            $table->decimal('company_margin_amount', 12, 2)->nullable()->after('company_margin_percentage');
            $table->decimal('ddc_margin_percentage', 5, 2)->nullable()->after('company_margin_amount');
            $table->decimal('ddc_margin_amount', 12, 2)->nullable()->after('ddc_margin_percentage');
            $table->decimal('company_gst_amount', 12, 2)->nullable()->after('ddc_margin_amount');
            $table->decimal('seller_gst_amount', 12, 2)->nullable()->after('company_gst_amount');
            $table->decimal('adc_purchase_price', 12, 2)->nullable()->after('seller_gst_amount');
            $table->decimal('available_margin', 12, 2)->nullable()->after('adc_purchase_price');
            $table->decimal('mbp_percentage', 5, 2)->nullable()->after('available_margin');
            $table->decimal('mbp_amount', 12, 2)->nullable()->after('mbp_percentage');
            $table->decimal('adc_final_margin', 12, 2)->nullable()->after('mbp_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'seller_price_incl_gst',
                'base_cost',
                'company_margin_percentage',
                'company_margin_amount',
                'ddc_margin_percentage',
                'ddc_margin_amount',
                'company_gst_amount',
                'seller_gst_amount',
                'adc_purchase_price',
                'available_margin',
                'mbp_percentage',
                'mbp_amount',
                'adc_final_margin',
            ]);
        });
    }
};
