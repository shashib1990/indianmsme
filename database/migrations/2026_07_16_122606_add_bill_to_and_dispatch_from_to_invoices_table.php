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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('type')->nullable()->after('purchase_order_id');
            
            // Bill To
            $table->string('bill_to_name')->nullable();
            $table->text('bill_to_address')->nullable();
            $table->string('bill_to_district')->nullable();
            $table->string('bill_to_state')->nullable();
            $table->string('bill_to_pincode')->nullable();
            $table->string('bill_to_phone')->nullable();
            $table->string('bill_to_gstin')->nullable();

            // Dispatch From
            $table->string('dispatch_from_name')->nullable();
            $table->text('dispatch_from_address')->nullable();
            $table->string('dispatch_from_district')->nullable();
            $table->string('dispatch_from_state')->nullable();
            $table->string('dispatch_from_pincode')->nullable();
            $table->string('dispatch_from_phone')->nullable();
            $table->string('dispatch_from_gstin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'bill_to_name',
                'bill_to_address',
                'bill_to_district',
                'bill_to_state',
                'bill_to_pincode',
                'bill_to_phone',
                'bill_to_gstin',
                'dispatch_from_name',
                'dispatch_from_address',
                'dispatch_from_district',
                'dispatch_from_state',
                'dispatch_from_pincode',
                'dispatch_from_phone',
                'dispatch_from_gstin',
            ]);
        });
    }
};
