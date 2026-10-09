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
            $indexesToDrop = [
                'invoices_supplier_gstin_invoice_number_unique',
                'invoices_invoice_number_unique',
                'invoices_purchase_order_id_unique'
            ];

            foreach ($indexesToDrop as $indexName) {
                try {
                    DB::statement("ALTER TABLE invoices DROP INDEX {$indexName}");
                } catch (\Throwable $e) {
                    // Ignore if index does not exist
                }
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
