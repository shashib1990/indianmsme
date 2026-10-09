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
        if (Schema::hasTable('adc_pos_customers') && !Schema::hasColumn('adc_pos_customers', 'abhhyam_code')) {
            Schema::table('adc_pos_customers', function (Blueprint $table) {
                $table->string('abhhyam_code')->nullable()->after('mobile');
            });
        }

        if (Schema::hasTable('ddc_pos_customers') && !Schema::hasColumn('ddc_pos_customers', 'abhhyam_code')) {
            Schema::table('ddc_pos_customers', function (Blueprint $table) {
                $table->string('abhhyam_code')->nullable()->after('mobile');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_pos_customers') && Schema::hasColumn('adc_pos_customers', 'abhhyam_code')) {
            Schema::table('adc_pos_customers', function (Blueprint $table) {
                $table->dropColumn('abhhyam_code');
            });
        }

        if (Schema::hasTable('ddc_pos_customers') && Schema::hasColumn('ddc_pos_customers', 'abhhyam_code')) {
            Schema::table('ddc_pos_customers', function (Blueprint $table) {
                $table->dropColumn('abhhyam_code');
            });
        }
    }
};
