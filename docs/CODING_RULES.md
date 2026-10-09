# Coding Rules

All development on the **Indian MSME** project must strictly adhere to the standards, safety practices, and architectural rules outlined in this document.

---

## 1. Extension Over Replacement

*   **No Code Removals**: Never delete, rename, or modify existing database tables, column names, model relationships, controller methods, or routes unless explicitly instructed. Doing so will break backward compatibility.
*   **Extend Controllers**: When adding new functionality, write dedicated, isolated methods or classes. Do not overwrite or inline code inside existing, tested workflows unless refactoring has been explicitly requested.
*   **Database Migrations**:
    *   Do not modify existing migration files directly if they have already run in production. Always write a new migration file to alter tables, add indexes, or introduce new columns.
    *   Use clear, descriptive snake_case names for migration files.

---

## 2. Reusability Commandments

*   **File Uploads**: All file upload operations **MUST** route through the base controller's `uploadFileToService` helper. Do not write local file storage scripts or invoke raw AWS S3/FTP endpoints directly in panels.
*   **Pricing Calculations**: All computations related to margins, base costs, GST distributions, and MRP splits **MUST** invoke `App\Services\PricingService::calculate`. Do not write manual math formulas or execute rounding operations in controllers or blade templates.
*   **System Settings**: Configuration values, commission rules, and margins must be loaded via `SystemSetting::getVal('key', 'default_value')` instead of hardcoding values.

---

## 3. Profile-Model Sync Constraint

*   The `User` model manages multiple related profiles (`SellerProfile`, `DdcProfile`, `AdcProfile`) dynamically through model hooks.
*   When executing database operations or writing tests, update the attributes directly on the `User` object (which forwards them to the profile) or retrieve the relation explicitly.
*   Never assume that creating a `User` model requires a manual profile creation query, as `boot()` handles `firstOrCreate([])` automatically.

---

## 4. Production Safety Rules

*   **Data Integrity Transactions**: Any operation involving multiple tables (e.g. updating a Purchase Order, stocking in inventory, and updating wallet balances) **MUST** run within a DB Transaction block:
    ```php
    DB::beginTransaction();
    try {
        // execute operations
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    ```
*   **Guard Separation**: The application is highly separated by guard middleware. Ensure authentication checks match the correct panel:
    *   Admin routes: `auth:admin` middleware.
    *   Vendor routes: `auth:vendor` middleware.
    *   DDC routes: `auth:ddc` middleware.
    *   ADC routes: `auth:adc` middleware.
*   **Validation Rules**: Always validate incoming HTTP inputs strictly. Enforce file types and limits (e.g., max 5MB for documents/slips) to ensure upload microservice success.
