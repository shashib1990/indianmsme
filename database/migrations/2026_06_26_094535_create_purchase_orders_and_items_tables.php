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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('ddc_id');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('accepted_amount', 12, 2)->default(0.00);
            $table->string('status')->default('Pending'); // Pending, Partially Accepted, Accepted, Rejected, Paid
            $table->string('payment_status')->default('Unpaid'); // Unpaid, Paid
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('status')->default('Pending'); // Pending, Accepted, Rejected
            $table->string('remarks')->nullable(); // Rejection reason
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
