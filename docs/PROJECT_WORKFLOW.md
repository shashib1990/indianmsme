# Project Workflow

This document traces the step-by-step lifecycle of critical system workflows.

---

## 1. DDC Manual Subscription Process

1.  **Vendor Application**:
    *   Vendor navigates to "DDC Model Subscription" on their profile dashboard.
    *   Fills in the transaction reference (UTR) and uploads a payment slip image/PDF (validated up to 5MB).
    *   Profile status transitions from `none`/`rejected` to `pending`.
2.  **Admin Verification**:
    *   Admin reviews all incoming submissions directly from the new **DDC Requests** submenu list under "Manage Vendors" in the Admin Panel (without needing to click through each vendor profile).
    *   Admin verifies the UTR reference with bank statement logs.
    *   **If Valid**: Admin clicks "Approve". Status updates to `approved` and `is_ddc_subscribed` is set to `true`.
    *   **If Invalid**: Admin clicks "Reject" and enters a rejection reason (saved to `ddc_subscription_reject_reason`). Status updates to `rejected`. The Vendor can modify details and re-apply.
    *   **If Terminated**: Admin can view active subscriptions in the **DDC Approved** list and click "Cancel Subscription", restoring status to `none` and setting `is_ddc_subscribed` to `false`.

---

## 2. Product Approval Lifecycle

1.  **Vendor Creation**:
    *   Vendor creates a new product. A unique `product_code` is auto-generated inside the `creating` event using the seller sequence: `{CleanSellerId}{SequenceNumber}` (e.g. `1001000111`).
    *   Initial status is set to `Draft`.
2.  **Approval Submission**:
    *   Vendor clicks "Submit for Approval". Status changes to `Pending`.
3.  **Admin Review**:
    *   Admin reviews specs, MRP, GST rate, and seller prices.
    *   **If Approved**: Status changes to `Approved`. The product is now indexed in the storefront catalog (enforced by the global `approved` scope).
    *   **If Rejected**: Status changes to `Rejected`. Rejection notes are logged.
4.  **Vendor Management**:
    *   The Vendor can toggle the product's active status between `Approved` and `Inactive` at any point.

---

## 3. DDC Purchase Order & Inventory Stock In

1.  **Order Placement**:
    *   DDC adds products to their cart.
    *   DDC clicks "Checkout". The order total is deducted from the DDC's `wallet_balance`.
    *   Purchase Order is created in `Pending` state.
2.  **Vendor Acceptance**:
    *   Vendor receives the PO.
    *   Vendor clicks "Accept" for each item.
    *   Once items are accepted, Vendor packages the products and updates the order status to "Dispatched".
    *   This generates the first tax invoice record with a sequential number (e.g. `DL/26-27/0001`).
3.  **DDC Verification & Stock-In**:
    *   Once the shipment arrives, the DDC must upload their DDC invoice details (Invoice number + PDF file).
    *   DDC goes to the Stock-In form and updates the quantities for each item:
        *   `NetQty = Quantity - Damage - Shortage + Excess`.
    *   DDC enters the **Batch Number**, **Manufacturing Date**, and **Expiry Date** (Mandatory).
    *   When all accepted items are stocked in, the Purchase Order transitions to `Received`.

---

## 4. Smart Sales Incentive (SSI)

1.  **Subscription Requirement**:
    *   Only active, approved DDC-subscribed vendors (`is_ddc_subscribed = true`) have access to the **Smart Sales Incentive** section.
2.  **Incentive Setup**:
    *   Subscribed vendors access the "Smart Sales Incentive" section from their sidebar panel.
    *   The vendor enters a custom commission percentage (`ddc_ama_commission_percentage`) between `0` and `100` and submits the form.
    *   This commission percentage is used to reward Abhyham Marketing Advisors (AMA) on each confirmed marketplace order.

---

## 5. Regional Product Creation & Catalog Matching Workflow

1.  **Product Name Entry & Dynamic Search**:
    *   The DDC user enters the Product Name in the Regional Product Master creation form.
    *   An automated AJAX search queries both existing MSME Products (`products` table) and DDC Regional Products (`ddc_local_products` table).
2.  **Suggestion Selection**:
    *   If matching products are found, they are displayed with distinctive badges (`✓ MSME Product` / `✓ REGIONAL Product`).
    *   Selecting an existing MSME product automatically populates Category, HSN, Brand, UOM, and links `msme_product_id` to prevent duplicate entries.
3.  **New Regional Product Creation**:
    *   If no matching product is found, the user selects **"+ Create New Regional Product"**.
    *   The system generates an auto-incremented Regional Product Code in the format `LCLxxxxx` (e.g., `LCL000001`).
    *   This enables structured catalog management and powers future Website (MSME) vs Regional Product price comparisons.

