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
        Schema::table('sub_seller_products', function (Blueprint $table) {
            $table->decimal('base_cost', 12, 2)->nullable()->after('final_price');
            $table->decimal('company_margin_percentage', 5, 2)->nullable()->after('base_cost');
            $table->decimal('company_margin_amount', 12, 2)->nullable()->after('company_margin_percentage');
            $table->decimal('admin_margin_percentage', 5, 2)->nullable()->after('company_margin_amount');
            $table->decimal('admin_margin_amount', 12, 2)->nullable()->after('admin_margin_percentage');
            $table->decimal('ddc_margin_percentage', 5, 2)->nullable()->after('admin_margin_amount');
            $table->decimal('ddc_margin_amount', 12, 2)->nullable()->after('ddc_margin_percentage');
            $table->decimal('price_before_gst', 12, 2)->nullable()->after('ddc_margin_amount');
            $table->decimal('company_gst_amount', 12, 2)->nullable()->after('price_before_gst');
            $table->decimal('seller_gst_amount', 12, 2)->nullable()->after('company_gst_amount');
            $table->decimal('adc_purchase_price', 12, 2)->nullable()->after('seller_gst_amount');
            $table->decimal('available_margin', 12, 2)->nullable()->after('adc_purchase_price');
            $table->decimal('mbp_percentage', 5, 2)->nullable()->after('available_margin');
            $table->decimal('mbp_amount', 12, 2)->nullable()->after('mbp_percentage');
            $table->decimal('mbp_90_share', 12, 2)->nullable()->after('mbp_amount');
            $table->decimal('mbp_10_share', 12, 2)->nullable()->after('mbp_90_share');
            $table->decimal('mbp_38_share', 12, 2)->nullable()->after('mbp_10_share');
            $table->decimal('mbp_62_share', 12, 2)->nullable()->after('mbp_38_share');
            $table->decimal('adc_final_margin', 12, 2)->nullable()->after('mbp_62_share');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_seller_products', function (Blueprint $table) {
            $table->dropColumn([
                'base_cost',
                'company_margin_percentage',
                'company_margin_amount',
                'admin_margin_percentage',
                'admin_margin_amount',
                'ddc_margin_percentage',
                'ddc_margin_amount',
                'price_before_gst',
                'company_gst_amount',
                'seller_gst_amount',
                'adc_purchase_price',
                'available_margin',
                'mbp_percentage',
                'mbp_amount',
                'mbp_90_share',
                'mbp_10_share',
                'mbp_38_share',
                'mbp_62_share',
                'adc_final_margin',
            ]);
        });
    }
};
