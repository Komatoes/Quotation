# 📊 View Additional Quotations - Visual Guide

## Feature Overview

```
┌─────────────────────────────────────────────────────────┐
│              PROJECT REPORT VIEW                        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Quotation #100: Main Project                          │
│  ┌───────────────────────────────────────────────────┐ │
│  │ Subject: Kitchen Renovation                       │ │
│  │ Customer: John Doe                                │ │
│  │ Status: Ongoing                                   │ │
│  │                                                   │ │
│  │ [Create] [View Add'l] ← NEW BUTTON                │ │
│  │                                                   │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
└─────────────────────────────────────────────────────────┘
                        ↓
                  Click Button
                        ↓
┌──────────────────────────────────────────────────────────────┐
│  📋 ADDITIONAL QUOTATIONS MODAL (NEW)                        │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Additional Materials Quotation      [Draft Badge]      │ │
│  │ ID: 101                                                │ │
│  │                                                        │ │
│  │ Description: Extra tiles and grout needed             │ │
│  │ Created: Dec 02, 2025                                 │ │
│  │ Materials: 5                                           │ │
│  │                                                        │ │
│  │ [View/Edit] [Project Report]                          │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Additional Labor Services          [Approved Badge]    │ │
│  │ ID: 102                                                │ │
│  │                                                        │ │
│  │ Description: Extra painting for accent wall           │ │
│  │ Created: Dec 03, 2025                                 │ │
│  │ Materials: 2                                           │ │
│  │                                                        │ │
│  │ [View/Edit] [Project Report]                          │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│                                         [Close]             │
└──────────────────────────────────────────────────────────────┘
```

---

## Button Placement

```
┌──────────────────────────────────────────────┐
│        PROJECT INFO CARD                     │
│                                              │
│  Subject: Kitchen Renovation                 │
│  Customer: John Doe                          │
│  Contact: 0987654321                         │
│  Address: 123 Main St                        │
│                                              │
│  [Generate Link]                             │
│  [Create Additional Quotation] (existing)    │
│  [View Additional Quotations] (NEW)          │
└──────────────────────────────────────────────┘
```

---

## Modal Card Layout

```
┌────────────────────────────────────────────────────────┐
│ Subject                              [Status Badge]    │
│ ID: 123                                                │
├────────────────────────────────────────────────────────┤
│                                                        │
│ Description: Some details about this quotation        │
│                                                        │
│ Created: Dec 06, 2025                                 │
│ Materials: 5                                           │
│                                                        │
│ [View/Edit Button] [Project Report Button]           │
│                                                        │
└────────────────────────────────────────────────────────┘
```

---

## Status Badge Colors

```
┌─────────────────────────────────┐
│ Status Badge Examples           │
├─────────────────────────────────┤
│ [Draft]       ← Gray background │
│ [Pending]     ← Yellow bg       │
│ [Approved]    ← Green bg        │
│ [Rejected]    ← Red background  │
│ [Completed]   ← Green bg        │
│ [Ongoing]     ← Blue background │
└─────────────────────────────────┘
```

---

## JavaScript Flow

```
┌─────────────────────────────────────────┐
│ VIEW ADDITIONAL QUOTATIONS BUTTON        │
├─────────────────────────────────────────┤
│                                         │
│ id="viewAdditionalQtnBtn"               │
│ data-parent-id="{{ quotation.id }}"     │
│                                         │
└──────────────┬──────────────────────────┘
               │
               ├─ addEventListener('click')
               │
               └─ Fetch /quotations/{id}/additional-quotations-json
                         │
                         ├─ Response: { success, quotations[], total }
                         │
                         ├─ Map quotations to card HTML
                         │
                         ├─ Insert into #additionalQuotationsList
                         │
                         └─ Show modal
```

---

## API Response Structure

```json
{
  "success": true,
  "quotations": [
    {
      "id": 101,
      "subject": "Additional Materials",
      "description": "Extra tiles needed",
      "status_name": "Draft",
      "created_at": "2025-12-06T10:30:00",
      "materials_count": 5,
      "labor_fee": 100.00,
      "delivery_fee": 50.00
    },
    {
      "id": 102,
      "subject": "Additional Labor",
      "description": "Extra painting",
      "status_name": "Approved",
      "created_at": "2025-12-07T14:15:00",
      "materials_count": 2,
      "labor_fee": 200.00,
      "delivery_fee": 0.00
    }
  ],
  "total": 2
}
```

---

## User Journey

```
1. OPEN PROJECT REPORT
   └─ View quotation #100 details

2. CLICK "VIEW ADDITIONAL QUOTATIONS"
   └─ Modal opens with loading state

3. FETCH DATA
   └─ /quotations/100/additional-quotations-json
   └─ Server returns linked quotations

4. DISPLAY RESULTS
   ├─ If none: "No additional quotations yet"
   └─ If some: Show cards for each quotation

5. INTERACT WITH RESULTS
   ├─ Click "View/Edit" → /quotations/{id}
   ├─ Click "Project Report" → /quotations/{id}/report
   └─ Click "Close" → Modal closes

6. BACK TO PROJECT REPORT
   └─ Can create more additional quotations
```

---

## Empty State

```
┌──────────────────────────────────────────┐
│  📋 ADDITIONAL QUOTATIONS MODAL          │
├──────────────────────────────────────────┤
│                                          │
│                                          │
│    ℹ️ No additional quotations yet.      │
│                                          │
│                                          │
└──────────────────────────────────────────┘
```

---

## File Structure

```
resources/
└── views/
    └── view-report.blade.php
        ├─ Line 108-113: "View Additional Quotations" Button
        ├─ Line 718-726: Modal HTML
        └─ Line 728-810: JavaScript Handler

routes/
└── web.php
    └─ Line 104: GET /quotations/{id}/additional-quotations-json

app/Http/Controllers/
└── QuotationController.php
    └─ Line 720-768: getAdditionalQuotationsJson() Method
```

---

## Feature Checklist

- ✅ Button added to project report view
- ✅ Modal HTML created
- ✅ JavaScript event handler implemented
- ✅ Route created for AJAX endpoint
- ✅ Controller method implemented
- ✅ Error handling included
- ✅ Empty state message
- ✅ Status badges with colors
- ✅ XSS protection (escapeHtml)
- ✅ Date formatting
- ✅ Action buttons with links
- ✅ Scrollable modal for many quotations

---

## Similar Features

This feature is designed similar to the **Revisions Modal** that already exists:

```
Both have:
├─ Button to view list
├─ Modal dialog (not page navigation)
├─ Scrollable content area
├─ Status/information display
├─ Action buttons for each item
└─ Empty state message
```

---

## Performance

- **Lazy Loading:** Quotations fetched only when button clicked
- **Efficient Query:** Uses `with(['status', 'materials'])` eager loading
- **Single Request:** One AJAX call for all data
- **Scrollable Modal:** Handles many quotations efficiently

---

## Accessibility

- ✅ Semantic HTML structure
- ✅ ARIA labels on modal
- ✅ Keyboard navigable buttons
- ✅ Color not the only indicator (status + text)
- ✅ Clear, descriptive button labels

---

**Status:** ✅ Ready for Testing  
**Implementation Date:** December 6, 2025  
**Testing Status:** Pending
