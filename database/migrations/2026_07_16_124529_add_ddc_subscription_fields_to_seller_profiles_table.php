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
            $table->boolean('is_ddc_subscribed')->default(false)->after('kyc_status');
            $table->decimal('ddc_ama_commission_percentage', 5, 2)->default(0.00)->after('is_ddc_subscribed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_ddc_subscribed', 'ddc_ama_commission_percentage']);
        });
    }
};
