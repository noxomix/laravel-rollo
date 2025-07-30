<?php

namespace Noxomix\LaravelRollo\Traits;

use Noxomix\LaravelRollo\Models\RolloContext;

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
        if ($context !== null) {
            if (is_numeric($context)) {
                return (int)$context;
            }

            if ($context instanceof RolloContext) {
                return $context->id;
            }

            if (is_object($context) && method_exists($context, 'getKey')) {
                $contextModel = RolloContext::findByModel($context);
                return $contextModel?->id;
            }

            throw new \InvalidArgumentException('Invalid context provided.');
        }
        return null;

    }
}