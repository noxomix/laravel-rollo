<?php

namespace Noxomix\LaravelRollo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Noxomix\LaravelRollo\Models\RolloPermission;
use Noxomix\LaravelRollo\Models\RolloContext;

trait HasRolloPermissions
{
    use ResolvesRolloContext;
    /**
     * Get all direct permissions for this model.
     *
     * @return MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            RolloPermission::class,
            'model',
            'rollo_model_has_permissions',
            'model_id',
            'permission_id'
        )->withPivot('context_id');
    }

    /**
     * Give a permission to this model.
     *
     * @param RolloPermission|string $permission
     * @param RolloContext|int|null $context
     * @return void
     */
    public function givePermissionTo($permission, $context = null): void
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($permission)) {
            $permission = RolloPermission::findByName($permission);
            if (!$permission) {
                throw new \InvalidArgumentException("Permission '{$permission}' not found.");
            }
        }

        $this->permissions()->attach($permission->id, ['context_id' => $contextId]);
    }

    /**
     * Revoke a permission from this model.
     *
     * @param RolloPermission|string $permission
     * @param RolloContext|int|null $context
     * @return void
     */
    public function revokePermissionTo($permission, $context = null): void
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($permission)) {
            $permission = RolloPermission::findByName($permission);
            if (!$permission) {
                return;
            }
        }

        $query = $this->permissions()->where('permission_id', $permission->id);
        
        if ($contextId !== null) {
            $query->wherePivot('context_id', $contextId);
        }

        $query->detach();
    }

    /**
     * Check if the model has a specific direct permission.
     *
     * @param RolloPermission|string $permission
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasPermissionTo($permission, $context = null): bool
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($permission)) {
            $query = $this->permissions()->where('name', $permission);
        } else {
            $query = $this->permissions()->where('rollo_permissions.id', $permission->id);
        }

        if ($contextId !== null) {
            $query->wherePivot('context_id', $contextId);
        }

        return $query->exists();
    }

    /**
     * Get all direct permission names for this model.
     *
     * @param RolloContext|int|null $context
     * @return Collection
     */
    public function getPermissionNames($context = null): Collection
    {
        $contextId = $this->resolveContextId($context);

        return $this->permissions()
            ->when($contextId !== null, function ($query) use ($contextId) {
                $query->wherePivot('context_id', $contextId);
            })
            ->pluck('name');
    }

    /**
     * Sync permissions for this model.
     *
     * @param array $permissions Array of permission names or IDs
     * @param RolloContext|int|null $context
     * @return void
     */
    public function syncPermissions(array $permissions, $context = null): void
    {
        $contextId = $this->resolveContextId($context);
        $sync = [];

        foreach ($permissions as $permission) {
            $permissionId = null;

            if (is_string($permission)) {
                $permissionModel = RolloPermission::findByName($permission);
                if ($permissionModel) {
                    $permissionId = $permissionModel->id;
                }
            } elseif (is_numeric($permission)) {
                $permissionId = $permission;
            } elseif ($permission instanceof RolloPermission) {
                $permissionId = $permission->id;
            }

            if ($permissionId) {
                $sync[$permissionId] = ['context_id' => $contextId];
            }
        }

        // If context is specified, only sync permissions for that context
        if ($contextId !== null) {
            // Get current permissions for this context
            $currentForContext = $this->permissions()
                ->wherePivot('context_id', $contextId)
                ->pluck('rollo_permissions.id')
                ->toArray();

            // Detach current permissions for this context
            foreach ($currentForContext as $permId) {
                $this->permissions()->wherePivot('context_id', $contextId)->detach($permId);
            }

            // Attach new permissions
            foreach ($sync as $permId => $pivotData) {
                $this->permissions()->attach($permId, $pivotData);
            }
        } else {
            $this->permissions()->sync($sync);
        }
    }

    /**
     * Check if the model has any of the given direct permissions.
     *
     * @param array $permissions
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasAnyPermission(array $permissions, $context = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given direct permissions.
     *
     * @param array $permissions
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasAllPermissions(array $permissions, $context = null): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Revoke all permissions from this model.
     *
     * @param RolloContext|int|null $context
     * @return void
     */
    public function revokeAllPermissions($context = null): void
    {
        $contextId = $this->resolveContextId($context);

        if ($contextId !== null) {
            $this->permissions()->wherePivot('context_id', $contextId)->detach();
        } else {
            $this->permissions()->detach();
        }
    }
}