<?php

namespace Noxomix\LaravelRollo;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Noxomix\LaravelRollo\Commands\RolloCacheResetCommand;
use Noxomix\LaravelRollo\Policies\RolloPolicy;

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
                // RolloCacheResetCommand::class, // Wird später implementiert
            ]);
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register authorization gates
        $this->registerGates();
    }

    /**
     * Register authorization gates.
     *
     * @return void
     */
    protected function registerGates(): void
    {
        $policy = new RolloPolicy();

        Gate::define('rollo.manage', [$policy, 'manage']);
        Gate::define('rollo.assignPermission', [$policy, 'assignPermission']);
        Gate::define('rollo.revokePermission', [$policy, 'revokePermission']);
        Gate::define('rollo.assignRole', [$policy, 'assignRole']);
        Gate::define('rollo.revokeRole', [$policy, 'revokeRole']);
        Gate::define('rollo.createPermission', [$policy, 'createPermission']);
        Gate::define('rollo.createRole', [$policy, 'createRole']);
        Gate::define('rollo.deletePermission', [$policy, 'deletePermission']);
        Gate::define('rollo.deleteRole', [$policy, 'deleteRole']);
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