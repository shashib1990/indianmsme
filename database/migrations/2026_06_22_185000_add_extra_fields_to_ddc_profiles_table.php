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
            $table->string('ddc_code')->nullable()->unique()->after('user_id');
            $table->string('ddb_owner_name')->nullable()->after('ddc_code');
            $table->string('official_phone')->nullable()->after('ddb_owner_name');
            $table->string('official_email')->nullable()->after('official_phone');
            $table->text('adpl_address')->nullable()->after('official_email');
            $table->text('ddb_address')->nullable()->after('adpl_address');
            $table->string('adpl_gst_number')->nullable()->after('ddb_address');
            $table->string('adpl_company_name')->nullable()->after('adpl_gst_number');
            $table->string('ddb_gst_number')->nullable()->after('adpl_company_name');
            $table->string('company_name')->nullable()->after('ddb_gst_number');
            $table->string('phone')->nullable()->after('company_name');
            $table->string('email')->nullable()->after('phone');
            $table->string('adpl_pincode')->nullable()->after('email');
            $table->string('ddb_pincode')->nullable()->after('adpl_pincode');
            $table->string('master_depot_image')->nullable()->after('ddb_pincode');
            $table->string('adpl_state')->nullable()->after('master_depot_image');
            $table->string('contract_document')->nullable()->after('adpl_state');
            $table->string('trade_name')->nullable()->after('contract_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ddc_code',
                'ddb_owner_name',
                'official_phone',
                'official_email',
                'adpl_address',
                'ddb_address',
                'adpl_gst_number',
                'adpl_company_name',
                'ddb_gst_number',
                'company_name',
                'phone',
                'email',
                'adpl_pincode',
                'ddb_pincode',
                'master_depot_image',
                'adpl_state',
                'contract_document',
                'trade_name',
            ]);
        });
    }
};
