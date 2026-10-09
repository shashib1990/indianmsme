<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('adc_wallets')) {
            DB::statement("ALTER TABLE adc_wallets MODIFY description TEXT NULL");
        }
        if (Schema::hasTable('ddc_wallets')) {
            DB::statement("ALTER TABLE ddc_wallets MODIFY description TEXT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_wallets')) {
            DB::statement("ALTER TABLE adc_wallets MODIFY description VARCHAR(255) NULL");
        }
        if (Schema::hasTable('ddc_wallets')) {
            DB::statement("ALTER TABLE ddc_wallets MODIFY description VARCHAR(255) NULL");
        }
    }
};
