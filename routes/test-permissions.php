<?php
// Quick test file to verify permissions
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Test 1: Check if roles exist
$adminRole = Role::where('name', 'admin')->first();
$staffRole = Role::where('name', 'staff')->first();

echo "=== ROLES ===\n";
echo "Admin Role exists: " . ($adminRole ? "✅ YES" : "❌ NO") . "\n";
echo "Staff Role exists: " . ($staffRole ? "✅ YES" : "❌ NO") . "\n\n";

// Test 2: Check admin permissions
if ($adminRole) {
    $adminPerms = $adminRole->permissions;
    echo "=== ADMIN PERMISSIONS ===\n";
    echo "Total permissions: " . $adminPerms->count() . "\n";
    echo "Permissions: " . $adminPerms->pluck('name')->join(', ') . "\n\n";
}

// Test 3: Check staff permissions
if ($staffRole) {
    $staffPerms = $staffRole->permissions;
    echo "=== STAFF PERMISSIONS ===\n";
    echo "Total permissions: " . $staffPerms->count() . "\n";
    echo "Permissions: " . $staffPerms->pluck('name')->join(', ') . "\n\n";
}

// Test 4: Check admin user
$adminUser = User::where('email', 'admin@example.com')->first();
echo "=== ADMIN USER ===\n";
echo "Admin user exists: " . ($adminUser ? "✅ YES" : "❌ NO") . "\n";
if ($adminUser) {
    echo "Name: " . $adminUser->name . "\n";
    echo "Username: " . $adminUser->username . "\n";
    echo "Email: " . $adminUser->email . "\n";
    echo "Has admin role: " . ($adminUser->hasRole('admin') ? "✅ YES" : "❌ NO") . "\n";
    echo "User roles: " . $adminUser->roles->pluck('name')->join(', ') . "\n";
    echo "User permissions: " . $adminUser->getAllPermissions()->pluck('name')->join(', ') . "\n";
}

echo "\n=== PERMISSION CHECK ===\n";
if ($adminUser) {
    echo "Can view_materials: " . ($adminUser->can('view_materials') ? "✅ YES" : "❌ NO") . "\n";
    echo "Can manage_users: " . ($adminUser->can('manage_users') ? "✅ YES" : "❌ NO") . "\n";
    echo "Can view_all_quotations: " . ($adminUser->can('view_all_quotations') ? "✅ YES" : "❌ NO") . "\n";
}
?>
