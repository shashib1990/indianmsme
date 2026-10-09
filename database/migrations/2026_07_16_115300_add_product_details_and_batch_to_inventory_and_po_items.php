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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('product_code')->nullable()->after('product_name');
            $table->string('barcode')->nullable()->after('product_code');
            $table->string('batch_number')->nullable()->after('expire_date');
        });

        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('product_code')->nullable()->after('product_name');
            $table->string('barcode')->nullable()->after('product_code');
            $table->decimal('purchase_price', 12, 2)->nullable()->after('mrp');
            $table->string('batch_number')->nullable()->after('expire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'product_code', 'barcode', 'batch_number']);
        });

        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'product_code', 'barcode', 'purchase_price', 'batch_number']);
        });
    }
};
