# Visual Flow: Additional Quotations Feature

## 1. CREATE FLOW

```
┌─────────────────────────────────────────────────────────────────┐
│ Parent Quotation Page (/quotations/{id} or /view-report/{id})   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ User clicks 
                              │ "Additional Quotation" button
                              ↓
                    ┌─────────────────────┐
                    │   Modal appears     │
                    │  - Subject input    │
                    │ - Description input │
                    └─────────────────────┘
                              │
                              │ User fills form
                              │ & clicks "Create"
                              ↓
              ┌───────────────────────────────────┐
              │ POST /additional-quotation        │
              │ Data: {                           │
              │   parent_quotation_id: 1,         │
              │   subject: "Extra Materials",     │
              │   description: "Need more stuff"  │
              │ }                                 │
              └───────────────────────────────────┘
                              │
                              ↓
            ┌─────────────────────────────────────┐
            │ QuotationController                 │
            │ @storeAdditionalQuotation()         │
            │                                     │
            │ AdditionalQuotation::create([...])  │
            └─────────────────────────────────────┘
                              │
                              ↓
        ┌──────────────────────────────────────────────┐
        │ Database: additional_quotations table        │
        │ ┌──────────────────────────────────────────┐ │
        │ │ id: 1                                    │ │
        │ │ parent_quotation_id: 1                   │ │
        │ │ subject: "Extra Materials"               │ │
        │ │ description: "Need more stuff"           │ │
        │ │ progress: 0 (draft)                      │ │
        │ │ labor_fee: 0                             │ │
        │ │ delivery_fee: 0                          │ │
        │ │ created_at: 2025-12-06 10:30:00         │ │
        │ └──────────────────────────────────────────┘ │
        └──────────────────────────────────────────────┘
                              │
                              ↓ Returns: {success: true, additional_quotation_id: 1}
                              │
                              ↓ JavaScript redirects
                              │
        ┌──────────────────────────────────────────────┐
        │ /additional-quotations/1/edit                │
        │ (NEW ROUTE & NEW TEMPLATE)                   │
        └──────────────────────────────────────────────┘
```

---

## 2. EDIT FLOW

```
┌─────────────────────────────────────────────────────────────────┐
│ additional-quotation.blade.php                                   │
│  - Shows parent quotation info                                   │
│  - Materials table (empty)                                       │
│  - Labor fee input: 0                                            │
│  - Delivery fee input: 0                                         │
│  - Buttons: [Add Material] [Save] [Approve & Attach] [Back]      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ User clicks "Add Material"
                              ↓
                    ┌─────────────────────────────┐
                    │ Modal: Add Material         │
                    │ - Select material dropdown  │
                    │ - Quantity input            │
                    │ [Cancel] [Add]              │
                    └─────────────────────────────┘
                              │
                              │ User selects Material & Qty
                              │ clicks "Add"
                              ↓
              ┌──────────────────────────────────────────┐
              │ POST /additional-quotations/1/materials  │
              │ Data: {                                  │
              │   material_id: 5,                        │
              │   quantity: 10                           │
              │ }                                        │
              └──────────────────────────────────────────┘
                              │
                              ↓
        ┌──────────────────────────────────────────────────┐
        │ Database: additional_quotation_materials table   │
        │ ┌────────────────────────────────────────────┐   │
        │ │ id: 1                                      │   │
        │ │ additional_quotation_id: 1                 │   │
        │ │ material_id: 5                             │   │
        │ │ quantity: 10                               │   │
        │ └────────────────────────────────────────────┘   │
        └──────────────────────────────────────────────────┘
                              │
                              ↓ Page reloads
                              │
        ┌──────────────────────────────────────────────┐
        │ Materials table updated:                     │
        │ ┌─────────────────────────────────────────┐  │
        │ │ Material  | Qty | Price/Unit | Total  │  │
        │ │ Material5 | 10  | $50        | $500   │  │
        │ │ [X remove]                              │  │
        │ └─────────────────────────────────────────┘  │
        │ Fees section:                                │
        │ Labor Fee: [0]       Delivery Fee: [0]       │
        │ Grand Total: $500                            │
        │ [Save] [Approve & Attach] [Back]             │
        └──────────────────────────────────────────────┘
                              │
                              │ User enters fees
                              │ labor_fee: 50
                              │ delivery_fee: 25
                              │ clicks "Save"
                              ↓
              ┌───────────────────────────────────────────┐
              │ POST /additional-quotations/1/update       │
              │ Data: {                                   │
              │   labor_fee: 50,                          │
              │   delivery_fee: 25                        │
              │ }                                         │
              └───────────────────────────────────────────┘
                              │
                              ↓
        ┌────────────────────────────────────────────────┐
        │ Database: additional_quotations table UPDATED  │
        │ ┌──────────────────────────────────────────┐  │
        │ │ id: 1                                    │  │
        │ │ labor_fee: 50 ← UPDATED                  │  │
        │ │ delivery_fee: 25 ← UPDATED               │  │
        │ └──────────────────────────────────────────┘  │
        │ Grand Total = $500 + $50 + $25 = $575        │
        └────────────────────────────────────────────────┘
                              │
                              ↓ Success message
                              │
        ┌────────────────────────────────────────────────┐
        │ "Changes saved"                                │
        │ Grand Total: $575                              │
        │ [Approve & Attach to Parent]                   │
        └────────────────────────────────────────────────┘
```

---

## 3. APPROVAL FLOW

```
┌────────────────────────────────────────────────────┐
│ User clicks "Approve & Attach to Parent"           │
└────────────────────────────────────────────────────┘
                      │
                      │ Confirmation dialog
                      ↓
        ┌──────────────────────────────────────┐
        │ "Approve & Attach?"                  │
        │ This will mark as approved and       │
        │ attach it to parent quotation.       │
        │ [Cancel] [Yes, approve]              │
        └──────────────────────────────────────┘
                      │
                      │ User clicks "Yes, approve"
                      ↓
        ┌──────────────────────────────────────────┐
        │ POST /additional-quotations/1/approve    │
        └──────────────────────────────────────────┘
                      │
                      ↓
    ┌────────────────────────────────────────────────┐
    │ Database: additional_quotations table UPDATED  │
    │ ┌──────────────────────────────────────────┐  │
    │ │ id: 1                                    │  │
    │ │ progress: 100 ← UPDATED (was 0)         │  │
    │ │ This means: "Approved & Attached"        │  │
    │ └──────────────────────────────────────────┘  │
    └────────────────────────────────────────────────┘
                      │
                      ↓ Success message & reload
                      │
    ┌────────────────────────────────────────────────┐
    │ "Approved and attached!"                       │
    │                                                │
    │ Page shows:                                    │
    │ Status Badge: ✓ Approved & Attached to Parent │
    │ [Approve & Attach] button is HIDDEN           │
    │ (approval button only shows when progress<100)│
    └────────────────────────────────────────────────┘
```

---

## 4. VIEW ADDITIONAL QUOTATIONS FLOW

```
┌──────────────────────────────────────────────────┐
│ Parent Quotation Page                            │
│ [View Additional Quotations] button               │
└──────────────────────────────────────────────────┘
                      │
                      │ Click button
                      ↓
        ┌────────────────────────────────────────┐
        │ GET /quotations/1/additional-quotations-json
        │ (API returns list of additional quotations)
        └────────────────────────────────────────┘
                      │
                      ↓
    ┌──────────────────────────────────────────────┐
    │ Modal: Additional Quotations                │
    │ ┌────────────────────────────────────────┐  │
    │ │ Additional Quotation #1                │  │
    │ │ Subject: "Extra Materials"             │  │
    │ │ Status: [In Progress badge]            │  │
    │ │ Description: Need more stuff           │  │
    │ │ Created: Dec 06, 2025                  │  │
    │ │ Materials: 1                           │  │
    │ │ [View/Edit] [Delete]                   │  │
    │ ├────────────────────────────────────────┤  │
    │ │ Additional Quotation #2                │  │
    │ │ Subject: "Additional Labor"            │  │
    │ │ Status: [✓ Approved badge]             │  │
    │ │ Description: Extra work needed         │  │
    │ │ Created: Dec 06, 2025                  │  │
    │ │ Materials: 0                           │  │
    │ │ [View/Edit] [Delete]                   │  │
    │ └────────────────────────────────────────┘  │
    │ [Close]                                     │
    └──────────────────────────────────────────────┘
                      │
                      │ Click [View/Edit]
                      ↓
        ┌────────────────────────────────────┐
        │ Redirect to:                       │
        │ /additional-quotations/1/edit      │
        │ or                                 │
        │ /additional-quotations/2/edit      │
        └────────────────────────────────────┘
```

---

## Database Relationships

```
quotations (parent table)
├─ id: 1
├─ subject: "Main Project"
└─ client_id: 5


additional_quotations (metadata for each additional item)
├─ id: 1
├─ parent_quotation_id: 1 ←──┐ Links to parent
├─ subject: "Extra Materials"│
├─ description: "..."        │
├─ progress: 100 (approved)  │
├─ labor_fee: 50             │
└─ delivery_fee: 25          │
        │
        │ has many materials (many-to-many)
        ↓
additional_quotation_materials (pivot table)
├─ id: 1
├─ additional_quotation_id: 1
├─ material_id: 5
└─ quantity: 10


materials
├─ id: 5
├─ name: "Concrete"
└─ unit_price: 50
```

---

## Status Indicators

```
Progress = 0-99     → Badge: ⏳ "In Progress"   (Blue/Yellow)
Progress = 100      → Badge: ✓ "Approved & Attached to Parent" (Green)
```

---

## Key Points

✅ Stored in SEPARATE TABLE (`additional_quotations`, not `quotations`)
✅ Materials linked via PIVOT TABLE (`additional_quotation_materials`)
✅ Each additional quotation has OWN FEES (labor_fee, delivery_fee)
✅ Progress field indicates approval status (0 = draft, 100 = approved)
✅ Edited in SEPARATE TEMPLATE (`additional-quotation.blade.php`)
✅ Accessed via SEPARATE ROUTE (`/additional-quotations/{id}/edit`)
✅ NOT independent quotations, just metadata attachments to parent

