<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {email} {name} {--password=}';
    protected $description = 'Create a new admin user';

    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $password = $this->option('password') ?? 'Admin@123456';

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("❌ User with email '$email' already exists!");
            return 1;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'username' => strtolower(str_replace(' ', '', $name)),
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $user->assignRole('admin');

        $this->info("✅ Admin user created successfully!");
        $this->line("");
        $this->line("👑 New Admin Details:");
        $this->line("   ID: {$user->id}");
        $this->line("   Name: {$user->name}");
        $this->line("   Email: {$user->email}");
        $this->line("   Username: {$user->username}");
        $this->line("   Password: $password");
        $this->line("   Role: admin");
        $this->line("   Email Verified: ✅");
        $this->line("");
        $this->line("🔑 Test Login:");
        $this->line("   Username: {$user->username}");
        $this->line("   Password: $password");
        $this->line("");

        return 0;
    }
}
