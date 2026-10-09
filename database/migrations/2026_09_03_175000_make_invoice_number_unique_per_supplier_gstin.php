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
            // Drop old global unique index on invoice_number if it exists
            try {
                DB::statement("ALTER TABLE invoices DROP INDEX invoices_invoice_number_unique");
            } catch (\Throwable $e) {
                // Ignore if index doesn't exist
            }

            // Drop composite unique index on (supplier_gstin, invoice_number) if it exists
            try {
                DB::statement("ALTER TABLE invoices DROP INDEX invoices_supplier_gstin_invoice_number_unique");
            } catch (\Throwable $e) {
                // Ignore if index doesn't exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
