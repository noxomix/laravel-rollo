<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;
use Noxomix\LaravelRollo\Models\RolloPermission;

class PermissionRemoved
{
    public function __construct(
        public Model $model,
        public RolloPermission $permission,
        public ?int $contextId
    ) {}
}

