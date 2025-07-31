<?php

namespace Noxomix\LaravelRollo;

use Illuminate\Support\ServiceProvider;
use Noxomix\LaravelRollo\Commands\RolloCacheResetCommand;
use Noxomix\LaravelRollo\Commands\RolloSetupCommand;
use Noxomix\LaravelRollo\Commands\RolloAuditCleanupCommand;

class LaravelRolloServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/rollo.php' => config_path('rollo.php'),
            ], 'rollo-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'rollo-migrations');

            // Register commands
            $this->commands([
                RolloSetupCommand::class,
                RolloAuditCleanupCommand::class,
                // RolloCacheResetCommand::class, // Wird später implementiert
            ]);
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/rollo.php', 'rollo'
        );

        // Register singleton
        $this->app->singleton('rollo', function ($app) {
            return new Rollo();
        });

        // Register audit service
        $this->app->singleton('rollo.audit', function ($app) {
            return new \Noxomix\LaravelRollo\Services\RolloAuditService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['rollo'];
    }
}