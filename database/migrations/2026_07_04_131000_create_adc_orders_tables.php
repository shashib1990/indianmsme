<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('adc_orders')) {
            Schema::create('adc_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->foreignId('adc_id')->constrained('users')->onDelete('cascade');
                $table->decimal('total_amount', 12, 2);
                $table->string('status')->default('Pending');        // Pending, Confirmed, Dispatched, Delivered, Cancelled
                $table->string('payment_status')->default('Paid');   // Paid (deducted from wallet immediately)
                $table->string('payment_method')->default('Wallet');
                $table->text('shipping_address')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('adc_order_items')) {
            Schema::create('adc_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('adc_order_id')->constrained('adc_orders')->onDelete('cascade');
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('price', 12, 2);   // snapshot of adc_purchase_price
                $table->decimal('total', 12, 2);
                $table->string('status')->default('Pending'); // Pending, Accepted, Rejected
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adc_order_items');
        Schema::dropIfExists('adc_orders');
    }
};
