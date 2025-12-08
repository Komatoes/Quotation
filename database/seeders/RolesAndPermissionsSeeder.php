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
            // Quotation Management (Admin only)
            'view_drafts',
            'create_quotation',
            'edit_quotation',
            'delete_quotation',
            'view_materials',
            'manage_materials',
            'view_prices',
            'edit_prices',
            'manage_fees',
            'view_all_quotations',
            
            // Project Management (Both can view approved/rejected/completed)
            'view_approved_projects',
            'view_rejected_projects',
            'view_completed_projects',
            
            // Progress Reports (Staff can create/edit, both can view)
            'create_progress_report',
            'edit_progress_report',
            'view_progress_reports',
            'delete_progress_report',
            
            // Comments (Both can comment)
            'create_comment',
            'edit_own_comment',
            'delete_own_comment',
            
            // Revisions (Admin manages, staff can view)
            'create_revision',
            'view_revision_history',
            'delete_revision',
            
            // User Management (Admin only)
            'manage_users',
            'manage_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Delete existing roles (except if they're being updated)
        Role::where('name', 'manager')->delete();
        Role::where('name', 'client')->delete();

        // Create Admin role with full permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create Staff role with limited permissions
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions([
            'view_approved_projects',
            'view_rejected_projects',
            'view_completed_projects',
            'create_progress_report',
            'edit_progress_report',
            'view_progress_reports',
            'create_comment',
            'edit_own_comment',
            'delete_own_comment',
            'view_revision_history',
        ]);

        $this->command->info('✅ Roles and permissions setup completed!');
        $this->command->line('<info>Admin Role:</info> Full access to all features');
        $this->command->line('<info>Staff Role:</info> Can view approved/rejected/completed projects, create progress reports, comment, and view revision history');
        $this->command->line('<info>Future roles:</info> Add new roles by extending this seeder');
    }
}
