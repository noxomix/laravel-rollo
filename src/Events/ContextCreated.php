<?php

namespace Noxomix\LaravelRollo\Events;

use Noxomix\LaravelRollo\Models\RolloContext;

class ContextCreated
{
    public function __construct(
        public RolloContext $context
    ) {}
}

