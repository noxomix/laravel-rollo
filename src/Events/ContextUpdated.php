<?php

namespace Noxomix\LaravelRollo\Events;

use Noxomix\LaravelRollo\Models\RolloContext;

class ContextUpdated
{
    public function __construct(
        public RolloContext $context
    ) {}
}

