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
        if (Schema::hasTable('invoices')) {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN type ENUM('ddc_po', 'adc_order', 'adc_pos_retail', 'ddc_pos_retail', 'adpl_to_adc', 'ddc_to_adc', 'excess_stock_ddc', 'excess_stock_adc') NOT NULL DEFAULT 'ddc_po'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN type ENUM('ddc_po', 'adc_order', 'adc_pos_retail', 'ddc_pos_retail', 'adpl_to_adc', 'ddc_to_adc') NOT NULL DEFAULT 'ddc_po'");
        }
    }
};
