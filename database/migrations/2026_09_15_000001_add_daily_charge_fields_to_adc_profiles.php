<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_profiles', 'first_sale_at')) {
                $table->timestamp('first_sale_at')->nullable()->after('per_user_daily_charge')
                    ->comment('Timestamp of first ever sale made by this ADC');
            }
            if (!Schema::hasColumn('adc_profiles', 'deactivation_reason')) {
                $table->string('deactivation_reason', 500)->nullable()->after('first_sale_at')
                    ->comment('Reason for account deactivation (e.g. wallet exhausted)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('adc_profiles', 'first_sale_at')) {
                $table->dropColumn('first_sale_at');
            }
            if (Schema::hasColumn('adc_profiles', 'deactivation_reason')) {
                $table->dropColumn('deactivation_reason');
            }
        });
    }
};
