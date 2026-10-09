# Dependency Map

This map outlines the coupling and import flows between system modules.

---

## 1. Core Authentication Dependencies

```
User (Base Model)
  └── Vendor (Extends User / Role: seller)
  └── Admin (Extends User / Role: admin)
  └── DDC (Extends User / Role: ddc)
  └── ADC (Extends User / Role: adc)
```

*   **Profiles coupling**: The `User` model is tightly coupled to profile classes:
    *   `AdminProfile`
    *   `SellerProfile`
    *   `DdcProfile`
    *   `AdcProfile`
*   **Role Mapping**: Guards configured in `config/auth.php` map directly to these models.

---

## 2. Order & Checkout Dependencies

```
[Storefront / DDC Cart]
        │
        ▼
[PurchaseOrder] ────(depends on)────► [User (DDC / Vendor)]
        │                                  │
        │ (hasMany)                        │ (hasOne)
        ▼                                  ▼
[PurchaseOrderItem] ──(hasOne)──► [SellerProfile]
        │
        ▼
[ddc_inventories] (Created during stock-in)
        │
        ▼
[Product] (Catalog Reference)
```

### Module Traces
1.  **DDC Checkout**:
    *   `DDC\PurchaseOrderController` -> references `DdcCartItem` -> references `Product` -> deductions made on `DdcProfile.wallet_balance`.
2.  **Order Processing / Dispatch**:
    *   `Vendor\PurchaseOrderController` -> processes `PurchaseOrder` -> updates `Invoice` -> fetches transporter info.
3.  **Stock Reception**:
    *   `DDC\PurchaseOrderController` -> triggers `stockInItem()` -> writes to `PurchaseOrderItem` -> inserts/updates `DdcInventory`.

---

## 3. Product Margin Dependencies

```
[SystemSetting] ──(defines margins)──► [PricingService]
                                             │
                                             ▼
                                     [Product / SubSellerProduct]
```

*   `PricingService` is a leaf utility class that imports `SystemSetting` but has no other internal database model dependencies. It is completely isolated and safe to call from any class.
