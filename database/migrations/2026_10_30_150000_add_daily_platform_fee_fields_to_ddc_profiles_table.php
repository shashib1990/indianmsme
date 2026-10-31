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
            if (!Schema::hasColumn('ddc_profiles', 'deactivation_reason')) {
                $table->string('deactivation_reason', 500)->nullable()->after('per_user_daily_charge')
                    ->comment('Reason for POS deactivation (e.g. wallet balance exhausted)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('ddc_profiles', 'deactivation_reason')) {
                $table->dropColumn('deactivation_reason');
            }
        });
    }
};
