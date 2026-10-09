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
            $table->unsignedBigInteger('seller_id')->nullable()->after('ddc_id');
            $table->unsignedBigInteger('pickup_store_id')->nullable()->after('seller_id');
            $table->string('payment_verification_status')->default('Pending')->after('payment_status'); // Pending, Verified, Rejected
            $table->string('dispatch_status')->default('Pending')->after('payment_verification_status'); // Pending, Dispatched, Delivered
            $table->text('rejection_reason')->nullable()->after('dispatch_status');

            $table->foreign('seller_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('pickup_store_id')->references('id')->on('pickup_stores')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['pickup_store_id']);
            $table->dropColumn(['seller_id', 'pickup_store_id', 'payment_verification_status', 'dispatch_status', 'rejection_reason']);
        });
    }
};
