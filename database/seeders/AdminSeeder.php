<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin accounts with proper roles and permissions
        $admins = [
            [
                'name' => 'Test Admin',
                'email' => 'laronvogn@gmail.com',
                'username' => 'ADMIN',
                'password' => 'ADMIN123',
                'role' => 'admin',
            ],
            [
                'name' => 'Nemo',
                'email' => 'blankgajes@gmail.com',
                'username' => 'nemo',
                'password' => 'Admin@123456',
                'role' => 'admin',
            ],
            [
                'name' => 'Ange',
                'email' => 'angelikamaslang@gmail.com',
                'username' => 'ange',
                'password' => 'Admin@123456',
                'role' => 'admin',
            ],
            [
                'name' => 'Mark',
                'email' => 'markandrebayo234@gmail.com',
                'username' => 'mark',
                'password' => 'Admin@123456',
                'role' => 'admin',
            ],
            [
                'name' => 'Jomilo',
                'email' => 'jomilo.lano@quotation.app',
                'username' => 'jomilo',
                'password' => 'Jomilo@123456',
                'role' => 'admin',
            ],
        ];

        foreach ($admins as $admin) {
            $user = User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'username' => $admin['username'],
                    'email' => $admin['email'],
                    'password' => Hash::make($admin['password']),
                    'role' => $admin['role'],
                    'email_verified_at' => now(),
                ]
            );

            // Assign Spatie admin role if using Spatie Laravel-Permission
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('admin');
                } catch (\Exception $e) {
                    echo "Note: Could not assign role to {$user->email}. Make sure roles are seeded first.\n";
                }
            }
        }

        echo "✅ Admin accounts seeded successfully!\n";
    }
}
