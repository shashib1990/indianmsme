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
        // Disable strict warnings temporarily during DDL/DML data normalization & column alteration
        DB::statement("SET SESSION sql_mode=''");

        // ─── 1. ADC ORDERS & ORDER ITEMS ─────────────────────────────────────
        if (Schema::hasTable('adc_orders')) {
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN status ENUM('Pending', 'Processing', 'Accepted', 'Partially Dispatched', 'Dispatched', 'Received', 'Completed', 'Cancelled', 'Rejected') NOT NULL DEFAULT 'Pending'");
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN payment_status ENUM('Unpaid', 'Paid', 'Pending') NOT NULL DEFAULT 'Unpaid'");
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN payment_method ENUM('Wallet', 'Bank Transfer', 'Online', 'UPI', 'Cash') NULL");
        }

        if (Schema::hasTable('adc_order_items')) {
            DB::statement("ALTER TABLE adc_order_items MODIFY COLUMN status ENUM('Pending', 'Processing', 'Accepted', 'Partially Dispatched', 'Dispatched', 'Received', 'Completed', 'Cancelled', 'Rejected') NOT NULL DEFAULT 'Pending'");
        }

        // ─── 2. INVENTORIES REFERENCE TYPES ───────────────────────────────────
        if (Schema::hasTable('adc_inventories')) {
            DB::statement("ALTER TABLE adc_inventories MODIFY COLUMN reference_type ENUM('LOCAL_PURCHASE', 'MSME_PO') NULL");
        }

        if (Schema::hasTable('ddc_inventories')) {
            DB::statement("ALTER TABLE ddc_inventories MODIFY COLUMN reference_type ENUM('Local Purchase', 'MSME PO') NULL");
        }

        // ─── 3. CREDIT LEDGERS ────────────────────────────────────────────────
        if (Schema::hasTable('adc_customer_credit_ledgers')) {
            DB::statement("UPDATE adc_customer_credit_ledgers SET payment_mode = LOWER(payment_mode) WHERE payment_mode IS NOT NULL AND payment_mode != ''");
            DB::statement("ALTER TABLE adc_customer_credit_ledgers MODIFY COLUMN payment_mode ENUM('credit', 'cash', 'card', 'upi', 'bank_transfer', 'cheque') NULL");
        }

        // ─── 4. DDC POS CARTS ────────────────────────────────────────────────
        if (Schema::hasTable('ddc_pos_carts')) {
            DB::statement("ALTER TABLE ddc_pos_carts MODIFY COLUMN status ENUM('active', 'held', 'abandoned') NOT NULL DEFAULT 'active'");
        }

        // ─── 5. DEPOT & DDC RACKS & SHELVES ──────────────────────────────────
        if (Schema::hasTable('ddc_rack_shelf_bookings')) {
            DB::statement("ALTER TABLE ddc_rack_shelf_bookings MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled', 'Expired') NOT NULL DEFAULT 'Pending'");
        }

        if (Schema::hasTable('ddc_racks')) {
            DB::statement("ALTER TABLE ddc_racks MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
        }

        if (Schema::hasTable('ddc_shelves')) {
            DB::statement("ALTER TABLE ddc_shelves MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
        }

        if (Schema::hasTable('depot_racks')) {
            DB::statement("UPDATE depot_racks SET unit_type = LOWER(unit_type) WHERE unit_type IS NOT NULL AND unit_type != ''");
            DB::statement("UPDATE depot_racks SET owner_type = LOWER(owner_type) WHERE owner_type IS NOT NULL AND owner_type != ''");
            DB::statement("ALTER TABLE depot_racks MODIFY COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
            DB::statement("ALTER TABLE depot_racks MODIFY COLUMN unit_type ENUM('shelf', 'rack', 'pallet', 'sqft', 'box') NOT NULL DEFAULT 'rack'");
            DB::statement("ALTER TABLE depot_racks MODIFY COLUMN owner_type ENUM('ddc', 'adc', 'admin', 'vendor') NOT NULL DEFAULT 'ddc'");
        }

        if (Schema::hasTable('depot_rack_bookings')) {
            DB::statement("UPDATE depot_rack_bookings SET owner_type = LOWER(owner_type) WHERE owner_type IS NOT NULL AND owner_type != ''");
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN owner_type ENUM('ddc', 'adc', 'admin', 'vendor') NOT NULL DEFAULT 'ddc'");
        }

        if (Schema::hasTable('depot_rack_images')) {
            DB::statement("UPDATE depot_rack_images SET owner_type = LOWER(owner_type) WHERE owner_type IS NOT NULL AND owner_type != ''");
            DB::statement("ALTER TABLE depot_rack_images MODIFY COLUMN owner_type ENUM('ddc', 'adc', 'admin', 'vendor') NOT NULL DEFAULT 'ddc'");
        }

        // ─── 6. AUDIT LOGS, LOGS & NOTIFICATIONS ──────────────────────────────
        if (Schema::hasTable('ddc_pincode_audit_logs')) {
            DB::statement("ALTER TABLE ddc_pincode_audit_logs MODIFY COLUMN action ENUM('ASSIGNED', 'UNASSIGNED', 'REMOVED') NOT NULL");
        }

        if (Schema::hasTable('impersonation_logs')) {
            DB::statement("ALTER TABLE impersonation_logs MODIFY COLUMN target_role ENUM('admin', 'adc', 'ddc', 'seller') NOT NULL");
        }

        if (Schema::hasTable('notifications')) {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN recipient_type ENUM('adc', 'admin', 'ddc', 'pickup_store', 'seller') NULL");
        }

        // ─── 7. SELLER PROFILES ───────────────────────────────────────────────
        if (Schema::hasTable('seller_profiles')) {
            DB::statement("UPDATE seller_profiles SET company_type = 'Proprietorship Firm' WHERE company_type = 'Company'");
            DB::statement("ALTER TABLE seller_profiles MODIFY COLUMN company_type ENUM('Proprietorship Firm', 'Partnership Firm', 'Private Limited Company', 'Public Limited Company', 'LLP', 'Company', 'Other') NULL");
            DB::statement("ALTER TABLE seller_profiles MODIFY COLUMN seller_type ENUM('Manufacturer', 'Wholesaler', 'Trader', 'Distributor', 'Retailer') NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('seller_profiles')) {
            DB::statement("ALTER TABLE seller_profiles MODIFY COLUMN seller_type VARCHAR(255) NULL");
            DB::statement("ALTER TABLE seller_profiles MODIFY COLUMN company_type VARCHAR(255) NULL");
        }
        if (Schema::hasTable('notifications')) {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN recipient_type VARCHAR(255) NULL");
        }
        if (Schema::hasTable('impersonation_logs')) {
            DB::statement("ALTER TABLE impersonation_logs MODIFY COLUMN target_role VARCHAR(255) NOT NULL");
        }
        if (Schema::hasTable('ddc_pincode_audit_logs')) {
            DB::statement("ALTER TABLE ddc_pincode_audit_logs MODIFY COLUMN action VARCHAR(255) NOT NULL");
        }
        if (Schema::hasTable('depot_rack_images')) {
            DB::statement("ALTER TABLE depot_rack_images MODIFY COLUMN owner_type VARCHAR(255) NOT NULL DEFAULT 'ddc'");
        }
        if (Schema::hasTable('depot_rack_bookings')) {
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN owner_type VARCHAR(255) NOT NULL DEFAULT 'ddc'");
        }
        if (Schema::hasTable('depot_racks')) {
            DB::statement("ALTER TABLE depot_racks MODIFY COLUMN owner_type VARCHAR(255) NOT NULL DEFAULT 'ddc'");
            DB::statement("ALTER TABLE depot_racks MODIFY COLUMN unit_type VARCHAR(255) NOT NULL DEFAULT 'rack'");
            DB::statement("ALTER TABLE depot_racks MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active'");
        }
        if (Schema::hasTable('ddc_shelves')) {
            DB::statement("ALTER TABLE ddc_shelves MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active'");
        }
        if (Schema::hasTable('ddc_racks')) {
            DB::statement("ALTER TABLE ddc_racks MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'active'");
        }
        if (Schema::hasTable('ddc_rack_shelf_bookings')) {
            DB::statement("ALTER TABLE ddc_rack_shelf_bookings MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }
        if (Schema::hasTable('ddc_pos_carts')) {
            DB::statement("ALTER TABLE ddc_pos_carts MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'active'");
        }
        if (Schema::hasTable('adc_customer_credit_ledgers')) {
            DB::statement("ALTER TABLE adc_customer_credit_ledgers MODIFY COLUMN payment_mode VARCHAR(50) NULL");
        }
        if (Schema::hasTable('ddc_inventories')) {
            DB::statement("ALTER TABLE ddc_inventories MODIFY COLUMN reference_type VARCHAR(255) NULL");
        }
        if (Schema::hasTable('adc_inventories')) {
            DB::statement("ALTER TABLE adc_inventories MODIFY COLUMN reference_type VARCHAR(255) NULL");
        }
        if (Schema::hasTable('adc_order_items')) {
            DB::statement("ALTER TABLE adc_order_items MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }
        if (Schema::hasTable('adc_orders')) {
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN payment_method VARCHAR(255) NULL");
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN payment_status VARCHAR(255) NOT NULL DEFAULT 'Unpaid'");
            DB::statement("ALTER TABLE adc_orders MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }
    }
};
