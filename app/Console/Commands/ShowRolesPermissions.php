<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ShowRolesPermissions extends Command
{
    protected $signature = 'roles:show';
    protected $description = 'Show all users with their actual roles and permissions';

    public function handle()
    {
        $this->line("");
        $this->line("╔════════════════════════════════════════════════════════════════════════════════╗");
        $this->line("║                   USERS WITH ROLES & PERMISSIONS                             ║");
        $this->line("╚════════════════════════════════════════════════════════════════════════════════╝");
        $this->line("");

        $users = User::all();

        foreach ($users as $user) {
            $roles = $user->getRoleNames();
            $permissions = $user->getAllPermissions()->pluck('name');
            
            $emailVerified = $user->email_verified_at ? '✅' : '❌';
            $rolesList = $roles->count() > 0 ? implode(', ', $roles->toArray()) : 'No roles';
            $permissionsList = $permissions->count() > 0 ? implode(', ', $permissions->toArray()) : 'No permissions';

            $this->line("👤 User ID: {$user->id}");
            $this->line("   Name:             {$user->name}");
            $this->line("   Username:         {$user->username}");
            $this->line("   Email:            {$user->email}");
            $this->line("   Email Verified:   $emailVerified");
            $this->line("   Roles:            $rolesList");
            $this->line("   Permissions:      $permissionsList");
            $this->line("");
        }

        // Show available roles
        $this->line("╔════════════════════════════════════════════════════════════════════════════════╗");
        $this->line("║                         AVAILABLE ROLES                                       ║");
        $this->line("╚════════════════════════════════════════════════════════════════════════════════╝");
        $this->line("");

        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            $permissions = DB::table('role_has_permissions')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->where('role_has_permissions.role_id', $role->id)
                ->pluck('permissions.name');

            $permissionsList = $permissions->count() > 0 ? implode(', ', $permissions->toArray()) : 'No permissions';
            
            $this->line("🔑 Role: {$role->name}");
            $this->line("   Permissions: $permissionsList");
            $this->line("");
        }

        return 0;
    }
}
