<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users with their roles and permissions';

    public function handle()
    {
        $users = DB::table('users')->get();

        if ($users->isEmpty()) {
            $this->error("❌ No users found in the database!");
            return 1;
        }

        $this->line("");
        $this->line("╔════════════════════════════════════════════════════════════════════════════════╗");
        $this->line("║                           USERS & ROLES OVERVIEW                              ║");
        $this->line("╚════════════════════════════════════════════════════════════════════════════════╝");
        $this->line("");

        foreach ($users as $user) {
            $roleIcon = $user->role === 'admin' ? '👑' : '👤';
            $emailVerified = $user->email_verified_at ? '✅' : '❌';

            $this->line("$roleIcon User ID: {$user->id}");
            $this->line("   Name:             {$user->name}");
            $this->line("   Username:         {$user->username}");
            $this->line("   Email:            {$user->email}");
            $this->line("   Role:             {$user->role}");
            $this->line("   Email Verified:   $emailVerified");
            $this->line("   Created:          {$user->created_at}");
            $this->line("");
        }

        $this->line("╔════════════════════════════════════════════════════════════════════════════════╗");
        $this->line("║                            ROLE SUMMARY                                       ║");
        $this->line("╚════════════════════════════════════════════════════════════════════════════════╝");
        $this->line("");

        $roleCount = DB::table('users')->groupBy('role')->selectRaw('role, count(*) as total')->get();

        foreach ($roleCount as $role) {
            $icon = $role->role === 'admin' ? '👑' : '👤';
            $this->line("$icon {$role->role}: {$role->total} user(s)");
        }

        $this->line("");
        $this->info("✅ Total Users: " . $users->count());
        $this->line("");

        return 0;
    }
}
