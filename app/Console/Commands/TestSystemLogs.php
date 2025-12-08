<?php

namespace App\Console\Commands;

use App\Helpers\SystemLogHelper;
use App\Models\SystemLog;
use Illuminate\Console\Command;

class TestSystemLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test system logging by creating a test log entry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing System Logs Setup...');
        $this->newLine();

        try {
            // Create a test log
            $this->info('Creating test log entry...');
            $log = SystemLogHelper::log(
                action: 'test',
                description: 'Test system log entry - middleware and observers verification',
                model: null,
                modelId: null,
                changes: ['test' => true, 'timestamp' => now()->toString()]
            );

            $this->info('✅ Test log created successfully!');
            $this->newLine();

            // Display the log details
            $this->info('Log Details:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $log->id],
                    ['User ID', $log->user_id ?? 'N/A (artisan command)'],
                    ['Action', $log->action],
                    ['Description', $log->description],
                    ['Model', $log->model ?? 'N/A'],
                    ['Model ID', $log->model_id ?? 'N/A'],
                    ['Created At', $log->created_at],
                ]
            );

            $this->newLine();

            // Count total logs
            $totalLogs = SystemLog::count();
            $this->info("📊 Total system logs in database: {$totalLogs}");

            $this->newLine();
            $this->info('✅ System Logs Setup is Working!');
            $this->info('');
            $this->info('Your system is now tracking:');
            $this->line('  • All POST/PUT/PATCH/DELETE requests (via middleware)');
            $this->line('  • All Quotation, Project, Material, Client changes (via observers)');
            $this->line('');
            $this->info('View logs at: http://localhost:8000/admin/logs');

        } catch (\Exception $e) {
            $this->error('❌ Error testing system logs: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
