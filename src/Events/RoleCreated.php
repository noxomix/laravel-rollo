<?php

namespace Noxomix\LaravelRollo\Events;

use Noxomix\LaravelRollo\Models\RolloRole;

class RoleCreated
{
    public function __construct(
        public RolloRole $role
    ) {}
}

