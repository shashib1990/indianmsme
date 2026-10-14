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
            if (!Schema::hasColumn('adc_profiles', 'show_order')) {
                $table->enum('show_order', ['Yes', 'No'])->default('Yes')->after('pos_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adc_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('adc_profiles', 'show_order')) {
                $table->dropColumn('show_order');
            }
        });
    }
};
