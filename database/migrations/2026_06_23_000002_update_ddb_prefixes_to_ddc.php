<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update ddc_codes table (replace prefix DDB with DDC)
        DB::statement("UPDATE ddc_codes SET code = REPLACE(code, 'DDB', 'DDC') WHERE code LIKE 'DDB%'");

        // Update ddc_profiles table (replace prefix DDB with DDC in ddc_code)
        DB::statement("UPDATE ddc_profiles SET ddc_code = REPLACE(ddc_code, 'DDB', 'DDC') WHERE ddc_code LIKE 'DDB%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update ddc_codes table (replace prefix DDC with DDB)
        DB::statement("UPDATE ddc_codes SET code = REPLACE(code, 'DDC', 'DDB') WHERE code LIKE 'DDC%'");

        // Update ddc_profiles table (replace prefix DDC with DDB in ddc_code)
        DB::statement("UPDATE ddc_profiles SET ddc_code = REPLACE(ddc_code, 'DDC', 'DDB') WHERE ddc_code LIKE 'DDC%'");
    }
};
