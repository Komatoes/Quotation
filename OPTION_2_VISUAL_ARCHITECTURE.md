# 🎨 Option 2 Visual Architecture

## Database Structure

```
┌─────────────────────────────────────────────────────────────┐
│                    PARENT QUOTATION                         │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Quotation ID: 100                                    │   │
│  │ Subject: "Kitchen Renovation"                        │   │
│  │ Client ID: 5 (John Doe)                             │   │
│  │ Status ID: 2 (Approved)                             │   │
│  │ Labor Fee: $500                                      │   │
│  │ Delivery Fee: $100                                   │   │
│  │ Contract Subject: "Renovation Agreement"             │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                    │
│                     (hasMany)                                │
│                          ↓                                    │
└─────────────────────────────────────────────────────────────┘
                            │
                ┌───────────┼───────────┐
                ↓           ↓           ↓
    ┌─────────────────┐ ┌─────────────────┐
    │  ADDITIONAL #1  │ │  ADDITIONAL #2  │
    │  ┌───────────┐  │ │  ┌───────────┐  │
    │  │ID: 1001   │  │ │  │ID: 1002   │  │
    │  │Subject:   │  │ │  │Subject:   │  │
    │  │"Fixtures" │  │ │  │"Labor"    │  │
    │  │Progress:40%  │ │  │Progress:60%  │
    │  └───────────┘  │ │  └───────────┘  │
    │        ↓        │ │        ↓        │
    │  Materials:     │ │  Materials:     │
    │  ├─ Handles     │ │  ├─ Hours       │
    │  └─ Hinges      │ │  └─ (additional)
    │                 │ │                 │
    │  Status:        │ │  Status:        │
    │  [INHERITED]    │ │  [INHERITED]    │
    │  Approved ✓     │ │  Approved ✓     │
    │                 │ │                 │
    │  Client:        │ │  Client:        │
    │  [INHERITED]    │ │  [INHERITED]    │
    │  John Doe       │ │  John Doe       │
    │                 │ │                 │
    │  Fees:          │ │  Fees:          │
    │  [INHERITED]    │ │  [INHERITED]    │
    │  Labor: $500    │ │  Labor: $500    │
    │  Delivery: $100 │ │  Delivery: $100 │
    └─────────────────┘ └─────────────────┘
           │                    │
           ↓                    ↓
    ┌──────────────────┐ ┌──────────────────┐
    │  MATERIALS #1    │ │  MATERIALS #2    │
    │  ┌────────────┐  │ │  ┌────────────┐  │
    │  │ Handles    │  │ │  │ Hours      │  │
    │  │ 20 × $5=$100 │  │ │  │ 20 × $20=$400
    │  ├────────────┤  │ │  └────────────┘  │
    │  │ Hinges     │  │ │                  │
    │  │ 10 × $8=$80 │  │ │  SUBTOTAL:      │
    │  └────────────┘  │ │  $400            │
    │                  │ │                  │
    │  SUBTOTAL:       │ │                  │
    │  $180            │ │                  │
    └──────────────────┘ └──────────────────┘
```

---

## Modal Display

```
┌──────────────────────────────────────────────────────────┐
│  📋 Additional Quotations                           [X]   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  ┌────────────────────────────────────────────────────┐  │
│  │  Fixtures                              [APPROVED]  │  │ ← Status inherited
│  │  ID: 1001                                          │  │   from parent
│  ├────────────────────────────────────────────────────┤  │
│  │  Fixtures for kitchen renovation work              │  │ ← Unique description
│  │  Created: Dec 05, 2025                             │  │
│  │  Materials: 2 items ($180.00)                      │  │
│  │  Progress: 40%                                      │  │
│  │                                                    │  │
│  │  [👁️ View/Edit]  [➕ Add Materials]               │  │
│  └────────────────────────────────────────────────────┘  │
│                                                          │
│  ┌────────────────────────────────────────────────────┐  │
│  │  Labor                                 [APPROVED]  │  │
│  │  ID: 1002                                          │  │
│  ├────────────────────────────────────────────────────┤  │
│  │  Installation and labor services                    │  │
│  │  Created: Dec 05, 2025                             │  │
│  │  Materials: 1 item ($400.00)                       │  │
│  │  Progress: 60%                                      │  │
│  │                                                    │  │
│  │  [👁️ View/Edit]  [➕ Add Materials]               │  │
│  └────────────────────────────────────────────────────┘  │
│                                                          │
├──────────────────────────────────────────────────────────┤
│                                  [Close]                 │
└──────────────────────────────────────────────────────────┘
```

---

## Data Flow - Create Additional Quotation

```
┌─────────────────────────────┐
│   User Click "Create"       │
└──────────────┬──────────────┘
               ↓
┌─────────────────────────────┐
│  Modal Form Opens           │
│  (Subject, Description)     │
└──────────────┬──────────────┘
               ↓
┌─────────────────────────────┐
│  User Fills Form            │
│  & Clicks "Create"          │
└──────────────┬──────────────┘
               ↓
┌─────────────────────────────┐
│  POST /quotations/{id}/     │
│  additional-quotation/store │
└──────────────┬──────────────┘
               ↓
┌──────────────────────────────────────┐
│ QuotationController::storeAdditional │
│  ✓ Validate input                    │
│  ✓ Check authorization               │
│  ✓ Create AdditionalQuotation        │
│  ✓ Log action                        │
└──────────────┬───────────────────────┘
               ↓
┌──────────────────────────────────────┐
│  Return JSON with:                   │
│  - additional_quotation_id           │
│  - success: true                     │
│  - message: "Created successfully!"  │
└──────────────┬───────────────────────┘
               ↓
┌──────────────────────────────────────┐
│  Frontend:                           │
│  ✓ Show success message              │
│  ✓ Dismiss modal                     │
│  ✓ (Optional) Redirect to add        │
│    materials page                    │
└──────────────────────────────────────┘
```

---

## Data Flow - View Additional Quotations

```
┌──────────────────────────────┐
│  User Click "View"           │
└──────────────┬───────────────┘
               ↓
┌──────────────────────────────┐
│  Modal Opens                 │
│  With Loading State          │
└──────────────┬───────────────┘
               ↓
┌─────────────────────────────────────┐
│  GET /quotations/{id}/              │
│  additional-quotations-json         │
└──────────────┬──────────────────────┘
               ↓
┌────────────────────────────────────────────┐
│ QuotationController::getAdditionalQuotations│
│  ✓ Check authorization                     │
│  ✓ Find parent quotation                   │
│  ✓ Load additionalQuotations()             │
│  ✓ Eager load materials.material           │
│  ✓ Order by created_at DESC                │
│  ✓ Map to JSON with inherited status       │
│  ✓ Calculate material totals               │
└──────────────┬───────────────────────────┘
               ↓
┌────────────────────────────────────┐
│  Return JSON:                      │
│  {                                 │
│    success: true,                  │
│    quotations: [                   │
│      {                             │
│        id, subject, description,   │
│        progress,                   │
│        status_name (inherited),    │
│        created_date,               │
│        materials_count,            │
│        material_total              │
│      }, ...                        │
│    ],                              │
│    total: count                    │
│  }                                 │
└──────────────┬────────────────────┘
               ↓
┌────────────────────────────────────┐
│  JavaScript:                       │
│  ✓ Receive JSON                    │
│  ✓ Create quotation cards          │
│  ✓ Set status badge color          │
│  ✓ Format materials & totals       │
│  ✓ Render in modal                 │
└──────────────┬────────────────────┘
               ↓
┌────────────────────────────────────┐
│  User See:                         │
│  ✓ List of additional quotations   │
│  ✓ Status badges (inherited)       │
│  ✓ Materials counts                │
│  ✓ Action buttons                  │
│  ✓ Can click View/Edit or Add Mats │
└────────────────────────────────────┘
```

---

## Inheritance Pattern

```
Parent Quotation Properties
├─ ✓ Client (inherited)
├─ ✓ Status (inherited)
├─ ✓ Labor Fee (inherited, applied ONCE)
├─ ✓ Delivery Fee (inherited, applied ONCE)
├─ ✓ Contract Subject (inherited)
├─ ✓ Project Start Date (inherited)
├─ ✓ Project End Date (inherited)
├─ ✓ With Contract Flag (inherited)
│
│  ADDITIONAL QUOTATION (Child)
│  ├─ 🔒 Status [INHERITED FROM PARENT]
│  ├─ 🔒 Client [INHERITED FROM PARENT]
│  ├─ 🔒 Labor Fee [INHERITED FROM PARENT]
│  ├─ 🔒 Delivery Fee [INHERITED FROM PARENT]
│  ├─ 🔒 Contract [INHERITED FROM PARENT]
│  │
│  └─ ✏️  UNIQUE FIELDS:
│     ├─ Subject (own subject)
│     ├─ Description (own description)
│     ├─ Materials (own materials table)
│     └─ Progress (own progress tracking)
```

---

## Key Relationships Diagram

```
quotations (Parent)
    │
    │ 1:N (One-to-Many)
    │
    └─→ additional_quotations (Children)
            │
            │ 1:N (One-to-Many)
            │
            └─→ additional_quotation_materials

quotations
    │
    │ N:N (Many-to-Many via quotation_materials)
    │
    └─→ materials

additional_quotations
    │
    │ N:N (Many-to-Many via additional_quotation_materials)
    │
    └─→ materials
```

---

## Status Badge Colors (Inherited from Parent)

```
Parent Status     Child Status Badge
────────────────────────────────────
Draft             [Draft] - Gray
Pending           [Pending] - Yellow
Approved          [Approved] - Green ✅
Rejected          [Rejected] - Red ❌
Completed         [Completed] - Green ✅
Ongoing           [Ongoing] - Blue

NOTE: All children show parent's status
      Status cannot be changed per child
      Status changes at parent level affect all children
```

---

## File Organization

```
app/
├─ Models/
│  ├─ AdditionalQuotation.php ✨ (NEW)
│  ├─ AdditionalQuotationMaterial.php ✨ (NEW)
│  ├─ Quotation.php (ENHANCED +100 lines)
│  └─ ...
│
├─ Http/
│  └─ Controllers/
│     ├─ QuotationController.php (ENHANCED)
│     └─ ...
│
database/
├─ migrations/
│  ├─ 2025_12_06_000000_create_additional_quotations_table.php ✨
│  ├─ 2025_12_06_000001_create_additional_quotation_materials_table.php ✨
│  └─ ...
│
resources/
└─ views/
   └─ view-report.blade.php (UNCHANGED - already works!)

Documentation/
├─ OPTION_2_IMPLEMENTATION.md (Complete guide)
├─ OPTION_2_QUICK_START.md (Quick reference)
├─ OPTION_2_STATUS.md (Status)
└─ OPTION_2_VISUAL_ARCHITECTURE.md (This file!)
```

---

## Summary

```
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║   Option 2: True Nested Additional Quotations             ║
║                                                            ║
║   ✓ Separate tables for semantic clarity                   ║
║   ✓ Inherited properties (single source of truth)          ║
║   ✓ Own materials per quotation                           ║
║   ✓ Status shows once (not repeated)                       ║
║   ✓ Fees applied once (not multiplied)                     ║
║   ✓ Cascading deletes for integrity                        ║
║   ✓ Eager loading prevents N+1 queries                     ║
║   ✓ Modal shows as "attached" components                   ║
║   ✓ True nesting feel and behavior                         ║
║   ✓ User-friendly UX                                       ║
║                                                            ║
║   Ready for: Testing & Deployment ✨                       ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
```

---

**Created:** December 6, 2025  
**Status:** ✅ COMPLETE  
**Ready:** Testing & Deployment  
