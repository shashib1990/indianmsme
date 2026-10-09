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
        Schema::table('ddc_profiles', function (Blueprint $table) {
            // Bank Details
            $table->string('account_holder_name')->nullable()->after('franchise_deposit_receipt');
            $table->string('bank_account_number')->nullable()->after('account_holder_name');
            $table->string('ifsc_code')->nullable()->after('bank_account_number');
            $table->string('bank_name')->nullable()->after('ifsc_code');
            $table->string('branch_name')->nullable()->after('bank_name');

            // KYC Status (PENDING | UNDER_REVIEW | APPROVED | REJECTED)
            $table->string('kyc_status')->default('PENDING')->after('branch_name');
            $table->text('kyc_remarks')->nullable()->after('kyc_status');

            // QR Code
            $table->string('qr_code')->nullable()->after('kyc_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'account_holder_name', 'bank_account_number', 'ifsc_code',
                'bank_name', 'branch_name', 'kyc_status', 'kyc_remarks', 'qr_code'
            ]);
        });
    }
};
