<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add frozen transaction and routing fields to adc_orders
        Schema::table('adc_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_orders', 'fulfillment_type')) {
                $table->enum('fulfillment_type', ['physical', 'virtual'])->default('physical')->after('payment_method');
            }
            if (!Schema::hasColumn('adc_orders', 'selected_ddc_id')) {
                $table->foreignId('selected_ddc_id')->nullable()->constrained('users')->onDelete('set null')->after('fulfillment_type');
            }
            if (!Schema::hasColumn('adc_orders', 'virtual_ddc_id')) {
                $table->foreignId('virtual_ddc_id')->nullable()->constrained('users')->onDelete('set null')->after('selected_ddc_id');
            }
            if (!Schema::hasColumn('adc_orders', 'assigned_ddc_id')) {
                $table->foreignId('assigned_ddc_id')->nullable()->constrained('users')->onDelete('set null')->after('virtual_ddc_id');
            }
            if (!Schema::hasColumn('adc_orders', 'routing_pincode')) {
                $table->string('routing_pincode', 20)->nullable()->after('assigned_ddc_id');
            }
            if (!Schema::hasColumn('adc_orders', 'delivery_pincode')) {
                $table->string('delivery_pincode', 20)->nullable()->after('routing_pincode');
            }
            if (!Schema::hasColumn('adc_orders', 'delivery_state')) {
                $table->string('delivery_state', 100)->nullable()->after('delivery_pincode');
            }
            if (!Schema::hasColumn('adc_orders', 'delivery_district')) {
                $table->string('delivery_district', 100)->nullable()->after('delivery_state');
            }
            if (!Schema::hasColumn('adc_orders', 'routing_logic')) {
                $table->string('routing_logic', 255)->nullable()->after('delivery_district');
            }
            if (!Schema::hasColumn('adc_orders', 'subtotal_excl_gst')) {
                $table->decimal('subtotal_excl_gst', 12, 2)->default(0.00)->after('total_amount');
            }
            if (!Schema::hasColumn('adc_orders', 'total_gst')) {
                $table->decimal('total_gst', 12, 2)->default(0.00)->after('subtotal_excl_gst');
            }
            if (!Schema::hasColumn('adc_orders', 'total_mbp_coins')) {
                $table->decimal('total_mbp_coins', 12, 2)->default(0.00)->after('total_gst');
            }
        });

        // 2. Add frozen transaction and routing fields to adc_order_items
        Schema::table('adc_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('adc_order_items', 'product_code')) {
                $table->string('product_code')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('adc_order_items', 'hsn_code')) {
                $table->string('hsn_code')->nullable()->after('product_code');
            }
            if (!Schema::hasColumn('adc_order_items', 'category_name')) {
                $table->string('category_name')->nullable()->after('hsn_code');
            }
            if (!Schema::hasColumn('adc_order_items', 'brand_name')) {
                $table->string('brand_name')->nullable()->after('category_name');
            }
            if (!Schema::hasColumn('adc_order_items', 'unit_of_measure')) {
                $table->string('unit_of_measure')->nullable()->after('brand_name');
            }
            if (!Schema::hasColumn('adc_order_items', 'mrp')) {
                $table->decimal('mrp', 12, 2)->default(0.00)->after('unit_of_measure');
            }
            if (!Schema::hasColumn('adc_order_items', 'seller_price')) {
                $table->decimal('seller_price', 12, 2)->default(0.00)->after('mrp');
            }
            if (!Schema::hasColumn('adc_order_items', 'msp')) {
                $table->decimal('msp', 12, 2)->default(0.00)->after('price');
            }
            if (!Schema::hasColumn('adc_order_items', 'csp')) {
                $table->decimal('csp', 12, 2)->default(0.00)->after('msp');
            }
            if (!Schema::hasColumn('adc_order_items', 'taxable_amount')) {
                $table->decimal('taxable_amount', 12, 2)->default(0.00)->after('csp');
            }
            if (!Schema::hasColumn('adc_order_items', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->default(0.00)->after('taxable_amount');
            }
            if (!Schema::hasColumn('adc_order_items', 'gst_amount')) {
                $table->decimal('gst_amount', 12, 2)->default(0.00)->after('gst_rate');
            }
            if (!Schema::hasColumn('adc_order_items', 'ddc_margin')) {
                $table->decimal('ddc_margin', 12, 2)->default(0.00)->after('total');
            }
            if (!Schema::hasColumn('adc_order_items', 'adc_margin')) {
                $table->decimal('adc_margin', 12, 2)->default(0.00)->after('ddc_margin');
            }
            if (!Schema::hasColumn('adc_order_items', 'csp_margin')) {
                $table->decimal('csp_margin', 12, 2)->default(0.00)->after('adc_margin');
            }
            if (!Schema::hasColumn('adc_order_items', 'mbp_percentage')) {
                $table->decimal('mbp_percentage', 5, 2)->default(0.00)->after('csp_margin');
            }
            if (!Schema::hasColumn('adc_order_items', 'mbp_amount')) {
                $table->decimal('mbp_amount', 12, 2)->default(0.00)->after('mbp_percentage');
            }
            if (!Schema::hasColumn('adc_order_items', 'mbp_90_share')) {
                $table->decimal('mbp_90_share', 12, 2)->default(0.00)->after('mbp_amount');
            }
            if (!Schema::hasColumn('adc_order_items', 'mbp_10_share')) {
                $table->decimal('mbp_10_share', 12, 2)->default(0.00)->after('mbp_90_share');
            }
            if (!Schema::hasColumn('adc_order_items', 'mbp_38_share')) {
                $table->decimal('mbp_38_share', 12, 2)->default(0.00)->after('mbp_10_share');
            }
            if (!Schema::hasColumn('adc_order_items', 'mbp_62_share')) {
                $table->decimal('mbp_62_share', 12, 2)->default(0.00)->after('mbp_38_share');
            }
            if (!Schema::hasColumn('adc_order_items', 'fulfillment_type')) {
                $table->enum('fulfillment_type', ['physical', 'virtual'])->default('physical')->after('mbp_62_share');
            }
            if (!Schema::hasColumn('adc_order_items', 'ddc_id')) {
                $table->foreignId('ddc_id')->nullable()->constrained('users')->onDelete('set null')->after('fulfillment_type');
            }
            if (!Schema::hasColumn('adc_order_items', 'virtual_ddc_id')) {
                $table->foreignId('virtual_ddc_id')->nullable()->constrained('users')->onDelete('set null')->after('ddc_id');
            }
            if (!Schema::hasColumn('adc_order_items', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->onDelete('set null')->after('virtual_ddc_id');
            }
        });

        // 3. Add fulfillment_type and adc_order_id to purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'fulfillment_type')) {
                $table->enum('fulfillment_type', ['physical', 'virtual'])->default('physical')->after('ssi_percentage');
            }
            if (!Schema::hasColumn('purchase_orders', 'adc_order_id')) {
                $table->foreignId('adc_order_id')->nullable()->constrained('adc_orders')->onDelete('set null')->after('fulfillment_type');
            }
        });

        // 4. Add supplier_gstin, adc_order_id tracking to invoices and make purchase_order_id nullable
        DB::statement('ALTER TABLE invoices MODIFY COLUMN purchase_order_id BIGINT UNSIGNED NULL');

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'adc_order_id')) {
                $table->unsignedBigInteger('adc_order_id')->nullable()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('invoices', 'supplier_gstin')) {
                $table->string('supplier_gstin', 50)->nullable()->after('type');
            }
        });

        // 5. Add financial_year & serial_number to ddc_pos_orders for independent Retail/Wholesale POS invoice series
        Schema::table('ddc_pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('ddc_pos_orders', 'financial_year')) {
                $table->string('financial_year', 10)->nullable()->after('mode');
            }
            if (!Schema::hasColumn('ddc_pos_orders', 'serial_number')) {
                $table->unsignedInteger('serial_number')->default(0)->after('financial_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'supplier_gstin')) {
                $table->dropColumn('supplier_gstin');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'adc_order_id')) {
                $table->dropForeign(['adc_order_id']);
                $table->dropColumn('adc_order_id');
            }
            if (Schema::hasColumn('purchase_orders', 'fulfillment_type')) {
                $table->dropColumn('fulfillment_type');
            }
        });

        Schema::table('adc_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'product_name', 'product_code', 'hsn_code', 'category_name', 'brand_name', 'unit_of_measure',
                'mrp', 'seller_price', 'msp', 'csp', 'taxable_amount', 'gst_rate', 'gst_amount',
                'ddc_margin', 'adc_margin', 'csp_margin', 'mbp_percentage', 'mbp_amount',
                'mbp_90_share', 'mbp_10_share', 'mbp_38_share', 'mbp_62_share', 'fulfillment_type'
            ]);
            if (Schema::hasColumn('adc_order_items', 'ddc_id')) {
                $table->dropForeign(['ddc_id']);
                $table->dropColumn('ddc_id');
            }
            if (Schema::hasColumn('adc_order_items', 'virtual_ddc_id')) {
                $table->dropForeign(['virtual_ddc_id']);
                $table->dropColumn('virtual_ddc_id');
            }
            if (Schema::hasColumn('adc_order_items', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
        });

        Schema::table('adc_orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_type', 'routing_pincode', 'delivery_pincode', 'delivery_state', 'delivery_district',
                'routing_logic', 'subtotal_excl_gst', 'total_gst', 'total_mbp_coins'
            ]);
            if (Schema::hasColumn('adc_orders', 'selected_ddc_id')) {
                $table->dropForeign(['selected_ddc_id']);
                $table->dropColumn('selected_ddc_id');
            }
            if (Schema::hasColumn('adc_orders', 'virtual_ddc_id')) {
                $table->dropForeign(['virtual_ddc_id']);
                $table->dropColumn('virtual_ddc_id');
            }
            if (Schema::hasColumn('adc_orders', 'assigned_ddc_id')) {
                $table->dropForeign(['assigned_ddc_id']);
                $table->dropColumn('assigned_ddc_id');
            }
        });
    }
};
