# Common Functions

This document serves as a quick reference catalog of global helper and utility functions.

---

## 1. File Upload Service

*   **Location**: `app/Http/Controllers/Controller.php`
*   **Signature**: `public function uploadFileToService($file, string $folderName): string`
*   **Behavior**: Validates file size (max 5MB), POSTs to remote microservice, falls back to public storage directory if upload fails, and returns the absolute HTTP URL of the file.

---

## 2. Admin Notification Builder

*   **Location**: `app/Http/Controllers/Controller.php`
*   **Signature**: `public function notifyAdmins($title, $message, $link = null)`
*   **Behavior**: Inserts a new row in the `notifications` table with `is_read = false`, generating notifications for administrators.

---

## 3. Number-to-Words Parser

*   **Location**: `app/Http/Controllers/InvoiceController.php`
*   **Signature**: `protected function numberToWords($number): string`
*   **Behavior**: Converts integer numeric values to standard Indian currency word strings (e.g. `12345` -> `"Twelve Thousand Three Hundred Forty Five"`).

---

## 4. Next Invoice Serial Number Generator

*   **Location**: `app/Models/Invoice.php`
*   **Signature**: `public static function generateNextInvoiceNumber($date = null): array`
*   **Behavior**: Scans current financial year series (April 1st to March 31st), increments serial count, and returns details: `invoice_number`, `financial_year`, `serial_number`, `invoice_date`.

---

## 5. Pincode Serviceability Scope

*   **Location**: `app/Models/Product.php`
*   **Signature**: `public function scopeServiceableByPincode($query, $pincode)`
*   **Behavior**: Filters product queries to return only products whose sellers have active pickup stores delivering to the target state, district, or specific pincode.
