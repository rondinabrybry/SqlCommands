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

        // Load routes automatically
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'sql-commands');

        // Publish views (optional, users can customize)
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/sql-commands'),
        ], 'views');

        // Publish example files for reference
        $this->publishes([
            __DIR__ . '/../../examples' => base_path('sql-practice-examples'),
        ], 'examples');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['sql-commands', 'sql-simulator'];
    }
}