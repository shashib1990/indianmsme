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
        Schema::table('adc_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_inventories', 'inventory_source')) {
                $table->enum('inventory_source', ['MSME', 'LOCAL'])->default('MSME')->after('adc_id');
            }
            if (!Schema::hasColumn('adc_inventories', 'adc_local_product_id')) {
                $table->unsignedBigInteger('adc_local_product_id')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('adc_inventories', 'landing_cost')) {
                $table->decimal('landing_cost', 12, 2)->default(0.00)->after('purchase_price');
            }
            if (!Schema::hasColumn('adc_inventories', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('adc_order_item_id');
            }
            if (!Schema::hasColumn('adc_inventories', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }
            if (!Schema::hasColumn('adc_inventories', 'pos_status')) {
                $table->enum('pos_status', ['Active', 'Inactive'])->default('Active')->after('reference_id');
            }
        });

        Schema::table('adc_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adc_inventories', function (Blueprint $table) {
            if (Schema::hasColumn('adc_inventories', 'inventory_source')) {
                $table->dropColumn('inventory_source');
            }
            if (Schema::hasColumn('adc_inventories', 'adc_local_product_id')) {
                $table->dropColumn('adc_local_product_id');
            }
            if (Schema::hasColumn('adc_inventories', 'landing_cost')) {
                $table->dropColumn('landing_cost');
            }
            if (Schema::hasColumn('adc_inventories', 'reference_type')) {
                $table->dropColumn('reference_type');
            }
            if (Schema::hasColumn('adc_inventories', 'reference_id')) {
                $table->dropColumn('reference_id');
            }
            if (Schema::hasColumn('adc_inventories', 'pos_status')) {
                $table->dropColumn('pos_status');
            }
        });
    }
};
