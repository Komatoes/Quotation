<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Industry-standard password (12 chars: uppercase, lowercase, numbers, special chars)
        $password = 'SecurePass@2025!Qtn';

        // Create or update admin account
        $admin = User::updateOrCreate(
            ['email' => 'jomilo.lano@quotation.app'],
            [
                'name' => 'Jomilo',
                'last_name' => 'Lano',
                'username' => 'jomilo',
                'password' => Hash::make($password),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create or update staff account
        $staff = User::updateOrCreate(
            ['email' => 'redcrislan.toralde@quotation.app'],
            [
                'name' => 'Redcrislan',
                'last_name' => 'Toralde',
                'username' => 'redcrislan',
                'password' => Hash::make($password),
            ]
        );
        if (! $staff->hasRole('staff')) {
            $staff->assignRole('staff');
        }
    }
}
