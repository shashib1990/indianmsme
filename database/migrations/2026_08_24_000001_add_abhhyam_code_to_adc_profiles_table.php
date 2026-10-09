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
        if (Schema::hasTable('adc_profiles') && !Schema::hasColumn('adc_profiles', 'abhhyam_code')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->string('abhhyam_code')->nullable()->after('advisor_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_profiles') && Schema::hasColumn('adc_profiles', 'abhhyam_code')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->dropColumn('abhhyam_code');
            });
        }
    }
};
