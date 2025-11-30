<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Spatie Backup: schedule backup and cleanup
        // Note: requires `spatie/laravel-backup` to be installed via composer
        // Run full backup daily at 02:00
        $schedule->command('backup:run')->dailyAt('02:00')->withoutOverlapping();

        // Run cleanup daily at 03:00 to prune old backups
        $schedule->command('backup:clean')->dailyAt('03:00')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
