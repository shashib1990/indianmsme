# Shared Services

This document details the shared services layer and integration with external platforms.

---

## 1. System Settings Config Resolver

All application-wide rules, margin percentages, and metadata switches are persisted in the `system_settings` database table.

*   **Model**: `App\Models\SystemSetting`
*   **Method**: `public static function getVal(string $key, $default = null)`
*   **Behavior**: Caches and returns system configurations.
*   **Core Configuration Keys**:
    *   `admin_margin_percentage`: Defaults to `8.0` (%).
    *   `ddc_margin_percentage`: Defaults to `5.0` (%).
    *   `mbp_percentage`: Defaults to `2.0` (%). Used to fund the customer MBP coin cashback program.

---

## 2. Pricing and Calculations Engine

The central engine for processing catalog item rates is encapsulated in the `PricingService` class (`app/Services/PricingService.php`).

*   **Operation Flow**:
    1.  **Base Cost**: Strips GST from the seller's initial price: `sellerPriceInclGst / (1 + (gstRate / 100))`.
    2.  **Company GST**: Computes difference: `sellerPriceInclGst - baseCost`.
    3.  **Margins**: Computes flat percentages on `baseCost` for Admin and DDC.
    4.  **Taxable Price**: `baseCost + adminMargin + ddcMargin`.
    5.  **DDC/ADC purchase prices**: Computes compound margins and reapplies the standard tax rate.
    6.  **MBP Allocation**: Divides a 2.0% coin pool into 90% and 10% shares. The 90% share is further split: 38% for the advisor and 62% for the customer.

---

## 3. External API Integrations

The system integrates with remote platforms using Guzzle/Http wrappers:

### AMA Codes Proxy
*   **Class**: `App\Http\Controllers\Controller`
*   **Method**: `getAmaCodes`
*   **API Target**: `https://abhhyamindia.com/api/get_ama_code`
*   **Behavior**: Executes HTTP GET request with a 10-second timeout. Logs non-200 responses and errors, returning a clean JSON array of active AMA coordinator codes. Used during ADC and customer registration.
