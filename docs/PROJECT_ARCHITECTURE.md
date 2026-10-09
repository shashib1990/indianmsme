# Project Architecture

This document describes the high-level architecture of the Indian MSME application.

## Directory Structure

```
app/
├── Casts/             # Custom Eloquent casts (e.g. EnumStringCast)
├── Console/           # Artisan console commands
├── Enums/             # PHP enums for status & roles (e.g. UserRole, ProductStatus)
├── Exceptions/        # Exception handlers
├── Exports/           # Excel/CSV exports (using Maatwebsite Excel)
├── Helpers/           # Global helpers (e.g. ImageHelper)
├── Http/
│   ├── Controllers/   # Grouped by namespace: Web, Admin, Vendor, DDC, ADC, Api
│   ├── Middleware/    # Auth guards, permission checks, password forcing, etc.
│   └── Requests/      # Form requests for validation
├── Imports/           # Bulk imports (e.g. Location imports)
├── Models/            # Database models (User, Product, PurchaseOrder, etc.)
├── Providers/         # Service Providers (AppServiceProvider, EventServiceProvider, etc.)
├── Services/          # Centralized business logic services (PricingService)
└── Traits/            # Shared PHP traits (Trackable)

routes/
├── web.php            # Storefront / Frontend public routes
├── admin.php          # Admin portal routes
├── vendor.php         # Seller / Vendor portal routes
├── ddc.php            # District Distribution Center routes
├── adc.php            # Area Development Coordinator routes
├── pickup_store.php   # Pickup store portal routes
└── api.php            # API endpoints
```

## Domains and Subdomains

The application uses subdomain routing for different panels. The domain structure config is mapped in `.env` and `config/app.php`:
*   **Admin Panel**: `admin.localhost` or `admin.indianmsme.org` (Route prefix: `admin`)
*   **Vendor Panel**: `vendor.localhost` or `vendor.indianmsme.org` (Route prefix: `vendor`)
*   **DDC Panel**: `ddc.localhost` or `ddc.indianmsme.org` (Route prefix: `ddc`)
*   **ADC Panel**: `adc.localhost` or `adc.indianmsme.org` (Route prefix: `adc`)
*   **Pickup Store Panel**: `pickup.localhost` (Route prefix: `pickup-stores`)

## Multi-Guard System

Laravel's standard auth configuration (`config/auth.php`) is configured with multiple guards using session drivers:

1.  **`admin` Guard**: Operates on the `admins` provider. Points to `App\Models\Admin` model, which uses the `users` table filtered by `role = 'admin'`.
2.  **`vendor` Guard**: Operates on the `vendors` provider. Points to `App\Models\Vendor` model, which uses the `users` table filtered by `role = 'seller'`.
3.  **`ddc` Guard**: Operates on the `ddcs` provider. Points to `App\Models\DDC` model, which uses the `users` table filtered by `role = 'ddc'`.
4.  **`adc` Guard**: Operates on the `adcs` provider. Points to `App\Models\ADC` model, which uses the `users` table filtered by `role = 'adc'`.
5.  **`pickup_store` Guard**: Operates on the `pickup_stores` provider. Points to `App\Models\PickupStore` model, which maps to `pickup_stores` table.
6.  **`web` Guard**: Operates on the `users` provider. Points to `App\Models\User` model. Used for customer logins on the main storefront.

## Key Middlewares

*   **`auth:{guard}`**: Authenticates requests against a specific guard.
*   **`role.seller` / `role.ddc` / `role.adc`**: Verifies that the authenticated user matches the appropriate user role.
*   **`force.password:{guard}`** (`ForcePasswordChange`): Checks if the authenticated user has `force_change_password` set to true. If so, they are restricted and redirected to `/profile/change-password` until they modify it.
*   **`CheckPermission`**: Standard permission system. If user is super admin (`is_super_admin === '1'`), they bypass permission gates. Otherwise, it checks `$user->hasPermission($permission)` or checks if they are the panel owner (`parent_id === null`).
*   **`SyncPincodeSession`**: Synces the visitor's pincode across sessions and requests to ensure catalog serviceability.

## Database & Model Layer Features

*   **User and Profile Event Hooks**: The `User` model overrides standard `boot` hooks to listen to `saving`, `saved`, and `created` events.
    *   During **`saving`**, it extracts profile fields (defined in `$profileFields` inside `User.php`) and caches them locally.
    *   During **`saved`** and **`created`**, it calls `profile()->firstOrCreate([])` and updates the related profile table with the cached profile fields.
    *   This enables standard mass-assignment on the `User` model, updating two tables simultaneously.
*   **Approved Global Scope**: The `Product` model has a global scope inside `booted()` that enforces catalog-wide visibility. By default, it restricts products to `status = 'Approved'` unless the request matches an administrative, vendor, or coordinator subdomain (`admin.*`, `vendor.*`, `ddc.*`, `adc.*`).
