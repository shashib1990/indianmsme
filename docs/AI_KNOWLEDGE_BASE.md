# AI Knowledge Base

Welcome to the **Indian MSME** Project Knowledge Base. This directory contains detailed system architecture, business process workflows, database structures, API structures, reusable components, common functions, shared services, coding rules, dependency maps, and project workflows.

## Master Document Directory

Below is the map of all documentation files created inside the `/docs/` directory of the project workspace. Use this as a map to locate information about specific modules or processes.

| Document | Purpose / Content |
|---|---|
| [PROJECT_ARCHITECTURE.md](file:///var/www/html/Indian%20MSME/docs/PROJECT_ARCHITECTURE.md) | High-level system overview, multi-tenant guard structure, panel domains, routing configuration, directory hierarchy. |
| [REUSABLE_COMPONENTS.md](file:///var/www/html/Indian%20MSME/docs/REUSABLE_COMPONENTS.md) | Standard repository of all helper classes, services, traits, and common blade modules. |
| [BUSINESS_PROCESSES.md](file:///var/www/html/Indian%20MSME/docs/BUSINESS_PROCESSES.md) | Detailed documentation of roles (Admin, Seller, DDC, ADC, Pickup Store), order processing, inventory rules, and financial calculations. |
| [DATABASE_STRUCTURE.md](file:///var/www/html/Indian%20MSME/docs/DATABASE_STRUCTURE.md) | Table-by-table schema overview, primary keys, relationships, indexes, and custom data types. |
| [API_STRUCTURE.md](file:///var/www/html/Indian%20MSME/docs/API_STRUCTURE.md) | Public and panel APIs, authentication mechanisms, proxy routes, request/response formats. |
| [CODING_RULES.md](file:///var/www/html/Indian%20MSME/docs/CODING_RULES.md) | Development standards, coding patterns, production safety rules, validation rules, and error handling policies. |
| [DEPENDENCY_MAP.md](file:///var/www/html/Indian%20MSME/docs/DEPENDENCY_MAP.md) | System module dependencies, import paths, and trace maps for inventories, checkout, and orders. |
| [COMMON_FUNCTIONS.md](file:///var/www/html/Indian%20MSME/docs/COMMON_FUNCTIONS.md) | Reference list of common helper and utility functions like file uploads, number-to-words conversion, and pincode checking. |
| [SHARED_SERVICES.md](file:///var/www/html/Indian%20MSME/docs/SHARED_SERVICES.md) | Deep dive into shared core services (e.g. `PricingService`, external APIs integrations). |
| [PROJECT_WORKFLOW.md](file:///var/www/html/Indian%20MSME/docs/PROJECT_WORKFLOW.md) | End-to-end lifecycle guides for key actions (e.g. DDC Manual Subscription, Product Listing & Code Generation, Purchase Orders). |

---

## Architectural Guard & Panel Map

The project implements a Multi-Auth guard structure on top of a single database, configured in `config/auth.php`. Different user roles map to specific models, guards, and subdomains/subpaths:

*   **Admin Panel**
    *   **Guard**: `admin`
    *   **Model**: `App\Models\Admin` (points to `users` table, role filter)
    *   **Routes**: `/routes/admin.php`
*   **Vendor/Seller Panel**
    *   **Guard**: `vendor`
    *   **Model**: `App\Models\Vendor` (points to `users` table, role filter)
    *   **Routes**: `/routes/vendor.php`
*   **DDC Panel (District Distribution & Display Centre)**
    *   **Guard**: `ddc`
    *   **Model**: `App\Models\DDC` (points to `users` table, role filter)
    *   **Routes**: `/routes/ddc.php`
*   **ADC Panel (Area Development Coordinator)**
    *   **Guard**: `adc`
    *   **Model**: `App\Models\ADC` (points to `users` table, role filter)
    *   **Routes**: `/routes/adc.php`
*   **Pickup Store Panel**
    *   **Guard**: `pickup_store`
    *   **Model**: `App\Models\PickupStore` (points to `pickup_stores` table)
    *   **Routes**: `/routes/pickup_store.php`
*   **Customer / Front Store**
    *   **Guard**: `web`
    *   **Model**: `App\Models\User`
    *   **Routes**: `/routes/web.php`

---

## Key Core Constraints

1.  **Read-Only Integrity**: Never modify any core tables or logic without executing impact analysis.
2.  **Profile Synchronization**: The `User` model features dynamic event hooks (`boot` / `saving` / `saved` / `created`) that automatically direct profile fields (such as `company_name`, `gst_number`, etc.) to the respective profile relation (`SellerProfile`, `DdcProfile`, `AdcProfile`, etc.). When writing mock data or updating records, always update the `User` attributes or the specific profile model.
3.  **Pricing Consistency**: All pricing computations MUST route through `App\Services\PricingService::calculate`. Do not write manual math or roundings for margins, GST, and purchase prices.
