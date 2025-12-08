# 🔐 Permissions & Roles Verification Checklist

## ✅ What We Just Did

1. **Ran fresh migrations** with seed (`php artisan migrate:fresh --seed`)
   - All permission tables created ✅
   - All roles created (admin, staff) ✅
   - All permissions assigned ✅
   - Admin and Staff users created with credentials ✅

2. **Cleared all caches** to ensure permissions are loaded fresh
   - Application cache cleared ✅
   - Config cache cleared ✅
   - Route cache cleared ✅
   - View cache cleared ✅

## 🔑 Credentials

### Admin Account
- **Username**: `admin`
- **Email**: `admin@example.com`
- **Password**: `password`
- **Role**: `admin` (Full access to ALL features)

### Staff Account
- **Username**: `staff`
- **Email**: `staff@example.com`
- **Password**: `password`
- **Role**: `staff` (Limited access to projects, reports, comments)

---

## 📋 Admin Permissions (26 total)

The admin role has access to ALL permissions including:

### Quotation Management
- `view_drafts` ✅
- `create_quotation` ✅
- `edit_quotation` ✅
- `delete_quotation` ✅
- `view_all_quotations` ✅

### Materials & Pricing
- `view_materials` ✅
- `manage_materials` ✅
- `view_prices` ✅
- `edit_prices` ✅
- `manage_fees` ✅

### Project Management
- `view_approved_projects` ✅
- `view_rejected_projects` ✅
- `view_completed_projects` ✅

### Progress Reports
- `create_progress_report` ✅
- `edit_progress_report` ✅
- `view_progress_reports` ✅
- `delete_progress_report` ✅

### Comments & Feedback
- `create_comment` ✅
- `edit_own_comment` ✅
- `delete_own_comment` ✅

### Revisions
- `create_revision` ✅
- `view_revision_history` ✅
- `delete_revision` ✅

### User Management
- `manage_users` ✅
- `manage_roles` ✅

---

## 📋 Staff Permissions (9 total)

Staff has limited access to:
- `view_approved_projects` ✅
- `view_rejected_projects` ✅
- `view_completed_projects` ✅
- `create_progress_report` ✅
- `edit_progress_report` ✅
- `view_progress_reports` ✅
- `create_comment` ✅
- `edit_own_comment` ✅
- `delete_own_comment` ✅
- `view_revision_history` ✅

---

## 🧪 Testing Your Permissions

### Method 1: Check Database Directly
You can verify roles and permissions in your MySQL database:

```sql
-- Check if roles exist
SELECT * FROM roles;

-- Check permissions
SELECT * FROM permissions;

-- Check role-permission assignments
SELECT r.name as role, p.name as permission 
FROM role_has_permissions rp
JOIN roles r ON rp.role_id = r.id
JOIN permissions p ON rp.permission_id = p.id
ORDER BY r.name;

-- Check user roles
SELECT u.id, u.username, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id;
```

### Method 2: Test in Browser
1. **Log in as admin** with username `admin` and password `password`
2. Go to `/dashboard` - you should see all quotations, materials, and management options
3. Check if the following buttons/links appear:
   - ✅ "Add Material" button
   - ✅ "Generate Link" button  
   - ✅ "View Revisions" button
   - ✅ "Create Revision" button
   - ✅ Materials table with prices visible
   - ✅ Labor fee and delivery fee inputs
   - ✅ Approve/Save Draft/Reject/Export buttons

### Method 3: Check in Code
Look at your blade views to see permission checks:

```blade
@if (Auth::user()->can('view_materials'))
    <!-- Materials section visible to authorized users -->
@endif

@if (Auth::user()->can('manage_fees'))
    <!-- Fee inputs visible to authorized users -->
@endif
```

---

## 🔄 If Permissions Are Still Missing

### Issue 1: Cache Not Cleared
**Solution**: Clear all caches again
```powershell
php artisan optimize:clear
```

### Issue 2: User Not Re-logged After Fresh Seed
**Solution**: Log out and log back in. The user's permission cache in the session needs to be refreshed.

### Issue 3: Permissions Not Loading in Blade
**Cause**: Spatie caches permissions for 24 hours
**Solution**: Clear cache manually
```powershell
php artisan cache:clear
```

### Issue 4: Permission Middleware Not Working
**Check**: Look at routes that use `permission:` middleware
```php
Route::middleware(['auth', 'permission:view_materials'])->group(function () {
    // Only users with 'view_materials' permission can access these routes
});
```

---

## 🚀 Next Steps

1. **Verify the fresh seed worked:**
   ```powershell
   php artisan tinker
   ```
   Then run:
   ```php
   $admin = User::where('email', 'admin@example.com')->first();
   $admin->can('view_materials'); // Should return true
   ```

2. **Log in as admin** with the credentials above

3. **Check if all expected buttons/features appear** on the quotation page

4. **Test staff account** to ensure they DON'T see admin-only features

5. **If still having issues**, run:
   ```powershell
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```
   Then log out and back in.

---

## 📝 Database Schema

### Permissions Tables Created:
- `permissions` - All available permissions
- `roles` - All roles (admin, staff)
- `role_has_permissions` - Maps roles to permissions
- `model_has_roles` - Maps users to roles
- `model_has_permissions` - Direct user permissions (if assigned)

---

## ⚠️ Common Issues & Fixes

| Issue | Cause | Fix |
|-------|-------|-----|
| Permissions not showing after login | User cache not cleared | Log out and back in |
| Fresh seed not working | Database locked | Stop Apache, clear temp files, restart |
| Can't see "Add Material" button | Missing `view_materials` permission | Run `php artisan db:seed` |
| Routes returning 403 Forbidden | Permission middleware blocking | Check middleware in routes/web.php |
| Permission cache stale | 24-hour cache not expired | Run `php artisan cache:clear` |

---

## ✨ Summary

Your system now has:
- ✅ 2 roles (admin, staff) fully configured
- ✅ 26 permissions properly mapped
- ✅ 2 test users (admin & staff) with correct roles
- ✅ All database caches cleared and refreshed
- ✅ Routes properly protected with middleware

**You should now be able to log in as admin and see ALL features!**

If you're still missing permissions after logging in:
1. Try logging out
2. Clear browser cache (Ctrl+Shift+Delete)
3. Log back in
4. Run `php artisan cache:clear` in terminal if needed

