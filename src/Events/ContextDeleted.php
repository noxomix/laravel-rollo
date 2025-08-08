<?php

namespace Noxomix\LaravelRollo\Events;

use Noxomix\LaravelRollo\Models\RolloContext;

class ContextDeleted
{
    public function __construct(
        public RolloContext $context
    ) {}
}

