<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds SSI (Smart Sales Incentive) tracking fields to purchase_orders.
     *
     * ssi_applicable : whether seller is DDC-subscribed at PO creation time
     * ssi_type       : 'SSI' or 'FSI' (Smart Sales Incentive / Field Sales Incentive)
     * ssi_percentage : the locked-in percentage at PO time (seller's saved %, or default 0.5)
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('ssi_applicable')->default(false)->after('ama_code')
                ->comment('True if seller had active DDC subscription at PO creation time');
            $table->enum('ssi_type', ['SSI', 'FSI'])->nullable()->after('ssi_applicable')
                ->comment('Type of incentive: SSI (Smart Sales Incentive) or FSI (Field Sales Incentive)');
            $table->decimal('ssi_percentage', 5, 2)->nullable()->after('ssi_type')
                ->comment('Locked-in incentive % at time of PO creation (seller setting or default 0.50)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['ssi_applicable', 'ssi_type', 'ssi_percentage']);
        });
    }
};
