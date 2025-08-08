<?php

namespace Noxomix\LaravelRollo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Noxomix\LaravelRollo\Models\RolloRole;
use Noxomix\LaravelRollo\Models\RolloContext;
use Noxomix\LaravelRollo\Validators\RolloValidator;
use Noxomix\LaravelRollo\Events\RoleAssigned;
use Noxomix\LaravelRollo\Events\RoleRemoved;

trait HasRolloRoles
{
    use ResolvesRolloContext;
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
            $roleName = $role;
            // Validate role name
            RolloValidator::validateRoleName($roleName);
            $role = RolloRole::findByName($roleName, $contextId);
            if (!$role) {
                throw new \InvalidArgumentException("Role '{$roleName}' not found in the given context.");
            }
        }

        // Ensure the role belongs to the same context
        if ($contextId !== null && $role->context_id !== $contextId) {
            throw new \InvalidArgumentException("Role does not belong to the specified context.");
        }

        // Check if role is already assigned
        if (!$this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
            event(new RoleAssigned($this, $role));
        }
    }

    /**
     * Assign multiple roles to this model.
     *
     * @param array $roles Array of role names, IDs or models
     * @param RolloContext|int|null $context
     * @return void
     */
    public function assignRoles(array $roles, $context = null): void
    {
        foreach ($roles as $role) {
            $this->assignRole($role, $context);
        }
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
            $roleName = $role;
            $role = RolloRole::findByName($roleName, $contextId);
            if (!$role) {
                return;
            }
        }

        // Check if role is assigned before detaching
        if ($this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->detach($role->id);
            event(new RoleRemoved($this, $role));
        }
    }

    /**
     * Remove multiple roles from this model.
     *
     * @param array $roles Array of role names, IDs or models
     * @param RolloContext|int|null $context
     * @return void
     */
    public function removeRoles(array $roles, $context = null): void
    {
        foreach ($roles as $role) {
            $this->removeRole($role, $context);
        }
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

        if ($contextId !== null) {
            // Current roles within this context
            $currentForContext = $this->roles()
                ->where('context_id', $contextId)
                ->pluck('rollo_roles.id')
                ->toArray();

            // Keep roles from other contexts
            $otherContextRoleIds = $this->roles()
                ->where('context_id', '!=', $contextId)
                ->pluck('rollo_roles.id')
                ->toArray();

            $newForContext = array_values(array_unique($roleIds));
            $attached = array_values(array_diff($newForContext, $currentForContext));
            $detached = array_values(array_diff($currentForContext, $newForContext));

            // Merge for actual sync
            $finalIds = array_merge($newForContext, $otherContextRoleIds);
            $this->roles()->sync($finalIds);
            event(new \Noxomix\LaravelRollo\Events\RolesSynced($this, $attached, $detached, $contextId));
            return;
        }

        // No context filter: compute overall delta
        $currentAll = $this->roles()->pluck('rollo_roles.id')->toArray();
        $newAll = array_values(array_unique($roleIds));
        $attached = array_values(array_diff($newAll, $currentAll));
        $detached = array_values(array_diff($currentAll, $newAll));

        $this->roles()->sync($newAll);
        event(new \Noxomix\LaravelRollo\Events\RolesSynced($this, $attached, $detached, null));
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

}
