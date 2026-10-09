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
            if (!Schema::hasColumn('ddc_profiles', 'adpl_district')) {
                $table->string('adpl_district')->nullable()->after('adpl_state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('ddc_profiles', 'adpl_district')) {
                $table->dropColumn('adpl_district');
            }
        });
    }
};
