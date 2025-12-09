# 🔧 KNOWN ISSUES - FIX IMPLEMENTATION SUMMARY

## Issues Fixed

### ✅ Issue #1: Forgot Password link disappears after failed login
**Status**: FIXED
**Problem**: The "Forgot Password?" link disappears after one incorrect login attempt
**Root Cause**: The link was positioned below the form, so when error messages appeared and the form expanded, the link would get pushed down or become hidden due to CSS overflow/scrolling
**Solution**: Moved the "Forgot Password?" link ABOVE the form instead of below, so it's not affected by form error expansion
**File Modified**: `resources/views/login.blade.php` (line ~224)

---

### ✅ Issue #2: Only quotation creator can generate link
**Status**: FIXED
**Problem**: Only the user who created a quotation could generate its public link. Admin users couldn't generate links for quotations they didn't create
**Root Cause**: The `generateToken()` method had a hard check `if (auth()->id() != $quotation->employee_id)` that only allowed the creator
**Solution**: Updated both `generateToken()` and `generateAdditionalToken()` methods to check if user is either the creator OR an admin
**Files Modified**: 
- `app/Http/Controllers/QuotationController.php` (line 722 and 2096)
- Changed authorization logic from:
  ```php
  if (auth()->id() != $quotation->employee_id) {
      return unauthorized error
  }
  ```
- To:
  ```php
  $isCreator = auth()->id() == $quotation->employee_id;
  $isAdmin = $user->hasRole('admin');
  if (!$isCreator && !$isAdmin) {
      return unauthorized error
  }
  ```

---

## Issues Still Requiring Investigation

### ⏳ Issue #3: Delivery fee input disappears after typing
**Status**: INVESTIGATING
**Problem**: The delivery fee input field temporarily disappears while typing, reappears on refresh
**Likely Cause**: The `loadMaterials()` function may be refreshing the table/totals section, affecting the fee inputs
**Next Step**: Need to prevent fee input re-renders when materials are updated

---

### ⏳ Issue #4: System allows past dates for project start
**Status**: NOT YET STARTED
**File to Update**: `resources/views/quotation.blade.php` - Approve Quotation Modal
**Fix Needed**: Add JavaScript validation + backend validation

---

### ⏳ Issue #5: Client details editable in ongoing projects
**Status**: NOT YET STARTED
**File to Update**: `resources/views/view-report.blade.php`
**Fix Needed**: Disable edit client button when status is "ongoing"

---

### ⏳ Issue #6: Admin comments not showing in additional quotation
**Status**: NOT YET STARTED
**Problem**: Admin comments appear under parent project instead of additional quotation
**File to Check**: `resources/views/components/threaded-comments-admin.blade.php`
**Fix Needed**: Verify `quotation_type` parameter is passed correctly

---

### ⏳ Issue #7: Additional quotation public link missing authentication
**Status**: NOT YET STARTED
**Problem**: Customer can view additional quotation without login
**File to Check**: `routes/web.php` and `QuotationController@showPublicQuotation()`
**Fix Needed**: Add token + auth check for additional quotations

---

### ⏳ Issue #8: Additional quotation not accessible from UI
**Status**: NOT YET STARTED
**Problem**: No way to view additional quotation except through parent project
**Fix Needed**: Add menu item or button to view additional quotations

---

### ⏳ Issue #9: Additional quotation progress auto-shows 100%
**Status**: NOT YET STARTED
**Problem**: Progress shows 100% completion without admin approval
**File to Check**: `AdditionalQuotationController.php` and logic for progress calculation
**Fix Needed**: Approval button should appear for admin, progress should be locked until approved

---

### ⏳ Issue #10: Customer view doesn't show comment replies
**Status**: NOT YET STARTED
**Problem**: Replies visible on admin side but not on customer public view
**File to Check**: `resources/views/public-quotation.blade.php` - comments section
**Fix Needed**: Query replies in the public view, ensure they're displayed

---

### ⏳ Issue #11: View Revisions button not clickable in customer view
**Status**: NOT YET STARTED
**Problem**: Button exists but doesn't respond to clicks
**File to Check**: `resources/views/public-quotation.blade.php`
**Fix Needed**: Check button ID, event listener, and handler function

---

### ⏳ Issue #12: Client details editable in archived projects
**Status**: NOT YET STARTED
**Problem**: Completed and Rejected projects still allow client editing
**Files to Update**: Views for completed/rejected projects
**Fix Needed**: Disable edit button for archived statuses

---

### ⏳ Issue #13: System Logs pagination broken
**Status**: NOT YET STARTED
**File to Check**: `SystemLogController.php` and `resources/views/system-logs.blade.php`
**Fix Needed**: Verify pagination parameters are correct

---

## Implementation Order (Priority)

1. ✅ **DONE** - Issue #1: Forgot Password link disappears
2. ✅ **DONE** - Issue #2: Admin can generate links
3. 🔧 **IN PROGRESS** - Issue #3: Delivery fee disappearing
4. ⏳ Issue #4: Past date validation
5. ⏳ Issue #5: Client readonly in ongoing
6. ⏳ Issue #6: Admin comments in additional quotation
7. ⏳ Issue #7: Additional quotation authentication
8. ⏳ Issue #8: Additional quotation accessibility
9. ⏳ Issue #9: Additional quotation progress logic
10. ⏳ Issue #10: Customer view replies
11. ⏳ Issue #11: View Revisions button
12. ⏳ Issue #12: Archived project client readonly
13. ⏳ Issue #13: System logs pagination

---

## Files Modified

- `resources/views/login.blade.php` - Moved forgot password link above form
- `app/Http/Controllers/QuotationController.php` - Updated `generateToken()` and `generateAdditionalToken()` to allow admins

---

## Testing Checklist

- [ ] Login with wrong credentials, verify "Forgot Password?" link is still visible
- [ ] Log in as admin, navigate to quotation created by different user
- [ ] Click "Generate Link" button - should work (previously would show "Unauthorized")
- [ ] Test delivery fee input with materials loaded
- [ ] Verify all other issues are addressed

---

## Next Steps

Continue with Issue #3 (delivery fee input) and work through remaining issues systematically.
Each fix should be tested and committed separately.
