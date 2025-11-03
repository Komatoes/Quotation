<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Customer permissions
            'view customers',
            'manage customers',
            'delete customers',
            
            // Quotation permissions
            'create quotations',
            'edit quotations',
            'delete quotations',
            'approve quotations',
            'view quotations',
            
            // Role & User management
            'manage roles',
            'manage users',
            
            // Service & Interaction
            'record interactions',
            'view interactions',
            'manage service history',
            'view service history',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin Role
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Internal User Role
        $internalRole = Role::create(['name' => 'Internal']);
        $internalRole->givePermissionTo([
            'view customers',
            'manage customers',
            'create quotations',
            'edit quotations',
            'view quotations',
            'record interactions',
            'view interactions',
            'manage service history',
            'view service history',
        ]);

        // Customer Role
        $customerRole = Role::create(['name' => 'Customer']);
        $customerRole->givePermissionTo([
            'view quotations',
            'view service history',
            'view interactions',
        ]);
    }
}