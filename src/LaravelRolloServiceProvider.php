<?php

namespace Noxomix\LaravelRollo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Noxomix\LaravelRollo\Commands\RolloInstallCommand;

class LaravelRolloServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-rollo')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_rollo_table')
            ->hasRoute('web')
            ->hasCommand(RolloInstallCommand::class);
    }
}