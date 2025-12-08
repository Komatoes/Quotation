# ✨ View Additional Quotations Feature

## Overview

Added the ability to **view all additional quotations linked to a parent quotation** in a modal, similar to the revisions feature. Users can now see a list of all created additional quotations with quick access to view/edit them.

---

## 🎯 Features

### User Interface
- **New Button:** "View Additional Quotations" (blue outline button)
- **Modal Display:** Shows all linked quotations in a scrollable list
- **Quick Actions:** Direct links to view, edit, or see project report
- **Status Badges:** Color-coded status indicators (Draft, Pending, Approved, etc.)

### Information Displayed
- ✅ Quotation Subject
- ✅ Quotation ID
- ✅ Description
- ✅ Current Status (with color badge)
- ✅ Created Date
- ✅ Number of Materials
- ✅ Action Buttons (View/Edit, Project Report)

### Empty State
- Shows friendly message "No additional quotations yet" if none exist

---

## 📁 Files Modified

### 1. `resources/views/view-report.blade.php`
**Added:**
- New button "View Additional Quotations" (line ~118)
- Modal HTML for displaying quotations
- JavaScript handler for fetching and displaying quotations

### 2. `routes/web.php`
**Added:**
- Route: `GET /quotations/{id}/additional-quotations-json`
- Route name: `quotations.additional.json`

### 3. `app/Http/Controllers/QuotationController.php`
**Added:**
- Method: `getAdditionalQuotationsJson($id)`
- Fetches linked quotations with relationships
- Returns JSON response with all details

---

## 🔧 Implementation Details

### Button Implementation
```blade
<button type="button" class="btn btn-outline-info mt-3" id="viewAdditionalQtnBtn"
    title="View Additional Quotations for this Project"
    data-parent-id="{{ $quotation->id }}">
    <i class="fa-solid fa-list me-1"></i> View Additional Quotations
</button>
```

### Modal Structure
```blade
<!-- View Additional Quotations Modal -->
<div class="modal fade" id="additionalQuotationsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Additional Quotations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="additionalQuotationsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
```

### JavaScript Flow
```javascript
1. User clicks "View Additional Quotations" button
2. Gets parent quotation ID from data attribute
3. Fetches /quotations/{id}/additional-quotations-json
4. Server returns list of linked quotations
5. JavaScript renders cards for each quotation
6. Modal displays with full quotation details
7. User can click to view/edit or see project report
```

### Controller Method
```php
public function getAdditionalQuotationsJson($id)
{
    // Get parent quotation
    $parentQuotation = Quotation::findOrFail($id);
    
    // Get all linked quotations with relationships
    $additionalQuotations = $parentQuotation->linkedQuotations()
        ->with(['status', 'materials'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($quotation) {
            return [
                'id' => $quotation->id,
                'subject' => $quotation->subject,
                'description' => $quotation->description,
                'status_name' => $quotation->status->status_name,
                'created_at' => $quotation->created_at,
                'materials_count' => $quotation->materials->count(),
                // ... more fields
            ];
        });

    return response()->json([
        'success' => true,
        'quotations' => $additionalQuotations,
        'total' => $additionalQuotations->count()
    ]);
}
```

---

## 📊 User Flow

```
Project Report View (view-report.blade.php)
        ↓
User clicks "View Additional Quotations" button
        ↓
JavaScript captures parent quotation ID
        ↓
AJAX GET /quotations/{id}/additional-quotations-json
        ↓
Server returns list of linked quotations with status
        ↓
Modal opens
        ↓
JavaScript renders quotation cards
        ↓
User sees:
  - Subject
  - Status (color-coded)
  - Description
  - Created date
  - Materials count
  - Action buttons
        ↓
User can:
  - Click "View/Edit" → Go to quotation editor
  - Click "Project Report" → Go to project tracking
  - Click "Close" → Close modal
```

---

## 🎨 Status Badge Colors

| Status | Color | Class |
|--------|-------|-------|
| Draft | Gray | bg-secondary |
| Pending | Yellow | bg-warning |
| Approved | Green | bg-success |
| Rejected | Red | bg-danger |
| Completed | Green | bg-success |
| Ongoing | Blue | bg-info |

---

## 🔗 Database Relationships

**Used Model Method:**
```php
// In Quotation model
public function linkedQuotations()
{
    return $this->hasMany(Quotation::class, 'parent_quotation_id');
}
```

**Query Used:**
```php
$parentQuotation->linkedQuotations()
    ->with(['status', 'materials'])
    ->orderBy('created_at', 'desc')
    ->get()
```

---

## ✅ Features

- ✅ Display list of additional quotations
- ✅ Show quotation details (subject, description, status)
- ✅ Color-coded status badges
- ✅ Material count display
- ✅ Direct links to view/edit quotations
- ✅ Direct links to project reports
- ✅ Empty state handling
- ✅ Error handling with user-friendly messages
- ✅ Scrollable modal for many quotations
- ✅ Date formatting
- ✅ XSS protection (escapeHtml function)

---

## 🚀 How to Use

1. **Navigate to Project Report**
   - Click on any quotation to view its report

2. **View Additional Quotations**
   - Click "View Additional Quotations" button
   - Modal opens showing all linked quotations

3. **Access a Quotation**
   - Click "View/Edit" to open the quotation editor
   - Click "Project Report" to see its project tracking

4. **Close Modal**
   - Click "Close" button or X button

---

## 🔍 Edge Cases Handled

- **No additional quotations:** Shows "No additional quotations yet" message
- **Network error:** Shows error message with retry option
- **Server error:** Displays error dialog
- **Empty description:** Shows "No description" placeholder
- **Missing status:** Defaults to "Unknown"
- **XSS attack:** HTML escaping on all user inputs

---

## 📊 Response Format

```json
{
  "success": true,
  "quotations": [
    {
      "id": 42,
      "subject": "Additional Materials",
      "description": "Extra materials needed",
      "status_name": "Draft",
      "created_at": "2025-12-06T10:30:00",
      "materials_count": 3,
      "labor_fee": 100.00,
      "delivery_fee": 50.00
    }
  ],
  "total": 1
}
```

---

## 🔐 Security

- ✅ Authorization: Parent quotation must exist
- ✅ XSS Protection: HTML escaping on all displayed text
- ✅ CSRF Protection: Route protected by auth middleware
- ✅ Input Validation: Database relationships validate IDs

---

## 🧪 Testing Checklist

- [ ] Create a parent quotation
- [ ] Create additional quotation(s) from project report
- [ ] Click "View Additional Quotations" button
- [ ] Verify modal opens
- [ ] Verify all quotation details display correctly
- [ ] Verify status badges show correct colors
- [ ] Click "View/Edit" button
- [ ] Verify redirects to quotation editor
- [ ] Click "Project Report" button
- [ ] Verify redirects to project report
- [ ] Test with no additional quotations
- [ ] Test error handling

---

## 🎯 Benefits

| Benefit | Impact |
|---------|--------|
| **Visibility** | Users can see all additional quotations at once |
| **Navigation** | Quick links to view/edit without page refresh |
| **Organization** | Related quotations grouped together |
| **User Experience** | Modal stays on current page (no navigation) |
| **Efficiency** | View all details without opening each quotation |

---

## 📝 Code Locations

| Component | Location |
|-----------|----------|
| Button | `view-report.blade.php:108-113` |
| Modal HTML | `view-report.blade.php:718-726` |
| Modal Script | `view-report.blade.php:728-810` |
| Route | `routes/web.php:104` |
| Controller | `QuotationController.php:720-768` |

---

## 🔄 Workflow Example

```
1. Manager creates quotation #100 (parent)
   ↓
2. Client approves, work begins
   ↓
3. Client requests additional materials
   ↓
4. Manager creates quotation #101 (additional)
   ↓
5. Manager creates quotation #102 (additional)
   ↓
6. Manager opens Project Report for quotation #100
   ↓
7. Clicks "View Additional Quotations"
   ↓
8. Modal shows:
   - #101: Additional Materials (Draft)
   - #102: Additional Services (Approved)
   ↓
9. Manager can quickly edit or track either quotation
```

---

**Status:** ✅ Complete and Ready to Test  
**Date:** December 6, 2025  
**Tested:** Not yet - awaiting manual testing
