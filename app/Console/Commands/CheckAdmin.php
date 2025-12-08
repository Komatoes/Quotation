<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAdmin extends Command
{
    protected $signature = 'admin:check';
    protected $description = 'Check admin user in database';

    public function handle()
    {
        $user = DB::table('users')->where('email', 'laronvogn@gmail.com')->first();
        
        if ($user) {
            $this->info('✅ Admin User Found!');
            $this->newLine();
            $this->info("ID: {$user->id}");
            $this->info("Username: {$user->username}");
            $this->info("Email: {$user->email}");
            $this->info("Role: {$user->role}");
            $this->newLine();
            $this->info('🚀 Test Credentials:');
            $this->line('   Username: ADMIN');
            $this->line('   Password: ADMIN123');
            $this->line('   Email: laronvogn@gmail.com');
        } else {
            $this->error('❌ Admin user not found');
        }
    }
}
