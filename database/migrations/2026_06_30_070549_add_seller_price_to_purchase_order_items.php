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
            $table->decimal('seller_price', 12, 2)->after('price')->default(0.00);
            $table->decimal('seller_total', 12, 2)->after('total')->default(0.00);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('seller_total_amount', 12, 2)->after('total_amount')->default(0.00);
            $table->decimal('seller_accepted_amount', 12, 2)->after('accepted_amount')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['seller_price', 'seller_total']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['seller_total_amount', 'seller_accepted_amount']);
        });
    }
};
