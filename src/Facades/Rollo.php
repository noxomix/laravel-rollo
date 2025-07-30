<?php

namespace Noxomix\LaravelRollo\Facades;

use Illuminate\Support\Facades\Facade;

class Rollo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Noxomix\LaravelRollo\Rollo::class;
    }
}