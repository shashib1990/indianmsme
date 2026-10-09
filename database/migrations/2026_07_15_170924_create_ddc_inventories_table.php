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
        Schema::create('ddc_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ddc_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(0);
            $table->decimal('mrp', 12, 2);
            $table->date('mfg_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->timestamps();

            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddc_inventories');
    }
};
