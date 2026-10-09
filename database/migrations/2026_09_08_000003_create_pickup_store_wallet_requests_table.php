<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pickup_store_wallet_requests')) {
            Schema::create('pickup_store_wallet_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pickup_store_id');
                $table->string('type')->default('topup'); // topup, withdrawal
                $table->decimal('amount', 12, 2);
                $table->string('utr_number')->nullable();
                $table->string('payment_receipt')->nullable();
                $table->string('account_holder_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('ifsc_code')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED, TRANSFERRED
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('pickup_store_id')->references('id')->on('pickup_stores')->onDelete('cascade');
                $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_store_wallet_requests');
    }
};
