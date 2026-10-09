<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes:
     * 1. Convert status column from VARCHAR default 'Paid' to ENUM('Hold','Paid','Released','Cancelled')
     *    so Hold entries can be created at accept-time and Released at stock-in time.
     * 2. Add nullable inventory_id FK to ddc_inventories for future incentive release mapping.
     * 3. Add nullable notes column for audit trail.
     */
    public function up(): void
    {
        // Step 1: Drop the existing default + change column to enum
        DB::statement("ALTER TABLE ama_transactions 
            MODIFY COLUMN status ENUM('Hold','Paid','Released','Cancelled') NOT NULL DEFAULT 'Hold'");

        // Step 2: Add inventory_id for mapping to the stocked-in inventory record
        Schema::table('ama_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')->nullable()->after('status')
                ->comment('FK to ddc_inventories — set when incentive is released on stock-in');
            $table->text('notes')->nullable()->after('inventory_id')
                ->comment('Audit notes, e.g. calculation breakdown');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('ddc_inventories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ama_transactions', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropColumn(['inventory_id', 'notes']);
        });

        DB::statement("ALTER TABLE ama_transactions 
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Paid'");
    }
};
