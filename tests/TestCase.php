<?php

namespace Noxomix\LaravelRollo\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Noxomix\LaravelRollo\LaravelRolloServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelRolloServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}