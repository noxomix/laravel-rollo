<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;

class RolesAssignedBatch
{
    public function __construct(
        public Model $model,
        public array $attachedIds,
        public ?int $contextId,
    ) {}
}

