# Database Structure

This document details the database schema and model relationships.

---

## 1. Core Users and Profiles

The system uses a consolidated `users` table for all authentication, paired with role-specific profile tables for secondary details.

### `users` Table
*   `id` (BigInt, PK)
*   `name` (Varchar)
*   `email` (Varchar, Unique)
*   `mobile` (Varchar, Unique)
*   `password` (Varchar)
*   `role` (Enum: `admin`, `seller`, `vendor`, `ddc`, `adc`, `customer`)
*   `status` (Enum: `'0'`, `'1'`, Default `'0'`)
*   `remember_token` (Varchar)

### `seller_profiles` Table
*   `id` (BigInt, PK)
*   `user_id` (BigInt, FK -> `users.id`)
*   `seller_id` (Varchar, Unique)
*   `company_name` (Varchar)
*   `kyc_status` (Enum: `PENDING`, `APPROVED`, `REJECTED`)
*   `is_ddc_subscribed` (Boolean, Default `false`)
*   `ddc_subscription_status` (Enum: `none`, `pending`, `approved`, `rejected`)
*   `ddc_subscription_utr` (Varchar, Nullable)
*   `ddc_subscription_slip` (Varchar, Nullable)
*   `ddc_subscription_reject_reason` (Text, Nullable)
*   `ddc_subscription_submitted_at` (Timestamp, Nullable)

### `ddc_profiles` Table
*   `id` (BigInt, PK)
*   `user_id` (BigInt, FK -> `users.id`)
*   `ddc_code` (Varchar)
*   `state_id` (BigInt, FK -> `states.id`)
*   `district_id` (BigInt, FK -> `districts.id`)
*   `wallet_balance` (Decimal, 15,2)

### `adc_profiles` Table
*   `id` (BigInt, PK)
*   `user_id` (BigInt, FK -> `users.id`)
*   `adc_code` (Varchar)
*   `wallet_balance` (Decimal, 15,2)

---

## 2. Product Catalog

### `products` Table
*   `id` (BigInt, PK)
*   `seller_id` (BigInt, FK -> `users.id`)
*   `product_code` (Varchar, Unique)
*   `product_name` (Varchar)
*   `barcode` (Varchar)
*   `mrp` (Decimal, 10,2)
*   `gst_rate` (Decimal, 5,2)
*   `seller_price_incl_gst` (Decimal, 10,2)
*   `base_cost` (Decimal, 10,2)
*   `admin_margin_percentage` (Decimal, 5,2)
*   `ddc_margin_percentage` (Decimal, 5,2)
*   `adc_purchase_price` (Decimal, 10,2)
*   `ddc_purchase_price` (Decimal, 10,2)
*   `status` (Enum: `Draft`, `Pending`, `Approved`, `Rejected`, `Inactive`)

---

## 3. Order Management

### `purchase_orders` Table
*   `id` (BigInt, PK)
*   `ddc_id` (BigInt, FK -> `users.id`)
*   `seller_id` (BigInt, FK -> `users.id`)
*   `pickup_store_id` (BigInt, FK -> `pickup_stores.id`)
*   `status` (Enum: `Pending`, `Accepted`, `Rejected`, `Dispatched`, `Received`, `Completed`, `Cancelled`)
*   `payment_status` (Enum: `Unpaid`, `Paid`, `Failed`)
*   `grand_total` (Decimal, 15,2)

### `purchase_order_items` Table
*   `id` (BigInt, PK)
*   `purchase_order_id` (BigInt, FK -> `purchase_orders.id`)
*   `product_id` (BigInt, FK -> `products.id`)
*   `quantity` (Int)
*   `price` (Decimal, 10,2)
*   `total` (Decimal, 10,2)
*   `status` (Enum: `Pending`, `Accepted`, `Rejected`)
*   `is_stocked_in` (Boolean, Default `false`)
*   `received_mrp` (Decimal, 10,2, Nullable)
*   `damage_qty` (Int, Default `0`)
*   `shortage_qty` (Int, Default `0`)
*   `excess_qty` (Int, Default `0`)
*   `mfg_date` (Date, Nullable)
*   `expire_date` (Date, Nullable)
*   `batch_number` (Varchar, Nullable)

### `invoices` Table
*   `id` (BigInt, PK)
*   `purchase_order_id` (BigInt, FK -> `purchase_orders.id`)
*   `type` (Varchar)
*   `invoice_number` (Varchar)
*   `invoice_date` (Date)
*   `invoice_file` (Varchar, Nullable)
*   `ddc_invoice_number` (Varchar, Nullable)
*   `ddc_invoice_file` (Varchar, Nullable)
*   `ddc_invoice_date` (Date, Nullable)
*   `bill_to_name` (Varchar)
*   `bill_to_address` (Text)
*   `bill_to_district` (Varchar, Nullable)
*   `bill_to_state` (Varchar, Nullable)
*   `bill_to_pincode` (Varchar, Nullable)
*   `bill_to_phone` (Varchar, Nullable)
*   `bill_to_gstin` (Varchar, Nullable)
*   `dispatch_from_name` (Varchar)
*   `dispatch_from_address` (Text)
*   `dispatch_from_district` (Varchar, Nullable)
*   `dispatch_from_state` (Varchar, Nullable)
*   `dispatch_from_pincode` (Varchar, Nullable)
*   `dispatch_from_phone` (Varchar, Nullable)
*   `dispatch_from_gstin` (Varchar, Nullable)

---

## 4. DDC Inventory

### `ddc_inventories` Table
*   `id` (BigInt, PK)
*   `ddc_id` (BigInt, FK -> `users.id`)
*   `product_id` (BigInt, FK -> `products.id`)
*   `product_name` (Varchar)
*   `product_code` (Varchar)
*   `barcode` (Varchar)
*   `quantity` (Int)
*   `mrp` (Decimal, 10,2)
*   `purchase_price` (Decimal, 10,2)
*   `batch_number` (Varchar)
*   `mfg_date` (Date)
*   `expire_date` (Date)
*   `purchase_order_id` (BigInt, FK -> `purchase_orders.id`)
*   `purchase_order_item_id` (BigInt, FK -> `purchase_order_items.id`)
