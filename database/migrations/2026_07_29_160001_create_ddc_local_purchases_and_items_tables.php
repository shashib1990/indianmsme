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
        Schema::create('ddc_local_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ddc_id')->index();
            $table->string('purchase_no')->unique();
            $table->date('purchase_date');
            $table->string('supplier_name');
            $table->string('supplier_mobile')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('freight_charges', 12, 2)->default(0.00);
            $table->decimal('loading_unloading_charges', 12, 2)->default(0.00);
            $table->decimal('other_charges', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('ddc_local_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ddc_local_purchase_id')->index();
            $table->unsignedBigInteger('ddc_local_product_id')->index();
            $table->integer('quantity');
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('freight_share', 12, 2)->default(0.00);
            $table->decimal('loading_share', 12, 2)->default(0.00);
            $table->decimal('other_share', 12, 2)->default(0.00);
            $table->decimal('landing_cost', 12, 2);
            $table->decimal('mrp', 12, 2);
            $table->decimal('csp', 12, 2);
            $table->decimal('msp', 12, 2);
            $table->decimal('gst_rate', 5, 2)->default(0.00);
            $table->string('batch_number')->nullable();
            $table->date('mfg_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->unsignedBigInteger('ddc_inventory_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('ddc_local_purchase_id')->references('id')->on('ddc_local_purchases')->onDelete('cascade');
            $table->foreign('ddc_local_product_id')->references('id')->on('ddc_local_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddc_local_purchase_items');
        Schema::dropIfExists('ddc_local_purchases');
    }
};
