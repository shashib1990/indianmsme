<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE adc_debit_notes MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'PENDING'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE adc_debit_notes MODIFY COLUMN status ENUM('PENDING','PENDING_SELLER_APPROVAL','SELLER_APPROVED','SELLER_REJECTED','ADPL_VERIFIED','SETTLED','CANCELLED') NOT NULL DEFAULT 'PENDING'");
    }
};

