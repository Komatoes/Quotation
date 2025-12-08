<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignAdminRole extends Command
{
    protected $signature = 'admin:assign-role {email}';
    protected $description = 'Assign admin role to a user by email';

    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User with email '$email' not found!");
            return 1;
        }

        // Remove all roles first
        $user->syncRoles([]);

        // Assign admin role
        $user->assignRole('admin');

        $this->info("✅ Admin role assigned successfully!");
        $this->line("");
        $this->line("User Details:");
        $this->line("  ID: {$user->id}");
        $this->line("  Name: {$user->name}");
        $this->line("  Email: {$user->email}");
        $this->line("  Username: {$user->username}");
        $this->line("  Roles: " . implode(', ', $user->getRoleNames()->toArray()));
        $this->line("  Permissions: " . ($user->getAllPermissions()->count() > 0 ? implode(', ', $user->getAllPermissions()->pluck('name')->toArray()) : 'None'));

        return 0;
    }
}
