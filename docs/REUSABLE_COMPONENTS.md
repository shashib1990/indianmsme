# Reusable Components

This document catalogues all reusable services, helpers, traits, global utilities, and custom middlewares in the project.

---

## 1. PricingService

*   **Name**: `PricingService`
*   **Purpose**: Centralized tax, pricing, and margin calculations engine for all marketplace products.
*   **Location**: `app/Services/PricingService.php`
*   **Methods**:
    *   `public static function calculate(float $sellerPriceInclGst, float $gstRate, float $mrp, ?float $adminMarginPercent = null, ?float $ddcMarginPercent = null, ?float $mbpPercent = null): array`
*   **Input**:
    *   `$sellerPriceInclGst` (float): The base seller price including GST.
    *   `$gstRate` (float): Tax percentage (e.g. 5, 12, 18, 28).
    *   `$mrp` (float): Maximum Retail Price.
    *   `$adminMarginPercent` (float|null): Optional override for admin margin. Defaults to system setting `admin_margin_percentage` (fallback 8.0).
    *   `$ddcMarginPercent` (float|null): Optional override for DDC margin. Defaults to system setting `ddc_margin_percentage` (fallback 5.0).
    *   `$mbpPercent` (float|null): Optional override for MBP coin percentage. Defaults to system setting `mbp_percentage` (fallback 2.0).
*   **Output**: An associative array containing:
    *   `mrp`, `seller_price_incl_gst`, `gst_rate`
    *   `base_cost`: Price before taxes (excluding GST) = `sellerPriceInclGst / (1 + (gstRate / 100))`
    *   `company_gst_amount`: GST collected from seller = `sellerPriceInclGst - baseCost`
    *   `admin_margin_amount`: Admin profit = `baseCost * (adminMarginPercent / 100)`
    *   `ddc_margin_amount`: DDC profit = `(baseCost + adminMarginAmount) * (ddcMarginPercent / 100)` (Calculated on DDC Purchase Price without GST)
    *   `price_before_gst`: Base value for DDC to ADC purchase = `ddcPurchasePriceWithoutGst + ddcMarginAmount`
    *   `seller_gst_amount`: Tax on the new price before GST = `priceBeforeGst * (gstRate / 100)`
    *   `adc_purchase_price`: Final price charged to ADC = `priceBeforeGst + sellerGst`
    *   `ddc_purchase_price`: Final price charged to DDC = `(baseCost + adminMarginAmount) * (1 + (gstRate / 100))`
    *   `mbp_amount`: Total MBP coin pool = `baseCost * (mbpPercent / 100)`
    *   `mbp_90_share`: 90% of total MBP amount.
    *   `mbp_10_share`: 10% of total MBP amount.
    *   `mbp_38_share`: 38% of the 90% share.
    *   `mbp_62_share`: 62% of the 90% share.
    *   `available_margin`: `mrp - adcPurchasePrice`
    *   `adc_final_margin`: Available margin minus the MBP amount.
*   **Dependencies**: `App\Models\SystemSetting` (for settings lookup)
*   **Used By**:
    *   `App\Http\Controllers\Vendor\ProductController` (during product creation/edit)
    *   `App\Http\Controllers\Admin\ProductApprovalController` (during review & status changes)
*   **Example Usage**:
    ```php
    use App\Services\PricingService;

    $priceMatrix = PricingService::calculate(100.0, 18.0, 150.0);
    $adcPrice = $priceMatrix['adc_purchase_price'];
    ```

---

## 2. File Upload Function (Base Controller)

*   **Name**: `uploadFileToService`
*   **Purpose**: Standard microservice-backed file upload helper with automated fallback to local public storage if the microservice fails.
*   **Location**: `app/Http/Controllers/Controller.php` (Inherited by all controllers)
*   **Methods**:
    *   `public function uploadFileToService($file, string $folderName): string`
*   **Input**:
    *   `$file` (`Illuminate\Http\UploadedFile`): Uploaded file object.
    *   `$folderName` (string): Destination subfolder (e.g. `kyc`, `invoices`, `ddc_subscriptions`).
*   **Output**: string (The URL path to the stored file).
*   **Dependencies**: External upload API endpoint (`https://upload.sainikcanteen.com/api/upload`), environment keys `FILE_SERVICE_PROJECT_KEY` and `FILE_SERVICE_SECRET_KEY`.
*   **Used By**:
    *   `App\Http\Controllers\Vendor\ProfileController` (KYC file uploads, DDC slips)
    *   `App\Http\Controllers\InvoiceController` (transporter invoices)
    *   `App\Http\Controllers\DDC\WalletController` (deposit receipts)
*   **Example Usage**:
    ```php
    $url = $this->uploadFileToService($request->file('avatar'), 'profiles');
    ```

---

## 3. Image Helper

*   **Name**: `ImageHelper`
*   **Purpose**: Helper to check, format, and optimize remote or local images, ensuring clean URLs.
*   **Location**: `app/Helpers/ImageHelper.php`
*   **Methods**:
    *   `public static function getImageUrl($path, $default = 'placeholder.png')`
*   **Input**:
    *   `$path` (string|null): Path to check.
    *   `$default` (string): Fallback path if path is missing.
*   **Output**: string (Absolute URL)
*   **Example Usage**:
    ```php
    $src = \App\Helpers\ImageHelper::getImageUrl($profile->profile_image);
    ```

---

## 4. Trackable Trait

*   **Name**: `Trackable`
*   **Purpose**: Automatically logs creation and modification logs on supporting tables.
*   **Location**: `app/Traits/Trackable.php`
*   **Used By**: `Product` model, `SellerProfile` model.

---

## 5. CheckPermission Middleware

*   **Name**: `CheckPermission`
*   **Purpose**: RBAC validation middleware for controller authorization.
*   **Location**: `app/Http/Middleware/CheckPermission.php`
*   **Input**: `Request`, `Closure`, `string $permission`
*   **Rules**:
    *   If user guard is `admin` and `is_super_admin === '1'`, bypass checking.
    *   If user guard is staff, verify `$user->hasPermission($permission)`.
    *   If user has `parent_id === null` (owner of the panel), allow access.
    *   Otherwise, abort with `403 Access Denied`.
