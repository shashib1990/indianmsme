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
            $table->string('type')->nullable()->after('user_id');
            $table->string('code')->nullable()->after('type');
            $table->string('advisor_code')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            $table->dropColumn(['type', 'code', 'advisor_code']);
        });
    }
};
