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
        if (Schema::hasTable('adc_pos_customers') && !Schema::hasColumn('adc_pos_customers', 'gst_number')) {
            Schema::table('adc_pos_customers', function (Blueprint $table) {
                $table->string('gst_number', 20)->nullable()->after('pincode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_pos_customers') && Schema::hasColumn('adc_pos_customers', 'gst_number')) {
            Schema::table('adc_pos_customers', function (Blueprint $table) {
                $table->dropColumn('gst_number');
            });
        }
    }
};
