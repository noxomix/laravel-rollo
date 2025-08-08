<?php

namespace Noxomix\LaravelRollo\Traits;

use Noxomix\LaravelRollo\Support\ContextResolver;

trait ResolvesRolloContext
{
    /**
     * Resolve context ID from various input types.
     *
     * @param mixed $context
     * @return int|null
     */
    protected function resolveContextId(mixed $context): ?int
    {
        return ContextResolver::resolveContextId($context);
    }
}

