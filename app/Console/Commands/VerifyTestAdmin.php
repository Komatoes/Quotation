<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyTestAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify test admin user exists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admin = User::where('email', 'laronvogn@gmail.com')->first();

        if ($admin) {
            $this->info('✅ Test Admin User Found!');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $admin->id],
                    ['Name', $admin->name],
                    ['Username', $admin->username],
                    ['Email', $admin->email],
                    ['Role', $admin->role ?? 'Not Set'],
                    ['Created At', $admin->created_at],
                ]
            );
            $this->newLine();
            $this->info('🚀 You can now login with:');
            $this->line('   Username: ADMIN');
            $this->line('   Password: ADMIN123');
        } else {
            $this->error('❌ Test Admin User Not Found!');
            $this->line('Please run: php artisan migrate');
        }
    }
}
