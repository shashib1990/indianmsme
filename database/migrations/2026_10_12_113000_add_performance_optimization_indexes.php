<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to safely add an index if it doesn't already exist.
     */
    private function addIndexSafely(string $table, array|string $columns, string $indexName): void
    {
        $existing = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (empty($existing)) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
                $tableBlueprint->index($columns, $indexName);
            });
        }
    }

    /**
     * Helper to safely drop an index if it exists.
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        $existing = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (!empty($existing)) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
                $tableBlueprint->dropIndex($indexName);
            });
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Products
        $this->addIndexSafely('products', ['status', 'seller_id'], 'idx_products_status_seller');
        $this->addIndexSafely('products', 'category_id', 'idx_products_category');

        // 2. Pickup Stores
        $this->addIndexSafely('pickup_stores', ['owner_id', 'status'], 'idx_pickup_stores_owner_status');

        // 3. Seller Profiles
        $this->addIndexSafely('seller_profiles', ['user_id', 'kyc_status'], 'idx_seller_profiles_user_kyc');
        $this->addIndexSafely('seller_profiles', 'parent_id', 'idx_seller_profiles_parent');

        // 4. Pickup Store Delivery Areas (Critical for serviceability checks)
        $this->addIndexSafely('pickup_store_delivery_areas', ['pickup_store_id', 'status', 'coverage_type'], 'idx_psda_store_status_cov');
        $this->addIndexSafely('pickup_store_delivery_areas', 'pincode', 'idx_psda_pincode');
        $this->addIndexSafely('pickup_store_delivery_areas', 'state_id', 'idx_psda_state');
        $this->addIndexSafely('pickup_store_delivery_areas', 'district_id', 'idx_psda_district');

        // 5. Notifications
        $this->addIndexSafely('notifications', ['recipient_type', 'is_read', 'created_at'], 'idx_notif_recip_read');

        // 6. Purchase Orders
        $this->addIndexSafely('purchase_orders', 'status', 'idx_po_status');
        $this->addIndexSafely('purchase_orders', 'payment_status', 'idx_po_payment_status');
        $this->addIndexSafely('purchase_orders', 'seller_payment_status', 'idx_po_seller_pay_status');
        $this->addIndexSafely('purchase_orders', 'seller_id', 'idx_po_seller_id');
        $this->addIndexSafely('purchase_orders', 'ddc_id', 'idx_po_ddc_id');
        $this->addIndexSafely('purchase_orders', 'created_at', 'idx_po_created_at');

        // 7. Pincodes
        $this->addIndexSafely('pincodes', 'pincode', 'idx_pincodes_pincode');
        $this->addIndexSafely('pincodes', 'state_id', 'idx_pincodes_state');
        $this->addIndexSafely('pincodes', 'district_id', 'idx_pincodes_district');

        // 8. Users
        $this->addIndexSafely('users', 'email', 'idx_users_email');
        $this->addIndexSafely('users', ['role', 'status'], 'idx_users_role_status');
        $this->addIndexSafely('users', 'mobile', 'idx_users_mobile');

        // 9. ADC Inventories
        $this->addIndexSafely('adc_inventories', ['adc_id', 'pos_status'], 'idx_adc_inv_adc_pos');
        $this->addIndexSafely('adc_inventories', 'product_id', 'idx_adc_inv_product_id');
        $this->addIndexSafely('adc_inventories', 'barcode', 'idx_adc_inv_barcode');

        // 10. ADC Local Products
        $this->addIndexSafely('adc_local_products', ['adc_id', 'status'], 'idx_adc_lp_adc_status');
        $this->addIndexSafely('adc_local_products', 'barcode', 'idx_adc_lp_barcode');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('products', 'idx_products_status_seller');
        $this->dropIndexSafely('products', 'idx_products_category');
        $this->dropIndexSafely('pickup_stores', 'idx_pickup_stores_owner_status');
        $this->dropIndexSafely('seller_profiles', 'idx_seller_profiles_user_kyc');
        $this->dropIndexSafely('seller_profiles', 'idx_seller_profiles_parent');
        $this->dropIndexSafely('pickup_store_delivery_areas', 'idx_psda_store_status_cov');
        $this->dropIndexSafely('pickup_store_delivery_areas', 'idx_psda_pincode');
        $this->dropIndexSafely('pickup_store_delivery_areas', 'idx_psda_state');
        $this->dropIndexSafely('pickup_store_delivery_areas', 'idx_psda_district');
        $this->dropIndexSafely('notifications', 'idx_notif_recip_read');
        $this->dropIndexSafely('purchase_orders', 'idx_po_status');
        $this->dropIndexSafely('purchase_orders', 'idx_po_payment_status');
        $this->dropIndexSafely('purchase_orders', 'idx_po_seller_pay_status');
        $this->dropIndexSafely('purchase_orders', 'idx_po_seller_id');
        $this->dropIndexSafely('purchase_orders', 'idx_po_ddc_id');
        $this->dropIndexSafely('purchase_orders', 'idx_po_created_at');
        $this->dropIndexSafely('pincodes', 'idx_pincodes_pincode');
        $this->dropIndexSafely('pincodes', 'idx_pincodes_state');
        $this->dropIndexSafely('pincodes', 'idx_pincodes_district');
        $this->dropIndexSafely('users', 'idx_users_email');
        $this->dropIndexSafely('users', 'idx_users_role_status');
        $this->dropIndexSafely('users', 'idx_users_mobile');
        $this->dropIndexSafely('adc_inventories', 'idx_adc_inv_adc_pos');
        $this->dropIndexSafely('adc_inventories', 'idx_adc_inv_product_id');
        $this->dropIndexSafely('adc_inventories', 'idx_adc_inv_barcode');
        $this->dropIndexSafely('adc_local_products', 'idx_adc_lp_adc_status');
        $this->dropIndexSafely('adc_local_products', 'idx_adc_lp_barcode');
    }
};
