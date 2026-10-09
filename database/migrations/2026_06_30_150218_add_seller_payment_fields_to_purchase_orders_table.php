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
            $table->string('seller_payment_status')->default('Unpaid')->after('payment_status');
            $table->string('seller_payment_reference')->nullable()->after('payment_reference');
            $table->string('seller_payment_verification_status')->default('Pending')->after('payment_verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['seller_payment_status', 'seller_payment_reference', 'seller_payment_verification_status']);
        });
    }
};
