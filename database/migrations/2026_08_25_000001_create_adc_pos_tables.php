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
        // 1. ADC POS Customers Table
        Schema::create('adc_pos_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adc_id');
            $table->string('name');
            $table->string('mobile', 15)->index();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->timestamps();

            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. ADC POS Orders Table
        Schema::create('adc_pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 50)->unique();
            $table->unsignedBigInteger('adc_id');
            $table->enum('mode', ['retail'])->default('retail');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_mobile', 15)->nullable();
            $table->integer('total_items')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('payment_mode', 50)->default('CASH'); // CASH, UPI, CARD, WALLET
            $table->string('payment_status', 20)->default('PAID');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('adc_pos_customers')->onDelete('set null');
        });

        // 3. ADC POS Order Items Table
        Schema::create('adc_pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_order_id');
            $table->unsignedBigInteger('adc_inventory_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('mrp', 10, 2)->default(0.00);
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->string('batch_number')->nullable();
            $table->date('mfg_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->timestamps();

            $table->foreign('pos_order_id')->references('id')->on('adc_pos_orders')->onDelete('cascade');
            $table->foreign('adc_inventory_id')->references('id')->on('adc_inventories')->onDelete('set null');
        });

        // 4. ADC POS Carts Table (Active & Held carts)
        Schema::create('adc_pos_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adc_id');
            $table->enum('mode', ['retail'])->default('retail');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_mobile', 15)->nullable();
            $table->enum('status', ['active', 'held'])->default('active');
            $table->longText('cart_data')->nullable(); // JSON stored [{id, qty}, ...]
            $table->timestamps();

            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adc_pos_carts');
        Schema::dropIfExists('adc_pos_order_items');
        Schema::dropIfExists('adc_pos_orders');
        Schema::dropIfExists('adc_pos_customers');
    }
};
