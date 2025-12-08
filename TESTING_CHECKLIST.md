# ✅ Testing Checklist: Additional Quotations Feature

## Pre-Testing Setup

- [ ] Run migrations: `php artisan migrate`
- [ ] Clear any cache: `php artisan cache:clear`
- [ ] Test in incognito/private browser (no caching issues)

---

## Unit 1: CREATE FLOW

### 1.1 Modal Opening
- [ ] Navigate to a quotation's report page
- [ ] Find "Additional Quotation" button
- [ ] Click it → Modal opens
- [ ] Modal has "Subject" input field
- [ ] Modal has "Description" textarea
- [ ] Modal has "Cancel" and "Create Quotation" buttons

### 1.2 Form Validation
- [ ] Try clicking "Create Quotation" with empty subject
  - ✓ Should show error: "Please enter a quotation subject"
- [ ] Enter subject, leave description blank
  - ✓ Should allow (description is optional)
- [ ] Enter both subject and description
  - ✓ Should proceed to next step

### 1.3 Creation
- [ ] Fill in Subject: "Test Additional #1"
- [ ] Fill in Description: "Testing additional quotation feature"
- [ ] Click "Create Quotation"
- [ ] Should see success message
- [ ] Should redirect to `/additional-quotations/{id}/edit`
- [ ] Check database:
  ```sql
  SELECT * FROM additional_quotations WHERE subject = 'Test Additional #1';
  ```
  - ✓ Record exists with correct parent_quotation_id
  - ✓ progress = 0
  - ✓ labor_fee = 0
  - ✓ delivery_fee = 0

---

## Unit 2: EDIT TEMPLATE

### 2.1 Page Loads
- [ ] Page shows parent quotation name
- [ ] Page shows customer name
- [ ] Page shows "Subject" as header
- [ ] Materials table is visible (empty initially)
- [ ] Labor fee input visible
- [ ] Delivery fee input visible
- [ ] Grand total shows ₱0.00

### 2.2 Layout Elements
- [ ] "Add Material" button visible
- [ ] "Save Changes" button visible
- [ ] "Approve & Attach to Parent" button visible
- [ ] "Back" button visible
- [ ] All buttons are clickable

---

## Unit 3: MATERIALS CRUD

### 3.1 Add Material
- [ ] Click "Add Material" button
- [ ] Modal opens with "Select Material" dropdown
- [ ] Material list populated (should show all materials)
- [ ] Each material shows name and price
- [ ] Quantity input defaults to 1
- [ ] Click "Add"
  - ✓ Material added to table
  - ✓ Page reloads
  - ✓ Material appears in materials table
  - ✓ Check database for pivot table entry

### 3.2 Multiple Materials
- [ ] Add 3 different materials with different quantities
  - Material 1: Qty 5
  - Material 2: Qty 10
  - Material 3: Qty 2
- [ ] All appear in table
- [ ] Grand total updates correctly

### 3.3 Remove Material
- [ ] Click [X] button on a material row
- [ ] Confirmation dialog appears
- [ ] Click "Yes, remove it"
  - ✓ Material removed from table
  - ✓ Grand total updates
  - ✓ Check database: pivot record deleted

### 3.4 Duplicate Material Check
- [ ] Try adding same material twice
  - ✓ Should show error: "Material already attached to this quotation"
  - ✓ Material not added twice

---

## Unit 4: FEES

### 4.1 Labor Fee
- [ ] Input labor fee: 100
- [ ] Grand total updates: previous_total + 100
- [ ] Input labor fee: 0
- [ ] Grand total updates back

### 4.2 Delivery Fee
- [ ] Input delivery fee: 50
- [ ] Grand total updates: previous_total + 50
- [ ] Input delivery fee: 0
- [ ] Grand total updates back

### 4.3 Both Fees Together
- [ ] Materials total: $500 (from 3 materials)
- [ ] Labor fee: 75
- [ ] Delivery fee: 25
- [ ] Grand total should be: $600 ✓
- [ ] Display shows: "₱600.00" ✓

---

## Unit 5: SAVE CHANGES

### 5.1 Save Fees
- [ ] Set labor_fee to 150
- [ ] Set delivery_fee to 75
- [ ] Click "Save Changes"
- [ ] Should see: "Changes saved" message
- [ ] Check database:
  ```sql
  SELECT labor_fee, delivery_fee FROM additional_quotations WHERE id = {id};
  ```
  - ✓ labor_fee = 150.00
  - ✓ delivery_fee = 75.00

### 5.2 Edit After Save
- [ ] Change labor_fee to 200
- [ ] Click "Save Changes"
- [ ] Database updates to 200.00 ✓

---

## Unit 6: APPROVAL FLOW

### 6.1 Approval Button
- [ ] "Approve & Attach to Parent" button visible
- [ ] Click it
- [ ] Confirmation dialog shows
- [ ] Dialog says: "This will mark the additional quotation as approved and attach it to the parent quotation."
- [ ] Click "Cancel" → Dialog closes, no change
- [ ] Click "Yes, approve" → Dialog closes, processing

### 6.2 After Approval
- [ ] See success message: "Approved and attached!"
- [ ] Page reloads
- [ ] Status badge changes to: "✓ Approved & Attached to Parent"
- [ ] "Approve & Attach" button disappears (it's now approved)
- [ ] Check database:
  ```sql
  SELECT progress FROM additional_quotations WHERE id = {id};
  ```
  - ✓ progress = 100

---

## Unit 7: VIEW ADDITIONAL QUOTATIONS

### 7.1 Button Presence
- [ ] Return to parent quotation page
- [ ] "View Additional Quotations" button visible
- [ ] Click it

### 7.2 Modal Contents
- [ ] Modal opens: "Additional Quotations"
- [ ] Lists all additional quotations for this parent
- [ ] Shows:
  - Subject
  - ID
  - Status badge (color-coded)
  - Description
  - Created date
  - Material count
  - [View/Edit] and [Delete] buttons

### 7.3 Status Badges
- [ ] Unapproved quotations show: ⏳ "In Progress" (blue/yellow)
- [ ] Approved quotations show: ✓ "Approved & Attached to Parent" (green)

### 7.4 View/Edit Link
- [ ] Click [View/Edit] button
- [ ] Should navigate to `/additional-quotations/{id}/edit`
- [ ] Should be same page you were just on (or editing)

### 7.5 Delete
- [ ] Click [Delete] button on an additional quotation
  - OR use [Delete] button on edit page
- [ ] Confirmation dialog
- [ ] After confirmation:
  - ✓ Record deleted from database
  - ✓ Modal updates (record removed from list)
  - ✓ Check that linked materials are also deleted (cascade delete)

---

## Unit 8: DATABASE INTEGRITY

### 8.1 Cascade Delete
- [ ] Create an additional quotation with 3 materials
- [ ] Delete the additional quotation
- [ ] Check database:
  ```sql
  SELECT COUNT(*) FROM additional_quotation_materials 
  WHERE additional_quotation_id = {deleted_id};
  ```
  - ✓ Count = 0 (materials deleted too)

### 8.2 Cascade Delete on Parent Delete
- [ ] Note parent quotation ID
- [ ] Delete the parent quotation
- [ ] Check database:
  ```sql
  SELECT COUNT(*) FROM additional_quotations 
  WHERE parent_quotation_id = {deleted_parent_id};
  ```
  - ✓ Count = 0 (all additional quotations deleted)
  ```sql
  SELECT COUNT(*) FROM additional_quotation_materials 
  WHERE additional_quotation_id IN (
    SELECT id FROM additional_quotations WHERE parent_quotation_id = {deleted_parent_id}
  );
  ```
  - ✓ Count = 0 (all materials also deleted)

---

## Unit 9: AUTHORIZATION & SECURITY

### 9.1 Employee Restriction
- [ ] Create quotation as Employee A
- [ ] Create additional quotation
- [ ] Login as Employee B
- [ ] Try to access `/additional-quotations/{id}/edit` directly
  - ✓ Should show: 403 Forbidden / "Unauthorized"
- [ ] Try to POST update as Employee B
  - ✓ API should return: 403 error

### 9.2 Validation
- [ ] Try to add material with invalid material_id
  - ✓ Error message appears
- [ ] Try to add material with negative quantity
  - ✓ Form validation prevents it
- [ ] Try to add material with zero quantity
  - ✓ Form validation prevents it

---

## Unit 10: EDGE CASES

### 10.1 No Materials
- [ ] Create additional quotation
- [ ] Don't add any materials
- [ ] Set labor_fee: 100, delivery_fee: 50
- [ ] Save
- [ ] Grand total shows: ₱150.00 ✓

### 10.2 No Fees
- [ ] Create additional quotation
- [ ] Add 2 materials (total $300)
- [ ] Don't enter labor_fee or delivery_fee
- [ ] Grand total shows: ₱300.00 ✓

### 10.3 Large Numbers
- [ ] Add material with quantity: 1000000
- [ ] Set labor_fee: 999999.99
- [ ] Set delivery_fee: 999999.99
- [ ] Grand total calculates correctly
- [ ] No overflow errors

### 10.4 Decimal Quantities
- [ ] Add material with quantity: 5.5
- [ ] Add material with quantity: 0.25
- [ ] Both appear correctly in table
- [ ] Totals calculate correctly

---

## Performance Testing

### 11.1 Loading Times
- [ ] Edit page loads in < 2 seconds
- [ ] Materials modal opens quickly
- [ ] List of 10+ additional quotations loads quickly

### 11.2 Database Queries
- [ ] Use Laravel Debugbar to check queries
- [ ] Edit page should run ~5 queries max
- [ ] No N+1 query issues

---

## Browser Compatibility

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (if available)
- [ ] Mobile browser (responsive design)

---

## Final Verification

- [ ] All form validations work
- [ ] All buttons work
- [ ] All redirects work
- [ ] All database operations work
- [ ] No console errors
- [ ] No 500 errors
- [ ] Permissions enforced
- [ ] Cascade deletes work
- [ ] Grand totals calculate correctly
- [ ] UI is clean and intuitive

---

## Known Limitations (By Design)

❌ Additional quotations don't have their own public links
❌ Additional quotations can't be viewed by public customers (unless you add that feature)
❌ Progress field only stores 0 or 100 (draft or approved)
❌ Can't edit subject/description after creation (optional - can add this)

---

## Sign-Off

**Tested By:** _______________  
**Date:** _______________  
**Status:** ☐ PASS ☐ FAIL  

**Notes:**  
_________________________________  
_________________________________  

