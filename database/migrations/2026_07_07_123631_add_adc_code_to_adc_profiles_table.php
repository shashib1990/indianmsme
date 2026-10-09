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
        if (!Schema::hasColumn('adc_profiles', 'adc_code')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->string('adc_code')->nullable()->after('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('adc_profiles', 'adc_code')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->dropColumn('adc_code');
            });
        }
    }
};
