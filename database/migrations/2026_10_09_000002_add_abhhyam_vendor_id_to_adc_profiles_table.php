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
        if (Schema::hasTable('adc_profiles')) {
            if (!Schema::hasColumn('adc_profiles', 'abhhyam_vendor_id')) {
                Schema::table('adc_profiles', function (Blueprint $table) {
                    $table->unsignedBigInteger('abhhyam_vendor_id')->nullable()->after('abhhyam_code');
                });
            } else {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE adc_profiles MODIFY COLUMN abhhyam_vendor_id BIGINT UNSIGNED NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_profiles') && Schema::hasColumn('adc_profiles', 'abhhyam_vendor_id')) {
            Schema::table('adc_profiles', function (Blueprint $table) {
                $table->dropColumn('abhhyam_vendor_id');
            });
        }
    }
};
