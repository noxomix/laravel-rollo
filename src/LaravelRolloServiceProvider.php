<?php

namespace Noxomix\LaravelRollo;

use Illuminate\Support\ServiceProvider;
use Noxomix\LaravelRollo\Commands\RolloSetupCommand;

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
