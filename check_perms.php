<?php
// Check permission names
use Spatie\Permission\Models\Permission;
use App\Models\User;

echo "=== MATERIAL PERMISSIONS ===\n";
$materialPerms = Permission::where('name', 'like', '%material%')->get();
foreach ($materialPerms as $perm) {
    echo "✓ " . $perm->name . "\n";
}

echo "\n=== ADMIN USER CHECK ===\n";
$admin = User::where('email', 'admin@example.com')->first();
if ($admin) {
    echo "Admin user found: " . $admin->name . "\n";
    echo "Roles: " . $admin->roles->pluck('name')->join(', ') . "\n";
    
    echo "\nAdmin permissions:\n";
    $adminPerms = $admin->roles->map(function($role) {
        return $role->permissions->pluck('name');
    })->flatten()->unique();
    
    foreach ($adminPerms->take(30) as $perm) {
        echo "  - " . $perm . "\n";
    }
    
    echo "\nPermission checks:\n";
    echo "  view_materials: " . ($admin->hasPermissionTo('view_materials') ? "✓ YES" : "✗ NO") . "\n";
    echo "  manage_materials: " . ($admin->hasPermissionTo('manage_materials') ? "✓ YES" : "✗ NO") . "\n";
    echo "  view_prices: " . ($admin->hasPermissionTo('view_prices') ? "✓ YES" : "✗ NO") . "\n";
} else {
    echo "Admin user NOT found\n";
}
?>
