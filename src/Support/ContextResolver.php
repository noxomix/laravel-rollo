<?php

namespace Noxomix\LaravelRollo\Support;

use Noxomix\LaravelRollo\Models\RolloContext;

class ContextResolver
{
    public static function resolveContextId(mixed $context): ?int
    {
        if ($context === null) {
            return null;
        }

        if (is_numeric($context)) {
            return (int) $context;
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
}

