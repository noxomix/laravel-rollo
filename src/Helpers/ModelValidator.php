<?php

namespace Noxomix\LaravelRollo\Helpers;

use Illuminate\Database\Eloquent\Model;

class ModelValidator
{
    /**
     * Validate if a model class is allowed to use Rollo.
     *
     * @param string $modelClass
     * @throws \InvalidArgumentException
     * @return void
     */
    public static function validateModelClass(string $modelClass): void
    {
        // Check if class exists
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class '{$modelClass}' does not exist.");
        }
        
        // Check against allowed models whitelist BEFORE any instantiation
        $allowedModels = config('rollo.allowed_models');
        if ($allowedModels !== null && !in_array($modelClass, $allowedModels)) {
            throw new \InvalidArgumentException("Model class '{$modelClass}' is not allowed to use Rollo.");
        }

        // Check if it's an Eloquent model (without instantiation)
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException("Class '{$modelClass}' is not an Eloquent model.");
        }
    }

    /**
     * Sanitize and validate table name to prevent SQL injection.
     *
     * @param string $tableName
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function sanitizeTableName(string $tableName): string
    {
        // Only allow alphanumeric characters, underscores, and dots (for database.table format)
        if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $tableName)) {
            throw new \InvalidArgumentException("Invalid table name format: '{$tableName}'");
        }
        
        return $tableName;
    }
}
