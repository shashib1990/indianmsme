<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('adc_cart_items')) {
            Schema::create('adc_cart_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('adc_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->integer('quantity')->default(1);
                $table->timestamps();

                $table->unique(['adc_id', 'product_id'], 'adc_cart_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adc_cart_items');
    }
};
