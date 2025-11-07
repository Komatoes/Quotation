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
            'reject quotations',
            'view all quotations',
            
            // Project Reports
            'create reports',
            'edit reports',
            'view reports',
            'complete reports',
            'delete reports',
            
            // Materials
            'manage materials',
            'create materials',
            'edit materials',
            'delete materials',
            'view materials',
            
            // Projects
            'manage projects',
            'create projects',
            'edit projects',
            'delete projects',
            'view projects',
            
            // Comments
            'create comments',
            'edit comments',
            'delete comments',
            'view comments',
            'create internal comments',
            'view internal comments',
            
            // Users
            'manage users',
            'manage roles',
            'view users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $role = Role::create(['name' => 'admin'])
            ->givePermissionTo(Permission::all());

        // Manager Role
        $role = Role::create(['name' => 'manager'])
            ->givePermissionTo([
                'create quotations',
                'edit quotations',
                'view quotations',
                'approve quotations',
                'reject quotations',
                'view all quotations',
                'create reports',
                'edit reports',
                'view reports',
                'complete reports',
                'manage materials',
                'manage projects',
                'create internal comments',
                'view internal comments',
                'view users'
            ]);

        // Staff Role
        $role = Role::create(['name' => 'staff'])
            ->givePermissionTo([
                'create quotations',
                'edit quotations',
                'view quotations',
                'create reports',
                'edit reports',
                'view reports',
                'view materials',
                'create materials',
                'view projects',
                'create comments',
                'view comments',
                'view internal comments'
            ]);

        // Client Role
        $role = Role::create(['name' => 'client'])
            ->givePermissionTo([
                'view quotations',
                'view materials',
                'view projects',
                'create comments',
                'view comments'
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
