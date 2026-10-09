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
            if (Schema::hasColumn('ddc_profiles', 'ddb_owner_name')) {
                $table->renameColumn('ddb_owner_name', 'ddc_owner_name');
            }
            if (Schema::hasColumn('ddc_profiles', 'ddb_address')) {
                $table->renameColumn('ddb_address', 'ddc_address');
            }
            if (Schema::hasColumn('ddc_profiles', 'ddb_gst_number')) {
                $table->renameColumn('ddb_gst_number', 'ddc_gst_number');
            }
            if (Schema::hasColumn('ddc_profiles', 'ddb_pincode')) {
                $table->renameColumn('ddb_pincode', 'ddc_pincode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('ddc_profiles', 'ddc_owner_name')) {
                $table->renameColumn('ddc_owner_name', 'ddb_owner_name');
            }
            if (Schema::hasColumn('ddc_profiles', 'ddc_address')) {
                $table->renameColumn('ddc_address', 'ddb_address');
            }
            if (Schema::hasColumn('ddc_profiles', 'ddc_gst_number')) {
                $table->renameColumn('ddc_gst_number', 'ddb_gst_number');
            }
            if (Schema::hasColumn('ddc_profiles', 'ddc_pincode')) {
                $table->renameColumn('ddc_pincode', 'ddb_pincode');
            }
        });
    }
};
