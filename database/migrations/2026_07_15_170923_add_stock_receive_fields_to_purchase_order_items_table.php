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
            $table->decimal('actual_mrp', 12, 2)->nullable()->after('price');
            $table->decimal('received_mrp', 12, 2)->nullable()->after('actual_mrp');
            $table->integer('supply_qty')->nullable()->after('quantity');
            $table->integer('damage_qty')->default(0)->after('supply_qty');
            $table->integer('shortage_qty')->default(0)->after('damage_qty');
            $table->integer('excess_qty')->default(0)->after('shortage_qty');
            $table->date('mfg_date')->nullable()->after('remarks');
            $table->date('expire_date')->nullable()->after('mfg_date');
            $table->boolean('is_stocked_in')->default(false)->after('expire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'actual_mrp',
                'received_mrp',
                'supply_qty',
                'damage_qty',
                'shortage_qty',
                'excess_qty',
                'mfg_date',
                'expire_date',
                'is_stocked_in'
            ]);
        });
    }
};
