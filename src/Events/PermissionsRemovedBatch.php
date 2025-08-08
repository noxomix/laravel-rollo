<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;

class PermissionsRemovedBatch
{
    public function __construct(
        public Model $model,
        public array $detachedIds,
        public ?int $contextId,
    ) {}
}

