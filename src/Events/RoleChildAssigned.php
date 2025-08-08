<?php

namespace Noxomix\LaravelRollo\Events;

use Noxomix\LaravelRollo\Models\RolloRole;

class RoleChildAssigned
{
    public function __construct(
        public RolloRole $parent,
        public RolloRole $child,
    ) {}
}

