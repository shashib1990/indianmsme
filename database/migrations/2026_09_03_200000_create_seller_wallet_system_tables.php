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
        // 1. Add wallet_balance to seller_profiles if not exists
        if (Schema::hasTable('seller_profiles') && !Schema::hasColumn('seller_profiles', 'wallet_balance')) {
            Schema::table('seller_profiles', function (Blueprint $table) {
                $table->decimal('wallet_balance', 12, 2)->default(0.00)->after('kyc_status');
            });
        }

        // 2. Create seller_wallet_requests table
        if (!Schema::hasTable('seller_wallet_requests')) {
            Schema::create('seller_wallet_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->string('type', 20)->default('topup'); // topup or withdrawal
                $table->decimal('amount', 12, 2);
                $table->string('utr_number')->nullable();
                $table->string('payment_receipt')->nullable();
                $table->string('account_holder_name')->nullable();
                $table->string('bank_account_number')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('ifsc_code')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('status', 20)->default('PENDING'); // PENDING, APPROVED, REJECTED
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Create seller_wallets table (for transaction log)
        if (!Schema::hasTable('seller_wallets')) {
            Schema::create('seller_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->string('type', 20); // credit, debit
                $table->text('description');
                $table->string('reference_id')->nullable();
                $table->string('reference_type')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_wallets');
        Schema::dropIfExists('seller_wallet_requests');

        if (Schema::hasTable('seller_profiles') && Schema::hasColumn('seller_profiles', 'wallet_balance')) {
            Schema::table('seller_profiles', function (Blueprint $table) {
                $table->dropColumn('wallet_balance');
            });
        }
    }
};
