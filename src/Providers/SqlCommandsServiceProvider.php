<?php

namespace SqlCommands\Providers;

use Illuminate\Support\ServiceProvider;
use SqlCommands\SqlCommands;
use SqlCommands\SqlSimulator;

/**
 * Laravel Service Provider for SQL Commands Package
 */
class SqlCommandsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('sql-commands', function ($app) {
            return new SqlCommands();
        });

        $this->app->bind('sql-simulator', function ($app) {
            $databasePath = config('sql-commands.practice_database_path', database_path('practice.sqlite'));
            return new SqlSimulator($databasePath);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../config/sql-commands.php' => config_path('sql-commands.php'),
        ], 'config');

        // Publish migration files
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Load routes for web interface (optional)
        if ($this->app->runningInConsole() === false) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'sql-commands');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/sql-commands'),
        ], 'views');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['sql-commands', 'sql-simulator'];
    }
}