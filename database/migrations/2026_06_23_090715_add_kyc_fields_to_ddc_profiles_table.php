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
            $table->string('gst_certificate')->nullable()->after('contract_document');
            $table->string('aadhaar_card')->nullable()->after('gst_certificate');
            $table->string('fssai_certificate')->nullable()->after('aadhaar_card');
            $table->string('cancelled_cheque')->nullable()->after('fssai_certificate');
            $table->string('trade_license')->nullable()->after('cancelled_cheque');
            $table->string('ddc_agreement')->nullable()->after('trade_license');
            $table->string('franchise_deposit_receipt')->nullable()->after('ddc_agreement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'gst_certificate',
                'aadhaar_card',
                'fssai_certificate',
                'cancelled_cheque',
                'trade_license',
                'ddc_agreement',
                'franchise_deposit_receipt'
            ]);
        });
    }
};
