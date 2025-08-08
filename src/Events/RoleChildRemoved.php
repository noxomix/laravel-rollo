<?php

namespace Noxomix\LaravelRollo\Events;

use Noxomix\LaravelRollo\Models\RolloRole;

class RoleChildRemoved
{
    public function __construct(
        public RolloRole $parent,
        public RolloRole $child,
    ) {}
}

