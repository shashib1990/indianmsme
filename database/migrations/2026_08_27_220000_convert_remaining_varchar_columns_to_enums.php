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

        // ─── 1. ADC POS ORDERS ───────────────────────────────────────────────
        if (Schema::hasTable('adc_pos_orders')) {
            DB::statement("UPDATE adc_pos_orders SET payment_mode = LOWER(payment_mode) WHERE payment_mode IS NOT NULL AND payment_mode != ''");
            DB::statement("UPDATE adc_pos_orders SET payment_status = LOWER(payment_status) WHERE payment_status IS NOT NULL AND payment_status != ''");
            DB::statement("UPDATE adc_pos_orders SET return_status = 'none' WHERE return_status IS NULL OR return_status = '' OR return_status IN ('None', 'none')");
            DB::statement("UPDATE adc_pos_orders SET return_status = 'partial' WHERE return_status IN ('Partially Returned', 'partial')");
            DB::statement("UPDATE adc_pos_orders SET return_status = 'full' WHERE return_status IN ('Fully Returned', 'full')");

            DB::statement("ALTER TABLE adc_pos_orders MODIFY COLUMN payment_mode ENUM('cash', 'card', 'upi', 'credit', 'split') NOT NULL DEFAULT 'cash'");
            DB::statement("ALTER TABLE adc_pos_orders MODIFY COLUMN payment_status ENUM('paid', 'unpaid', 'partial', 'credit') NOT NULL DEFAULT 'unpaid'");
            DB::statement("ALTER TABLE adc_pos_orders MODIFY COLUMN return_status ENUM('none', 'partial', 'full') NOT NULL DEFAULT 'none'");
        }

        // ─── 2. DDC POS ORDERS ───────────────────────────────────────────────
        if (Schema::hasTable('ddc_pos_orders')) {
            DB::statement("UPDATE ddc_pos_orders SET payment_mode = LOWER(payment_mode) WHERE payment_mode IS NOT NULL AND payment_mode != ''");
            DB::statement("UPDATE ddc_pos_orders SET payment_status = LOWER(payment_status) WHERE payment_status IS NOT NULL AND payment_status != ''");
            DB::statement("UPDATE ddc_pos_orders SET return_status = 'none' WHERE return_status IS NULL OR return_status = '' OR return_status IN ('None', 'none')");
            DB::statement("UPDATE ddc_pos_orders SET return_status = 'partial' WHERE return_status IN ('Partially Returned', 'partial')");
            DB::statement("UPDATE ddc_pos_orders SET return_status = 'full' WHERE return_status IN ('Fully Returned', 'full')");

            DB::statement("ALTER TABLE ddc_pos_orders MODIFY COLUMN payment_mode ENUM('cash', 'card', 'upi', 'credit', 'split') NOT NULL DEFAULT 'cash'");
            DB::statement("ALTER TABLE ddc_pos_orders MODIFY COLUMN payment_status ENUM('paid', 'unpaid', 'partial', 'credit') NOT NULL DEFAULT 'unpaid'");
            DB::statement("ALTER TABLE ddc_pos_orders MODIFY COLUMN return_status ENUM('none', 'partial', 'full') NOT NULL DEFAULT 'none'");
        }

        // ─── 3. SALES RETURNS ────────────────────────────────────────────────
        if (Schema::hasTable('adc_pos_sales_returns')) {
            DB::statement("UPDATE adc_pos_sales_returns SET refund_mode = LOWER(refund_mode) WHERE refund_mode IS NOT NULL AND refund_mode != ''");
            DB::statement("ALTER TABLE adc_pos_sales_returns MODIFY COLUMN refund_mode ENUM('cash', 'credit', 'wallet') NOT NULL DEFAULT 'cash'");
        }

        if (Schema::hasTable('ddc_pos_sales_returns')) {
            DB::statement("UPDATE ddc_pos_sales_returns SET refund_mode = LOWER(refund_mode) WHERE refund_mode IS NOT NULL AND refund_mode != ''");
            DB::statement("ALTER TABLE ddc_pos_sales_returns MODIFY COLUMN refund_mode ENUM('cash', 'credit', 'wallet') NOT NULL DEFAULT 'cash'");
        }

        // ─── 4. CUSTOMER CREDIT LEDGERS & PAYMENTS ───────────────────────────
        if (Schema::hasTable('adc_customer_credit_ledgers')) {
            DB::statement("UPDATE adc_customer_credit_ledgers SET transaction_type = 'credit_sale' WHERE transaction_type IN ('Credit Sale', 'credit_sale')");
            DB::statement("UPDATE adc_customer_credit_ledgers SET transaction_type = 'payment' WHERE transaction_type IN ('Payment Received', 'payment')");
            DB::statement("UPDATE adc_customer_credit_ledgers SET transaction_type = 'return_refund' WHERE transaction_type IN ('Return Refund', 'return_refund')");
            DB::statement("UPDATE adc_customer_credit_ledgers SET transaction_type = 'adjustment' WHERE transaction_type IN ('Adjustment', 'adjustment')");

            DB::statement("ALTER TABLE adc_customer_credit_ledgers MODIFY COLUMN transaction_type ENUM('credit_sale', 'payment', 'return_refund', 'adjustment') NOT NULL");
        }

        if (Schema::hasTable('adc_customer_payments')) {
            DB::statement("UPDATE adc_customer_payments SET payment_mode = LOWER(payment_mode) WHERE payment_mode IS NOT NULL AND payment_mode != ''");
            DB::statement("ALTER TABLE adc_customer_payments MODIFY COLUMN payment_mode ENUM('cash', 'card', 'upi', 'bank_transfer', 'cheque') NOT NULL DEFAULT 'cash'");
        }

        // ─── 5. DEPOT RACK BOOKINGS & IMAGES ──────────────────────────────────
        if (Schema::hasTable('depot_rack_bookings')) {
            DB::statement("UPDATE depot_rack_bookings SET status = 'Pending' WHERE status = 'Pending Payment Verification'");
            DB::statement("UPDATE depot_rack_bookings SET unit_type = LOWER(unit_type) WHERE unit_type IS NOT NULL AND unit_type != ''");
            DB::statement("UPDATE depot_rack_bookings SET payment_method = 'UPI' WHERE payment_method LIKE '%UPI%'");
            DB::statement("UPDATE depot_rack_bookings SET payment_method = 'Bank Transfer' WHERE payment_method LIKE '%Bank%'");
            DB::statement("UPDATE depot_rack_bookings SET payment_method = 'Wallet' WHERE payment_method LIKE '%Wallet%'");
            DB::statement("UPDATE depot_rack_bookings SET payment_method = 'Online' WHERE payment_method LIKE '%Online%'");

            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled', 'Expired') NOT NULL DEFAULT 'Pending'");
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN payment_method ENUM('Wallet', 'Bank Transfer', 'Online', 'UPI') NULL");
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN unit_type ENUM('shelf', 'rack', 'pallet', 'sqft', 'box') NOT NULL DEFAULT 'rack'");
        }

        if (Schema::hasTable('depot_rack_images')) {
            DB::statement("UPDATE depot_rack_images SET approval_status = LOWER(approval_status) WHERE approval_status IS NOT NULL AND approval_status != ''");
            DB::statement("ALTER TABLE depot_rack_images MODIFY COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }

        // ─── 6. INCENTIVE HOLDS ──────────────────────────────────────────────
        if (Schema::hasTable('incentive_holds')) {
            DB::statement("ALTER TABLE incentive_holds MODIFY COLUMN status ENUM('Hold', 'Paid', 'Released', 'Cancelled', 'Partial_Released', 'Fully_Released') NOT NULL DEFAULT 'Hold'");
            DB::statement("ALTER TABLE incentive_holds MODIFY COLUMN type ENUM('SSI', 'FSI') NOT NULL DEFAULT 'SSI'");
        }

        // ─── 7. INVOICES ─────────────────────────────────────────────────────
        if (Schema::hasTable('invoices')) {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN type ENUM('ddc_po', 'adc_order', 'adc_pos_retail', 'ddc_pos_retail', 'adpl_to_adc', 'ddc_to_adc') NOT NULL DEFAULT 'ddc_po'");
        }

        // ─── 8. PROFILES ─────────────────────────────────────────────────────
        if (Schema::hasTable('seller_profiles')) {
            DB::statement("UPDATE seller_profiles SET ddc_subscription_status = 'none' WHERE ddc_subscription_status IS NULL OR ddc_subscription_status = ''");
            DB::statement("ALTER TABLE seller_profiles MODIFY COLUMN ddc_subscription_status ENUM('none', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'none'");
        }

        if (Schema::hasTable('adc_profiles')) {
            DB::statement("ALTER TABLE adc_profiles MODIFY COLUMN type ENUM('physical', 'virtual', 'ASC', 'MVS') NOT NULL DEFAULT 'physical'");
        }

        if (Schema::hasTable('ddc_profiles')) {
            DB::statement("ALTER TABLE ddc_profiles MODIFY COLUMN type ENUM('physical', 'virtual') NOT NULL DEFAULT 'physical'");
        }

        // ─── 9. WALLET REQUESTS & TRANSACTIONS ────────────────────────────────
        if (Schema::hasTable('adc_wallet_requests')) {
            DB::statement("ALTER TABLE adc_wallet_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }

        if (Schema::hasTable('ddc_wallet_requests')) {
            DB::statement("ALTER TABLE ddc_wallet_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }

        if (Schema::hasTable('wallet_transactions')) {
            DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('credit', 'debit') NOT NULL");
        }

        // ─── 10. PRODUCTS ────────────────────────────────────────────────────
        if (Schema::hasTable('products')) {
            DB::statement("UPDATE products SET status = 'Pending Approval' WHERE status = 'pending'");
            DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected', 'Inactive') NOT NULL DEFAULT 'Pending Approval'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            DB::statement("ALTER TABLE products MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending Approval'");
        }
        if (Schema::hasTable('wallet_transactions')) {
            DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type VARCHAR(20) NOT NULL");
        }
        if (Schema::hasTable('ddc_wallet_requests')) {
            DB::statement("ALTER TABLE ddc_wallet_requests MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }
        if (Schema::hasTable('adc_wallet_requests')) {
            DB::statement("ALTER TABLE adc_wallet_requests MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }
        if (Schema::hasTable('ddc_profiles')) {
            DB::statement("ALTER TABLE ddc_profiles MODIFY COLUMN type VARCHAR(255) NOT NULL DEFAULT 'physical'");
        }
        if (Schema::hasTable('adc_profiles')) {
            DB::statement("ALTER TABLE adc_profiles MODIFY COLUMN type VARCHAR(255) NOT NULL DEFAULT 'physical'");
        }
        if (Schema::hasTable('seller_profiles')) {
            DB::statement("ALTER TABLE seller_profiles MODIFY COLUMN ddc_subscription_status VARCHAR(255) NOT NULL DEFAULT 'none'");
        }
        if (Schema::hasTable('invoices')) {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN type VARCHAR(255) NOT NULL DEFAULT 'ddc_po'");
        }
        if (Schema::hasTable('incentive_holds')) {
            DB::statement("ALTER TABLE incentive_holds MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'SSI'");
            DB::statement("ALTER TABLE incentive_holds MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Hold'");
        }
        if (Schema::hasTable('depot_rack_images')) {
            DB::statement("ALTER TABLE depot_rack_images MODIFY COLUMN approval_status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        }
        if (Schema::hasTable('depot_rack_bookings')) {
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN unit_type VARCHAR(255) NOT NULL DEFAULT 'rack'");
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN payment_method VARCHAR(255) NULL");
            DB::statement("ALTER TABLE depot_rack_bookings MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        }
        if (Schema::hasTable('adc_customer_payments')) {
            DB::statement("ALTER TABLE adc_customer_payments MODIFY COLUMN payment_mode VARCHAR(20) NOT NULL DEFAULT 'cash'");
        }
        if (Schema::hasTable('adc_customer_credit_ledgers')) {
            DB::statement("ALTER TABLE adc_customer_credit_ledgers MODIFY COLUMN transaction_type VARCHAR(50) NOT NULL");
        }
        if (Schema::hasTable('ddc_pos_sales_returns')) {
            DB::statement("ALTER TABLE ddc_pos_sales_returns MODIFY COLUMN refund_mode VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
        if (Schema::hasTable('adc_pos_sales_returns')) {
            DB::statement("ALTER TABLE adc_pos_sales_returns MODIFY COLUMN refund_mode VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
        if (Schema::hasTable('ddc_pos_orders')) {
            DB::statement("ALTER TABLE ddc_pos_orders MODIFY COLUMN return_status VARCHAR(20) NOT NULL DEFAULT 'none'");
            DB::statement("ALTER TABLE ddc_pos_orders MODIFY COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid'");
            DB::statement("ALTER TABLE ddc_pos_orders MODIFY COLUMN payment_mode VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
        if (Schema::hasTable('adc_pos_orders')) {
            DB::statement("ALTER TABLE adc_pos_orders MODIFY COLUMN return_status VARCHAR(20) NOT NULL DEFAULT 'none'");
            DB::statement("ALTER TABLE adc_pos_orders MODIFY COLUMN payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid'");
            DB::statement("ALTER TABLE adc_pos_orders MODIFY COLUMN payment_mode VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
    }
};
