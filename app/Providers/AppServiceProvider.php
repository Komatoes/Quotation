<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register Spatie Backup cleanup strategy for the service container
        // This resolves the "Target [Spatie\Backup\Tasks\Cleanup\CleanupStrategy] is not instantiable" error
        if (class_exists(\Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class)) {
            $this->app->singleton(
                \Spatie\Backup\Tasks\Cleanup\CleanupStrategy::class,
                \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class
            );
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Configure db-dumper for XAMPP on Windows by setting environment variables
        // db-dumper will use these to find mysqldump and mysql executables
        if (PHP_OS_FAMILY === 'Windows') {
            $mysqlBinPath = 'C:\\xampp\\mysql\\bin';
            putenv("PATH=" . $mysqlBinPath . ";" . getenv('PATH'));
        }
    }
}
