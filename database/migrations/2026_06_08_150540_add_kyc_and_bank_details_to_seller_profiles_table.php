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
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->string('seller_id')->nullable()->unique()->after('user_id');
            $table->string('authorized_rep_name')->nullable()->after('seller_id');
            $table->unsignedBigInteger('category_id')->nullable()->after('department_id');
            $table->enum('kyc_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->after('category_id');

            // KYC Documents
            $table->string('gst_certificate')->nullable()->after('kyc_status');
            $table->string('pan_card')->nullable()->after('gst_certificate');
            $table->string('udyam_certificate')->nullable()->after('pan_card');
            $table->string('incorporation_certificate')->nullable()->after('udyam_certificate');
            $table->string('cancelled_cheque')->nullable()->after('incorporation_certificate');
            $table->string('company_logo')->nullable()->after('cancelled_cheque');
            $table->string('company_banner')->nullable()->after('company_logo');

            // Bank Details
            $table->string('account_holder_name')->nullable()->after('company_banner');
            $table->string('bank_account_number')->nullable()->after('account_holder_name');
            $table->string('ifsc_code')->nullable()->after('bank_account_number');
            $table->string('bank_name')->nullable()->after('ifsc_code');
            $table->string('branch_name')->nullable()->after('bank_name');

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'seller_id',
                'authorized_rep_name',
                'category_id',
                'kyc_status',
                'gst_certificate',
                'pan_card',
                'udyam_certificate',
                'incorporation_certificate',
                'cancelled_cheque',
                'company_logo',
                'company_banner',
                'account_holder_name',
                'bank_account_number',
                'ifsc_code',
                'bank_name',
                'branch_name'
            ]);
        });
    }
};
