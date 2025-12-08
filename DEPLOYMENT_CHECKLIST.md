# 📋 Option 2 - Deployment & Testing Checklist

**Date:** December 6, 2025  
**Status:** Ready for Deployment  

---

## ✅ Pre-Deployment Checklist

### Database
- [ ] Backup database (just in case)
- [ ] Verify migrations are in `database/migrations/` directory
  - [ ] `2025_12_06_000000_create_additional_quotations_table.php` exists
  - [ ] `2025_12_06_000001_create_additional_quotation_materials_table.php` exists

### Code Files
- [ ] `app/Models/AdditionalQuotation.php` exists and loads
- [ ] `app/Models/AdditionalQuotationMaterial.php` exists and loads
- [ ] `app/Models/Quotation.php` updated with new relationship
- [ ] `app/Http/Controllers/QuotationController.php` updated
- [ ] No PHP syntax errors: `php artisan tinker` (exit with no errors)

### Configuration
- [ ] `.env` is properly configured
- [ ] Database connection is working
- [ ] Cache driver is configured

---

## 🚀 Deployment Steps

### Step 1: Run Migrations
```bash
php artisan migrate
```

**Expected Output:**
```
Migrating: 2025_12_06_000000_create_additional_quotations_table
Migrated: 2025_12_06_000000_create_additional_quotations_table (0.05 seconds)
Migrating: 2025_12_06_000001_create_additional_quotation_materials_table
Migrated: 2025_12_06_000001_create_additional_quotation_materials_table (0.05 seconds)
```

**If you see errors:**
- Check database connection
- Ensure migrations are in correct folder
- Check for typos in migration files

### Step 2: Clear Application Cache
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

**Expected:** Clean output or "Nothing to clear"

### Step 3: Test Laravel Installation
```bash
php artisan tinker
```

Then test models load:
```php
>>> App\Models\AdditionalQuotation::class
>>> App\Models\AdditionalQuotationMaterial::class
>>> App\Models\Quotation::class
>>> exit
```

**Expected:** No errors, classes load successfully

---

## 🧪 Browser Testing Checklist

### Test 1: Create Additional Quotation

**Steps:**
1. [ ] Go to any quotation's project report page
2. [ ] Click "Create Additional Quotation" button (blue button at top)
3. [ ] Modal appears with form
4. [ ] Enter subject: "Test Additional #1"
5. [ ] Enter description: "This is a test"
6. [ ] Click "Create Quotation" button
7. [ ] Should see success message
8. [ ] Should be redirected or modal closes

**Expected Results:**
- ✅ No console errors
- ✅ Success message appears
- ✅ Database has new entry in `additional_quotations` table
- ✅ Can verify in database browser

**Database Check:**
```sql
SELECT * FROM additional_quotations WHERE subject = 'Test Additional #1';
```

Should show:
- `id`: auto-incremented
- `parent_quotation_id`: matches parent quotation ID
- `subject`: "Test Additional #1"
- `description`: "This is a test"
- `progress`: 0
- `created_at`, `updated_at`: current time

### Test 2: View Additional Quotations

**Steps:**
1. [ ] Go back to parent quotation's project report
2. [ ] Click "View Additional Quotations" button (blue info button)
3. [ ] Modal opens with loading state
4. [ ] Quotations load in modal
5. [ ] See quotation card with:
   - [ ] Subject displayed
   - [ ] Status badge (should show parent's status, e.g., "Approved")
   - [ ] Description shown
   - [ ] Created date displayed
   - [ ] "Materials: 0 items" (since we haven't added materials)
   - [ ] Action buttons: "View/Edit" and "Project Report" (if existing)

**Expected Results:**
- ✅ No console errors
- ✅ Quotation appears in modal
- ✅ Status badge shows parent's status (NOT independent status)
- ✅ Data matches what we created
- ✅ Button clicks work

**Browser Console Check:**
- [ ] Open Developer Tools (F12)
- [ ] Go to Console tab
- [ ] No red errors should appear
- [ ] Network tab should show successful GET request to `/additional-quotations-json`

### Test 3: Test Authorization

**Steps:**
1. [ ] Create a second user account (non-owner)
2. [ ] Log in as second user
3. [ ] Try to access quotation from first user
4. [ ] Try to create additional quotation
5. [ ] Should be denied or hidden

**Expected Results:**
- ✅ Non-owner cannot create additional quotations (should get 403 error)
- ✅ Non-owner cannot view additional quotations (should get 403 error)

### Test 4: Test Multiple Additional Quotations

**Steps:**
1. [ ] Create 3+ additional quotations for same parent
2. [ ] Click "View Additional Quotations"
3. [ ] All should appear in list
4. [ ] They should be ordered newest first

**Expected Results:**
- ✅ All quotations appear
- ✅ Most recent appears first
- [ ] Order by created_at DESC works correctly

### Test 5: Test Status Inheritance

**Steps:**
1. [ ] Create additional quotation
2. [ ] View it in modal
3. [ ] Change parent quotation's status
4. [ ] View additional quotations again
5. [ ] Status badge should show new parent status

**Expected Results:**
- ✅ Status changes when parent changes
- ✅ All children show same status
- ✅ Status is clearly inherited, not independent

### Test 6: Test Mobile/Responsive

**Steps:**
1. [ ] Open on tablet (resize browser)
2. [ ] Open on mobile (resize browser to 375px width)
3. [ ] Create and view additional quotations
4. [ ] Modal should be responsive
5. [ ] Cards should stack properly
6. [ ] Buttons should be clickable

**Expected Results:**
- ✅ Works on all screen sizes
- ✅ Text readable
- ✅ Buttons clickable
- ✅ No broken layout

---

## 🔍 Database Verification

### Check Tables Exist
```sql
SHOW TABLES LIKE 'additional%';
```

**Expected Output:**
```
additional_quotation_materials
additional_quotations
```

### Check Table Structure
```sql
DESCRIBE additional_quotations;
```

**Expected Columns:**
- id
- parent_quotation_id
- subject
- description
- progress
- created_at
- updated_at

```sql
DESCRIBE additional_quotation_materials;
```

**Expected Columns:**
- id
- additional_quotation_id
- material_id
- quantity
- unit_cost
- created_at
- updated_at

### Check Foreign Keys
```sql
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME IN ('additional_quotations', 'additional_quotation_materials');
```

**Expected:**
- `additional_quotations.parent_quotation_id` → `quotations.id`
- `additional_quotation_materials.additional_quotation_id` → `additional_quotations.id`
- `additional_quotation_materials.material_id` → `materials.id`

---

## 📊 Performance Testing

### Query Performance Check

**In Laravel Tinker:**
```php
$parent = \App\Models\Quotation::with('additionalQuotations.materials.material')->find(100);

// Should use only 3 queries:
// 1. Get parent quotation
// 2. Get additional quotations
// 3. Get materials for all quotations
```

**Expected:**
- ✅ No N+1 queries
- ✅ Response time < 100ms for < 1000 items
- ✅ Memory usage reasonable

### Load Testing

**Create many additional quotations:**
```bash
# In PHP/Artisan command or tinker:
for ($i = 1; $i <= 100; $i++) {
    \App\Models\AdditionalQuotation::create([
        'parent_quotation_id' => 1,
        'subject' => "Additional #{$i}",
        'description' => "Test additional quotation",
        'progress' => rand(0, 100),
    ]);
}
```

Then test view endpoint:
- [ ] Modal loads 100 quotations smoothly
- [ ] No timeout errors
- [ ] Page responsive
- [ ] Response time < 500ms

---

## 📝 Logging & Monitoring

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

**Expected:**
- [ ] No ERROR entries
- [ ] INFO entries show operations
- [ ] WARNING entries for auth failures

**Look for:**
```
[2025-12-06] Additional quotation created successfully
[2025-12-06] Additional quotations fetched
```

### Monitor Database
```sql
-- Check for slow queries
SELECT * FROM mysql.slow_log LIMIT 10;

-- Check record count
SELECT COUNT(*) FROM additional_quotations;
SELECT COUNT(*) FROM additional_quotation_materials;
```

---

## 🐛 Debugging Guide

### If Create Fails

**Check:**
1. Is parent quotation ID valid?
   ```sql
   SELECT id FROM quotations WHERE id = 1;
   ```

2. Are migrations applied?
   ```bash
   php artisan migrate:status
   ```

3. Check logs:
   ```bash
   tail -20 storage/logs/laravel.log
   ```

4. Test in tinker:
   ```php
   $q = \App\Models\AdditionalQuotation::create([
       'parent_quotation_id' => 1,
       'subject' => 'Test',
       'description' => 'Test'
   ]);
   ```

### If View Fails

**Check:**
1. Are there quotations in DB?
   ```sql
   SELECT * FROM additional_quotations LIMIT 1;
   ```

2. Is parent quotation authorized?
   ```php
   $parent = \App\Models\Quotation::find(1);
   auth()->user()->id === $parent->employee_id
   ```

3. Check network request:
   - Open DevTools (F12)
   - Go to Network tab
   - Click "View Additional Quotations"
   - Check request to `/additional-quotations-json`
   - Should be 200 status with JSON response

### If Status Inheritance Not Working

**Check:**
1. Parent quotation has status?
   ```sql
   SELECT id, status_id FROM quotations WHERE id = 1;
   SELECT * FROM quotation_status WHERE id = (SELECT status_id FROM quotations WHERE id = 1);
   ```

2. Relationship is loading?
   ```php
   $q = \App\Models\AdditionalQuotation::with('parentQuotation.status')->find(1);
   $q->parentQuotation->status->status_name
   ```

---

## ✅ Sign-Off Checklist

- [ ] Migrations executed successfully
- [ ] Create test passed
- [ ] View test passed
- [ ] Authorization test passed
- [ ] Database verification complete
- [ ] No console errors
- [ ] Responsive design works
- [ ] Performance is acceptable
- [ ] Logging shows expected entries
- [ ] All documentation reviewed

---

## 🚀 Ready for Production?

**YES** when:
- ✅ All tests in this checklist pass
- ✅ No errors in logs
- ✅ Performance acceptable
- ✅ Team approves
- ✅ Database backup taken

**Deploy with:**
```bash
# On production server
php artisan migrate
php artisan cache:clear
# Restart application/web server
```

---

## 📞 Support

If issues arise:

1. **Check Documentation:**
   - `OPTION_2_IMPLEMENTATION.md` - Full details
   - `OPTION_2_QUICK_START.md` - Quick reference
   - `OPTION_2_VISUAL_ARCHITECTURE.md` - Diagrams

2. **Review Code:**
   - `app/Models/AdditionalQuotation.php`
   - `app/Models/QuotationController.php` (relevant methods)

3. **Test Again:**
   - Clear cache: `php artisan cache:clear`
   - Re-run migrations: `php artisan migrate:refresh --seed`
   - Test in browser

4. **Check Logs:**
   - `tail -f storage/logs/laravel.log`

---

**Checklist Created:** December 6, 2025  
**Status:** Ready for Deployment  
**Version:** 1.0  

✅ **All systems go for launch!**
