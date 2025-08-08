<?php

namespace Noxomix\LaravelRollo\Events;

use Illuminate\Database\Eloquent\Model;

class RolesSynced
{
    /**
     * @param Model $model The subject model
     * @param array<int,int> $attached Role IDs attached in this sync (within optional context)
     * @param array<int,int> $detached Role IDs detached in this sync (within optional context)
     * @param int|null $contextId Optional context boundary used during sync
     */
    public function __construct(
        public Model $model,
        public array $attached,
        public array $detached,
        public ?int $contextId,
    ) {}
}

