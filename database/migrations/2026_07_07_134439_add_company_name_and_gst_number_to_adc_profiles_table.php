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
        Schema::table('adc_profiles', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('advisor_code');
            $table->string('gst_number')->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'gst_number']);
        });
    }
};
