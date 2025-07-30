<?php

namespace Noxomix\LaravelRollo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Noxomix\LaravelRollo\Models\RolloRole;
use Noxomix\LaravelRollo\Models\RolloContext;

trait HasRolloRoles
{
    /**
     * Get all roles for this model.
     *
     * @return MorphToMany
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            RolloRole::class,
            'model',
            'rollo_model_has_roles',
            'model_id',
            'role_id'
        );
    }

    /**
     * Assign a role to this model.
     *
     * @param RolloRole|string $role
     * @param RolloContext|int|null $context
     * @return void
     */
    public function assignRole($role, $context = null): void
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($role)) {
            $role = RolloRole::findByName($role, $contextId);
            if (!$role) {
                throw new \InvalidArgumentException("Role '{$role}' not found in the given context.");
            }
        }

        // Ensure the role belongs to the same context
        if ($contextId !== null && $role->context_id !== $contextId) {
            throw new \InvalidArgumentException("Role does not belong to the specified context.");
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Remove a role from this model.
     *
     * @param RolloRole|string $role
     * @param RolloContext|int|null $context
     * @return void
     */
    public function removeRole($role, $context = null): void
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($role)) {
            $role = RolloRole::findByName($role, $contextId);
            if (!$role) {
                return;
            }
        }

        $this->roles()->detach($role->id);
    }

    /**
     * Check if the model has a specific role.
     *
     * @param RolloRole|string $role
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasRole($role, $context = null): bool
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($role)) {
            return $this->roles()
                ->where('name', $role)
                ->when($contextId !== null, function ($query) use ($contextId) {
                    $query->where('context_id', $contextId);
                })
                ->exists();
        }

        return $this->roles()
            ->where('rollo_roles.id', $role->id)
            ->exists();
    }

    /**
     * Get all role names for this model.
     *
     * @param RolloContext|int|null $context
     * @return Collection
     */
    public function getRoleNames($context = null): Collection
    {
        $contextId = $this->resolveContextId($context);

        return $this->roles()
            ->when($contextId !== null, function ($query) use ($contextId) {
                $query->where('context_id', $contextId);
            })
            ->pluck('name');
    }

    /**
     * Sync roles for this model.
     *
     * @param array $roles Array of role names or IDs
     * @param RolloContext|int|null $context
     * @return void
     */
    public function syncRoles(array $roles, $context = null): void
    {
        $contextId = $this->resolveContextId($context);
        $roleIds = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $roleModel = RolloRole::findByName($role, $contextId);
                if ($roleModel) {
                    $roleIds[] = $roleModel->id;
                }
            } elseif (is_numeric($role)) {
                $roleIds[] = $role;
            } elseif ($role instanceof RolloRole) {
                $roleIds[] = $role->id;
            }
        }

        // If context is specified, only sync roles from that context
        if ($contextId !== null) {
            // Keep roles from other contexts
            $otherContextRoleIds = $this->roles()
                ->where('context_id', '!=', $contextId)
                ->pluck('rollo_roles.id')
                ->toArray();
            
            $roleIds = array_merge($roleIds, $otherContextRoleIds);
        }

        $this->roles()->sync($roleIds);
    }

    /**
     * Check if the model has any of the given roles.
     *
     * @param array $roles
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasAnyRole(array $roles, $context = null): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given roles.
     *
     * @param array $roles
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasAllRoles(array $roles, $context = null): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve context ID from various input types.
     *
     * @param mixed $context
     * @return int|null
     */
    protected function resolveContextId($context): ?int
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
            return $contextModel ? $contextModel->id : null;
        }

        throw new \InvalidArgumentException('Invalid context provided.');
    }
}