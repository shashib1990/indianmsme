<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_profiles', 'trade_name')) {
                $table->string('trade_name')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('adc_profiles', 'official_phone')) {
                $table->string('official_phone')->nullable()->after('trade_name');
            }
            if (!Schema::hasColumn('adc_profiles', 'official_email')) {
                $table->string('official_email')->nullable()->after('official_phone');
            }

            // KYC document fields
            if (!Schema::hasColumn('adc_profiles', 'master_depot_image')) {
                $table->string('master_depot_image')->nullable()->after('profile_image');
            }
            if (!Schema::hasColumn('adc_profiles', 'contract_document')) {
                $table->string('contract_document')->nullable()->after('master_depot_image');
            }
            if (!Schema::hasColumn('adc_profiles', 'gst_certificate')) {
                $table->string('gst_certificate')->nullable()->after('contract_document');
            }
            if (!Schema::hasColumn('adc_profiles', 'aadhaar_card')) {
                $table->string('aadhaar_card')->nullable()->after('gst_certificate');
            }
            if (!Schema::hasColumn('adc_profiles', 'pan_card')) {
                $table->string('pan_card')->nullable()->after('aadhaar_card');
            }
            if (!Schema::hasColumn('adc_profiles', 'fssai_certificate')) {
                $table->string('fssai_certificate')->nullable()->after('pan_card');
            }
            if (!Schema::hasColumn('adc_profiles', 'cancelled_cheque')) {
                $table->string('cancelled_cheque')->nullable()->after('fssai_certificate');
            }
            if (!Schema::hasColumn('adc_profiles', 'trade_license')) {
                $table->string('trade_license')->nullable()->after('cancelled_cheque');
            }
            if (!Schema::hasColumn('adc_profiles', 'adc_agreement')) {
                $table->string('adc_agreement')->nullable()->after('trade_license');
            }
            if (!Schema::hasColumn('adc_profiles', 'franchise_deposit_receipt')) {
                $table->string('franchise_deposit_receipt')->nullable()->after('adc_agreement');
            }

            // KYC Status and remarks
            if (!Schema::hasColumn('adc_profiles', 'kyc_status')) {
                $table->string('kyc_status')->default('PENDING')->after('franchise_deposit_receipt');
            }
            if (!Schema::hasColumn('adc_profiles', 'kyc_remarks')) {
                $table->text('kyc_remarks')->nullable()->after('kyc_status');
            }

            // Bank detail fields
            if (!Schema::hasColumn('adc_profiles', 'account_holder_name')) {
                $table->string('account_holder_name')->nullable()->after('kyc_remarks');
            }
            if (!Schema::hasColumn('adc_profiles', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('account_holder_name');
            }
            if (!Schema::hasColumn('adc_profiles', 'ifsc_code')) {
                $table->string('ifsc_code')->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('adc_profiles', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('ifsc_code');
            }
            if (!Schema::hasColumn('adc_profiles', 'branch_name')) {
                $table->string('branch_name')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('adc_profiles', 'qr_code')) {
                $table->string('qr_code')->nullable()->after('branch_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'trade_name', 'official_phone', 'official_email',
                'master_depot_image', 'contract_document', 'gst_certificate',
                'aadhaar_card', 'pan_card', 'fssai_certificate', 'cancelled_cheque',
                'trade_license', 'adc_agreement', 'franchise_deposit_receipt',
                'kyc_status', 'kyc_remarks',
                'account_holder_name', 'bank_account_number', 'ifsc_code',
                'bank_name', 'branch_name', 'qr_code',
            ]);
        });
    }
};
