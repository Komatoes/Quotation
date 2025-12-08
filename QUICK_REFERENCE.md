# Quick Reference: Role-Based Access Control

## 🎯 Current Setup

### Admin Role
- **Full System Access**
- Can see: Drafts, Materials, Prices, Fees, All Projects
- Can do: Everything

### Staff Role  
- **Limited Access**
- Can see: Approved, Rejected, Completed Projects
- Cannot see: Drafts, Materials, Prices, Fees
- Can do: Progress Reports, Comments, View Revisions

## 🚀 Quick Commands

### Assign Admin to User
```bash
php artisan user:assign-role {user_id} admin
```

### Assign Staff to User
```bash
php artisan user:assign-role {user_id} staff
```

### Re-seed Permissions (careful!)
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

## 📋 Blade Template Usage

### Check if Admin
```blade
@can('view_drafts')
    <!-- Show admin-only content -->
@endcan
```

### Check Multiple Permissions
```blade
@canany(['view_materials', 'manage_materials'])
    <!-- Show materials -->
@endcanany
```

### Check Role
```blade
@if(Auth::user()->hasRole('admin'))
    <!-- Admin content -->
@endif
```

## 🔐 Protected Features by Role

| Feature | Admin | Staff |
|---------|-------|-------|
| View Drafts | ✅ | ❌ |
| Add Materials | ✅ | ❌ |
| See Prices | ✅ | ❌ |
| Edit Fees | ✅ | ❌ |
| View Projects | ✅ | ✅ |
| Create Reports | ✅ | ✅ |
| Comment | ✅ | ✅ |

## 📁 Files Modified/Created

1. **database/seeders/RolesAndPermissionsSeeder.php** - Updated with Admin/Staff roles
2. **resources/views/quotation.blade.php** - Added permission checks for materials, prices, fees
3. **resources/views/draftprojects.blade.php** - Hidden drafts from staff
4. **app/Helpers/PermissionHelper.php** - Created helper class for permission checks
5. **app/Http/Middleware/CheckPermission.php** - Created middleware
6. **app/Console/Commands/AssignUserRole.php** - Created command for role assignment
7. **app/View/Components/PermissionCheck.php** - Created view component

## 🔄 Future Expansion

To add a new role (e.g., "manager"):

1. Edit `database/seeders/RolesAndPermissionsSeeder.php`
2. Add the role definition
3. Run seeder again: `php artisan db:seed --class=RolesAndPermissionsSeeder`

Example:
```php
$managerRole = Role::firstOrCreate(['name' => 'manager']);
$managerRole->syncPermissions([
    'view_drafts',
    'view_materials',
    // ... add permissions
]);
```

## ✅ Testing Checklist

- [ ] Admin can see drafts
- [ ] Admin can see materials
- [ ] Admin can see prices and fees
- [ ] Staff cannot see drafts
- [ ] Staff cannot see materials  
- [ ] Staff cannot see prices and fees
- [ ] Staff can see approved projects
- [ ] Staff can create progress reports
- [ ] Staff can comment on projects

---

**All permissions are cached!** Clear cache if making permission changes:
```bash
php artisan cache:clear
```
