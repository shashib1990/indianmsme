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
        // 1. Modify products status column to add 'Inactive'
        DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected', 'Inactive') NOT NULL DEFAULT 'Draft'");

        // 2. Modify product_audit_logs approval_status column to add 'Inactive'
        DB::statement("ALTER TABLE product_audit_logs MODIFY COLUMN approval_status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected', 'Inactive') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original options
        DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected') NOT NULL DEFAULT 'Draft'");
        DB::statement("ALTER TABLE product_audit_logs MODIFY COLUMN approval_status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected') NOT NULL");
    }
};
