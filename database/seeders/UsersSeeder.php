<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Admin accounts
        $admins = [
            [
                'name' => 'Test Admin',
                'last_name' => '',
                'email' => 'laronvogn@gmail.com',
                'username' => 'ADMIN',
                'password' => 'ADMIN123',
            ],
            [
                'name' => 'Nemo',
                'last_name' => '',
                'email' => 'blankgajes@gmail.com',
                'username' => 'nemo',
                'password' => 'Admin@123456',
            ],
            [
                'name' => 'Ange',
                'last_name' => '',
                'email' => 'angelikamaslang@gmail.com',
                'username' => 'ange',
                'password' => 'Admin@123456',
            ],
            [
                'name' => 'Mark',
                'last_name' => '',
                'email' => 'markandrebayo234@gmail.com',
                'username' => 'mark',
                'password' => 'Admin@123456',
            ],
            [
                'name' => 'Jomilo',
                'last_name' => 'Lano',
                'email' => 'jomilo.lano@quotation.app',
                'username' => 'jomilo',
                'password' => 'Jomilo@123456',
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = User::updateOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'last_name' => $adminData['last_name'],
                    'username' => $adminData['username'],
                    'password' => Hash::make($adminData['password']),
                ]
            );
            if (! $admin->hasRole('admin')) {
                $admin->assignRole('admin');
            }
        }

        // Create or update staff account
        $staff = User::updateOrCreate(
            ['email' => 'redcrislan.toralde@quotation.app'],
            [
                'name' => 'Redcrislan',
                'last_name' => 'Toralde',
                'username' => 'redcrislan',
                'password' => Hash::make('SecurePass@2025!Qtn'),
            ]
        );
        if (! $staff->hasRole('staff')) {
            $staff->assignRole('staff');
        }
    }
}
