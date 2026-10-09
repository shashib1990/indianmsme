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
        // 1. users.role -> enum('admin', 'adc', 'ddc', 'seller')
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'adc', 'ddc', 'seller') NULL");

        // 2. ddc_profiles.kyc_status -> enum('PENDING', 'UNDER_REVIEW', 'APPROVED', 'REJECTED')
        DB::statement("UPDATE ddc_profiles SET kyc_status = 'PENDING' WHERE kyc_status IS NULL OR kyc_status = ''");
        DB::statement("ALTER TABLE ddc_profiles MODIFY COLUMN kyc_status ENUM('PENDING', 'UNDER_REVIEW', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING'");

        // 3. pickup_stores.owner_type -> enum('Seller', 'Sub Seller')
        DB::statement("ALTER TABLE pickup_stores MODIFY COLUMN owner_type ENUM('Seller', 'Sub Seller') NOT NULL");

        // 4. pickup_stores.status -> enum('Active', 'Inactive')
        DB::statement("ALTER TABLE pickup_stores MODIFY COLUMN status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'");

        // 5. pickup_stores.delivery_coverage_type -> enum('pan_india', 'state', 'district', 'pincode')
        DB::statement("ALTER TABLE pickup_stores MODIFY COLUMN delivery_coverage_type ENUM('pan_india', 'state', 'district', 'pincode') NOT NULL DEFAULT 'pan_india'");

        // 6. pickup_store_delivery_areas.coverage_type -> enum('pan_india', 'state', 'district', 'pincode')
        DB::statement("ALTER TABLE pickup_store_delivery_areas MODIFY COLUMN coverage_type ENUM('pan_india', 'state', 'district', 'pincode') NOT NULL");

        // 7. pickup_store_delivery_areas.status -> enum('Active', 'Inactive')
        DB::statement("ALTER TABLE pickup_store_delivery_areas MODIFY COLUMN status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'");

        // 8. purchase_orders.status -> enum('Pending', 'Accepted', 'Processing', 'Dispatched', 'Received', 'Cancelled', 'Rejected')
        DB::statement("UPDATE purchase_orders SET status = 'Accepted' WHERE status = 'Partially Accepted'");
        DB::statement("UPDATE purchase_orders SET status = 'Received' WHERE status = 'Completed'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('Pending', 'Accepted', 'Processing', 'Dispatched', 'Received', 'Cancelled', 'Rejected') NOT NULL DEFAULT 'Pending'");

        // 9. purchase_orders.payment_status -> enum('Unpaid', 'Paid')
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN payment_status ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid'");

        // 10. purchase_orders.payment_verification_status -> enum('Pending', 'Verified', 'Rejected')
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN payment_verification_status ENUM('Pending', 'Verified', 'Rejected') NOT NULL DEFAULT 'Pending'");

        // 11. purchase_orders.seller_payment_status -> enum('Unpaid', 'Paid')
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN seller_payment_status ENUM('Unpaid', 'Paid') NOT NULL DEFAULT 'Unpaid'");

        // 12. purchase_orders.seller_payment_verification_status -> enum('Pending', 'Verified', 'Rejected')
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN seller_payment_verification_status ENUM('Pending', 'Verified', 'Rejected') NOT NULL DEFAULT 'Pending'");

        // 13. purchase_orders.dispatch_status -> enum('Pending', 'Dispatched', 'Delivered')
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN dispatch_status ENUM('Pending', 'Dispatched', 'Delivered') NOT NULL DEFAULT 'Pending'");

        // 14. purchase_order_items.status -> enum('Pending', 'Accepted', 'Rejected')
        DB::statement("ALTER TABLE purchase_order_items MODIFY COLUMN status ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending'");

        // 15. product_audit_logs.approval_status -> enum('Draft', 'Pending Approval', 'Approved', 'Rejected')
        DB::statement("ALTER TABLE product_audit_logs MODIFY COLUMN approval_status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE product_audit_logs MODIFY COLUMN approval_status VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE purchase_order_items MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN dispatch_status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN seller_payment_verification_status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN seller_payment_status VARCHAR(255) NOT NULL DEFAULT 'Unpaid'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN payment_verification_status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN payment_status VARCHAR(255) NOT NULL DEFAULT 'Unpaid'");
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
        DB::statement("ALTER TABLE pickup_store_delivery_areas MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Active'");
        DB::statement("ALTER TABLE pickup_store_delivery_areas MODIFY COLUMN coverage_type VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE pickup_stores MODIFY COLUMN delivery_coverage_type VARCHAR(255) NOT NULL DEFAULT 'pan_india'");
        DB::statement("ALTER TABLE pickup_stores MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Active'");
        DB::statement("ALTER TABLE pickup_stores MODIFY COLUMN owner_type VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE ddc_profiles MODIFY COLUMN kyc_status VARCHAR(255) NOT NULL DEFAULT 'PENDING'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(255) NULL");
    }
};
