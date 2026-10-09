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
        if (Schema::hasTable('ddb_codes') && !Schema::hasTable('ddc_codes')) {
            Schema::rename('ddb_codes', 'ddc_codes');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_codes') && !Schema::hasTable('ddb_codes')) {
            Schema::rename('ddc_codes', 'ddb_codes');
        }
    }
};
