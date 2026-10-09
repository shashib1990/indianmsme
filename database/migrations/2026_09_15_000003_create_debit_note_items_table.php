<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_item_id')->nullable()->change();
        });

        Schema::create('debit_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debit_note_id');
            $table->unsignedBigInteger('purchase_order_item_id');
            $table->string('product_name');
            $table->integer('order_qty');
            $table->integer('received_qty');
            $table->integer('shortage_qty')->default(0);
            $table->integer('damage_qty')->default(0);
            $table->integer('excess_qty')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('item_discrepancy_amount', 12, 2)->default(0.00);
            $table->integer('seller_agreed_qty')->nullable();
            $table->decimal('seller_agreed_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('debit_note_id')->references('id')->on('debit_notes')->onDelete('cascade');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_note_items');
    }
};
