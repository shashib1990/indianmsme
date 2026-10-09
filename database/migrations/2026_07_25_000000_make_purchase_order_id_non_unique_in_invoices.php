<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE invoices DROP FOREIGN KEY invoices_purchase_order_id_foreign');
        } catch (\Throwable $e) {
            // Ignore if foreign key was already dropped or has different name
        }

        try {
            DB::statement('ALTER TABLE invoices DROP INDEX invoices_purchase_order_id_unique');
        } catch (\Throwable $e) {
            // Ignore if index was already dropped
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('purchase_order_id', 'invoices_purchase_order_id_foreign')
                ->references('id')
                ->on('purchase_orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for down
    }
};
