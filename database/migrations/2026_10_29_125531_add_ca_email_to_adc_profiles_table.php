<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_profiles', 'ca_email')) {
                $table->string('ca_email', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('adc_profiles', 'ca_email')) {
                $table->dropColumn('ca_email');
            }
        });
    }
};