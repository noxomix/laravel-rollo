<?php

namespace Noxomix\LaravelRollo\Tests;

use Noxomix\LaravelRollo\Rollo as RolloService;
use Noxomix\LaravelRollo\Facades\Rollo as RolloFacade;

class ExampleTest extends TestCase
{
    public function test_service_is_bound_in_container(): void
    {
        $service = app('rollo');
        $this->assertInstanceOf(RolloService::class, $service);
    }

    public function test_facade_resolves_service(): void
    {
        $this->assertInstanceOf(RolloService::class, RolloFacade::getFacadeRoot());
    }
}
