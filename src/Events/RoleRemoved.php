<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;
use Noxomix\LaravelRollo\Models\RolloRole;

class RoleRemoved
{
    public function __construct(
        public Model $model,
        public RolloRole $role
    ) {}
}

