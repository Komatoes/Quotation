<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create test admin user if it doesn't exist
        $testAdmin = User::firstOrCreate(
            ['email' => 'laronvogn@gmail.com'],
            [
                'name' => 'Test Admin',
                'username' => 'ADMIN',
                'email' => 'laronvogn@gmail.com',
                'password' => Hash::make('ADMIN123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the test admin user
        User::where('email', 'laronvogn@gmail.com')->delete();
    }
};
