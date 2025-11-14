<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AssignUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {user_id} {role : The role name (admin or staff)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user (admin or staff)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $role = strtolower($this->argument('role'));

        // Validate role
        if (!in_array($role, ['admin', 'staff'])) {
            $this->error('❌ Invalid role. Use: admin or staff');
            return self::FAILURE;
        }

        // Find user
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found.");
            return self::FAILURE;
        }

        // Assign role
        $user->syncRoles([$role]);

        $this->info("✅ Role '{$role}' assigned to user '{$user->name}' (ID: {$user->id})");
        return self::SUCCESS;
    }
}
