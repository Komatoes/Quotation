<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyPasswordResets extends Command
{
    protected $signature = 'db:verify-password-resets';
    protected $description = 'Verify password_resets table structure';

    public function handle()
    {
        try {
            // Check if table exists
            if (!DB::connection()->getSchemaBuilder()->hasTable('password_resets')) {
                $this->error('❌ password_resets table does not exist!');
                return 1;
            }

            $this->info('✅ password_resets table exists!');
            $this->newLine();

            // Get table columns
            $columns = DB::select("DESCRIBE password_resets");
            
            $this->info('Table Columns:');
            $this->table(
                ['Field', 'Type', 'Null', 'Key', 'Default'],
                array_map(function ($col) {
                    return [
                        $col->Field,
                        $col->Type,
                        $col->Null,
                        $col->Key,
                        $col->Default ?? 'NULL'
                    ];
                }, $columns)
            );

            $this->newLine();

            // Check required columns
            $requiredColumns = ['email', 'token', 'otp', 'otp_verified', 'otp_expires_at'];
            $hasAllColumns = true;

            foreach ($requiredColumns as $col) {
                $exists = DB::connection()->getSchemaBuilder()->hasColumn('password_resets', $col);
                if ($exists) {
                    $this->line("✅ {$col}");
                } else {
                    $this->error("❌ {$col} - MISSING!");
                    $hasAllColumns = false;
                }
            }

            $this->newLine();

            if ($hasAllColumns) {
                $this->info('✅ All required columns are present!');
                $this->info('✅ OTP system is ready to use!');
                return 0;
            } else {
                $this->error('❌ Some columns are missing. Run: php artisan migrate');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
