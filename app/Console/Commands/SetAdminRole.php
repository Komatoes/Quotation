<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetAdminRole extends Command
{
    protected $signature = 'admin:set-role {email}';
    protected $description = 'Set admin role for a user by email';

    public function handle()
    {
        $email = $this->argument('email');

        // Check if user exists
        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User with email '$email' not found!");
            return 1;
        }

        // Update role to admin
        DB::table('users')->where('email', $email)->update(['role' => 'admin']);

        $updatedUser = DB::table('users')->where('email', $email)->first();

        $this->info("✅ Role updated successfully!");
        $this->line("");
        $this->line("User Details:");
        $this->line("  ID: {$updatedUser->id}");
        $this->line("  Name: {$updatedUser->name}");
        $this->line("  Email: {$updatedUser->email}");
        $this->line("  Username: {$updatedUser->username}");
        $this->line("  Role: {$updatedUser->role}");
        $this->line("  Email Verified: " . ($updatedUser->email_verified_at ? 'Yes' : 'No'));

        return 0;
    }
}
