<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;

class PermissionsSynced
{
    /**
     * @param Model $model The subject model
     * @param array<int,int> $attached Permission IDs attached in this sync (within optional context)
     * @param array<int,int> $detached Permission IDs detached in this sync (within optional context)
     * @param int|null $contextId Optional context boundary used during sync
     */
    public function __construct(
        public Model $model,
        public array $attached,
        public array $detached,
        public ?int $contextId,
    ) {}
}

