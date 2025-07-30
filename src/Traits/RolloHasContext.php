<?php

namespace Noxomix\LaravelRollo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Noxomix\LaravelRollo\Models\RolloContext;

trait RolloHasContext
{
    /**
     * Get the Rollo context for this model.
     *
     * @return MorphOne
     */
    public function rolloContext(): MorphOne
    {
        return $this->morphOne(RolloContext::class, 'contextable');
    }

    /**
     * Create a Rollo context for this model.
     *
     * @param string|null $name
     * @return RolloContext
     */
    public function createRolloContext(?string $name = null): RolloContext
    {
        // Delete existing context if any
        $this->rolloContext()->delete();

        // Create new context
        return $this->rolloContext()->create([
            'name' => $name ?? $this->getContextName(),
        ]);
    }

    /**
     * Get or create the Rollo context for this model.
     *
     * @param string|null $name
     * @return RolloContext
     */
    public function getRolloContext(?string $name = null): RolloContext
    {
        $context = $this->rolloContext;

        if (!$context) {
            $context = $this->createRolloContext($name);
        }

        return $context;
    }

    /**
     * Get the default context name for this model.
     *
     * @return string
     */
    protected function getContextName(): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getKey();

        return "{$modelName} {$identifier}";
    }

    /**
     * Check if this model has a Rollo context.
     *
     * @return bool
     */
    public function hasRolloContext(): bool
    {
        return $this->rolloContext()->exists();
    }

    /**
     * Delete the Rollo context for this model.
     *
     * @return void
     */
    public function deleteRolloContext(): void
    {
        $this->rolloContext()->delete();
    }

    /**
     * Get all roles defined in this context.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContextRoles()
    {
        $context = $this->getRolloContext();
        return $context->roles;
    }

    /**
     * Get all permissions assigned in this context.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getContextPermissions()
    {
        $context = $this->getRolloContext();
        return $context->permissions();
    }

    /**
     * Get all models with roles in this context.
     *
     * @param string $modelClass
     * @return \Illuminate\Support\Collection
     */
    public function getModelsWithRolesInContext(string $modelClass)
    {
        $context = $this->getRolloContext();
        return $context->modelsWithRoles($modelClass);
    }

    /**
     * Get all models with permissions in this context.
     *
     * @param string $modelClass
     * @return \Illuminate\Support\Collection
     */
    public function getModelsWithPermissionsInContext(string $modelClass)
    {
        $context = $this->getRolloContext();
        return $context->modelsWithPermissions($modelClass);
    }

    /**
     * Create a role in this context.
     *
     * @param string $name
     * @param array $attributes
     * @return \Noxomix\LaravelRollo\Models\RolloRole
     */
    public function createRoleInContext(string $name, array $attributes = [])
    {
        $context = $this->getRolloContext();
        
        return $context->roles()->create(array_merge([
            'name' => $name,
        ], $attributes));
    }

    /**
     * Find a role by name in this context.
     *
     * @param string $name
     * @return \Noxomix\LaravelRollo\Models\RolloRole|null
     */
    public function findRoleInContext(string $name)
    {
        $context = $this->getRolloContext();
        
        return $context->roles()->where('name', $name)->first();
    }

    /**
     * Delete all roles in this context.
     *
     * @return void
     */
    public function deleteAllRolesInContext(): void
    {
        $context = $this->getRolloContext();
        $context->roles()->delete();
    }
}