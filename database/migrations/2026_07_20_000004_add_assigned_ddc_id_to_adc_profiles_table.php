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
        if (!Schema::hasColumn('adc_profiles', 'assigned_ddc_id')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('assigned_ddc_id')->nullable()->after('wallet_balance');
                $table->foreign('assigned_ddc_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('adc_profiles', 'assigned_ddc_id')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->dropForeign(['assigned_ddc_id']);
                $table->dropColumn('assigned_ddc_id');
            });
        }
    }
};
