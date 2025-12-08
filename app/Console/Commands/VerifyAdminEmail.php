<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyAdminEmail extends Command
{
    protected $signature = 'admin:verify-email {email}';
    protected $description = 'Mark email as verified for a user';

    public function handle()
    {
        $email = $this->argument('email');

        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User with email '$email' not found!");
            return 1;
        }

        DB::table('users')->where('email', $email)->update(['email_verified_at' => now()]);

        $updatedUser = DB::table('users')->where('email', $email)->first();

        $this->info("✅ Email verified successfully!");
        $this->line("");
        $this->line("User Details:");
        $this->line("  ID: {$updatedUser->id}");
        $this->line("  Name: {$updatedUser->name}");
        $this->line("  Email: {$updatedUser->email}");
        $this->line("  Username: {$updatedUser->username}");
        $this->line("  Role: {$updatedUser->role}");
        $this->line("  Email Verified: Yes ✅");

        return 0;
    }
}
