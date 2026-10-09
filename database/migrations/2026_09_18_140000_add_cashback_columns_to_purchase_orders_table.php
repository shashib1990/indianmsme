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
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'is_cashback_credited')) {
                $table->boolean('is_cashback_credited')->default(false)->after('status');
            }
            if (!Schema::hasColumn('purchase_orders', 'cashback_percentage')) {
                $table->decimal('cashback_percentage', 5, 2)->nullable()->after('is_cashback_credited');
            }
            if (!Schema::hasColumn('purchase_orders', 'cashback_amount')) {
                $table->decimal('cashback_amount', 12, 2)->nullable()->after('cashback_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'is_cashback_credited')) {
                $table->dropColumn('is_cashback_credited');
            }
            if (Schema::hasColumn('purchase_orders', 'cashback_percentage')) {
                $table->dropColumn('cashback_percentage');
            }
            if (Schema::hasColumn('purchase_orders', 'cashback_amount')) {
                $table->dropColumn('cashback_amount');
            }
        });
    }
};
