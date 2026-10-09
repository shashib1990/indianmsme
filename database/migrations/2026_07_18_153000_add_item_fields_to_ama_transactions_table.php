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
        Schema::table('ama_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_item_id')->nullable()->after('purchase_order_id');
            $table->unsignedBigInteger('product_id')->nullable()->after('purchase_order_item_id');
            $table->integer('quantity')->default(0)->after('product_id');
            $table->decimal('released_amount', 10, 2)->default(0.00)->after('amount');
            $table->decimal('balance_amount', 10, 2)->default(0.00)->after('released_amount');

            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ama_transactions', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropForeign(['product_id']);

            $table->dropColumn([
                'purchase_order_item_id',
                'product_id',
                'quantity',
                'released_amount',
                'balance_amount'
            ]);
        });
    }
};
