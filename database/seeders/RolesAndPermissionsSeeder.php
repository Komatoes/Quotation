<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Quotations
            'create quotations',
            'edit quotations',
            'delete quotations',
            'view quotations',
            'approve quotations',
            
            // Project Reports
            'create reports',
            'edit reports',
            'view reports',
            'complete reports',
            
            // Materials
            'manage materials',
            
            // Users
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $role = Role::create(['name' => 'admin'])
            ->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'staff'])
            ->givePermissionTo([
                'create quotations',
                'edit quotations',
                'view quotations',
                'create reports',
                'edit reports',
                'view reports',
                'manage materials'
            ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create staff user
        $staff = User::create([
            'name' => 'Staff User',
            'username' => 'staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');
    }
}
