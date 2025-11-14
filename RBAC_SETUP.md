# Role-Based Access Control (RBAC) Setup

## Overview
Your application now has a two-tier role system: **Admin** and **Staff**, with the flexibility to add more roles in the future.

## Roles & Permissions

### Admin Role
✅ **Full Access** to:
- View and manage all quotation drafts
- View, edit, and manage materials
- View and edit all prices and fees
- Create and manage quotations
- Create revisions
- View all projects (approved, rejected, completed)
- Create and edit progress reports
- Create and comment on quotations
- User management (future)

### Staff Role
✅ **Limited Access** to:
- ❌ **CANNOT** see draft quotations (drafts are hidden)
- ❌ **CANNOT** see materials table
- ❌ **CANNOT** see prices or fees (hidden in UI)
- ✅ **CAN** view approved projects
- ✅ **CAN** view rejected projects
- ✅ **CAN** view completed projects
- ✅ **CAN** create progress reports
- ✅ **CAN** edit their own progress reports
- ✅ **CAN** create comments on projects
- ✅ **CAN** view revision history

## Setup Instructions

### 1. Run the Seeder (Already Done)
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

This creates:
- All permissions
- Admin role with full permissions
- Staff role with limited permissions

### 2. Assign Roles to Users

#### For Existing Users:
```bash
# Make user an admin (replace 1 with user ID)
php artisan user:assign-role 1 admin

# Make user a staff member (replace 2 with user ID)
php artisan user:assign-role 2 staff
```

#### For New Users:
You can manually assign roles in the database or create a user management interface.

### 3. Verify Setup
- Log in as **Admin**: Should see all features (drafts, materials, prices)
- Log in as **Staff**: Should see only approved/rejected/completed projects, create reports, and comment

## Frontend Changes

### Quotation View (`quotation.blade.php`)
- Materials table is now hidden for staff users
- Price fields are hidden for staff users  
- Fee fields (Labor & Delivery) are hidden for staff users
- Grand total is hidden for staff users
- Staff sees: "Materials and pricing information is restricted to administrators"

### Draft Projects View (`draftprojects.blade.php`)
- Entire draft quotations section is hidden for staff users
- Staff sees: "Access Restricted - You don't have permission to view draft quotations"

### Other Views
- Staff can still view approved, rejected, and completed projects
- Staff can create and view progress reports
- Staff can comment on quotations

## Blade Directives for Permission Checks

In your Blade templates, use:
```blade
@can('view_drafts')
    <!-- Admin-only content -->
@endcan

@can('view_prices')
    <!-- Show prices -->
@endcan

@can('create_progress_report')
    <!-- Show report creation button -->
@endcan

@canany(['view_materials', 'manage_materials'])
    <!-- Materials content -->
@endcanany
```

## Adding New Roles (Future)

To add a new role in the future:

1. Edit `database/seeders/RolesAndPermissionsSeeder.php`
2. Add new permissions array if needed
3. Create the new role and assign permissions
4. Run: `php artisan db:seed --class=RolesAndPermissionsSeeder`

Example:
```php
$managerRole = Role::firstOrCreate(['name' => 'manager']);
$managerRole->syncPermissions([
    'view_drafts',
    'view_materials',
    'view_prices',
    'view_approved_projects',
    'create_progress_report',
    'view_progress_reports',
    'create_comment',
]);
```

## Helper Classes

### PermissionHelper (`app/Helpers/PermissionHelper.php`)
Static methods for checking permissions in controllers:
```php
use App\Helpers\PermissionHelper;

if (PermissionHelper::canViewDrafts()) {
    // Show drafts
}

if (PermissionHelper::isAdmin()) {
    // Admin-only logic
}
```

### Middleware (`app/Http/Middleware/CheckPermission.php`)
Use in routes to protect endpoints:
```php
Route::middleware(['auth', 'permission:view_drafts'])->group(function () {
    // Only admins with view_drafts permission can access
});
```

## Permissions List

| Permission | Admin | Staff |
|-----------|-------|-------|
| view_drafts | ✅ | ❌ |
| view_materials | ✅ | ❌ |
| view_prices | ✅ | ❌ |
| edit_prices | ✅ | ❌ |
| manage_fees | ✅ | ❌ |
| view_approved_projects | ✅ | ✅ |
| view_rejected_projects | ✅ | ✅ |
| view_completed_projects | ✅ | ✅ |
| create_progress_report | ✅ | ✅ |
| edit_progress_report | ✅ | ✅ |
| view_progress_reports | ✅ | ✅ |
| create_comment | ✅ | ✅ |
| edit_own_comment | ✅ | ✅ |
| view_revision_history | ✅ | ✅ |
| create_revision | ✅ | ❌ |
| manage_users | ✅ | ❌ |

## Testing Checklist

- [ ] Log in as Admin - verify all features visible
- [ ] Log in as Staff - verify drafts hidden
- [ ] Log in as Staff - verify materials hidden
- [ ] Log in as Staff - verify prices hidden
- [ ] Log in as Staff - verify fees hidden
- [ ] Log in as Staff - verify approved projects visible
- [ ] Log in as Staff - verify can create progress reports
- [ ] Log in as Staff - verify can comment
- [ ] Log in as Staff - verify can view revisions

---

**Setup Complete!** Your application now has role-based access control implemented.
