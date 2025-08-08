<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;

class PermissionsAssignedBatch
{
    public function __construct(
        public Model $model,
        public array $attachedIds,
        public ?int $contextId,
    ) {}
}

