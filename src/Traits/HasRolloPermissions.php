<?php

namespace Noxomix\LaravelRollo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Noxomix\LaravelRollo\Models\RolloPermission;
use Noxomix\LaravelRollo\Models\RolloContext;
use Noxomix\LaravelRollo\Validators\RolloValidator;

trait HasRolloPermissions
{
    use ResolvesRolloContext, AuthorizesRolloActions;
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
     * Assign a permission to this model.
     *
     * @param RolloPermission|string $permission
     * @param RolloContext|int|null $context
     * @return void
     */
    public function assignPermission($permission, $context = null): void
    {
        // Authorize the action
        $this->authorizeRolloAction('assignPermission', $this, $permission);
        
        $contextId = $this->resolveContextId($context);

        if (is_string($permission)) {
            $permissionName = $permission;
            // Validate permission name
            RolloValidator::validatePermissionName($permissionName);
            $permission = RolloPermission::findByName($permissionName);
            if (!$permission) {
                throw new \InvalidArgumentException("Permission '{$permissionName}' not found.");
            }
        }

        // Check if this exact permission-context combination already exists
        $existingQuery = $this->permissions()->where('permission_id', $permission->id);
        
        if ($contextId !== null) {
            $existingQuery->wherePivot('context_id', $contextId);
        } else {
            $existingQuery->wherePivotNull('context_id');
        }
        
        // Only attach if it doesn't already exist
        if (!$existingQuery->exists()) {
            $this->permissions()->attach($permission->id, ['context_id' => $contextId]);
        }
    }

    /**
     * Assign multiple permissions to this model.
     *
     * @param array $permissions Array of permission names, IDs or models
     * @param RolloContext|int|null $context
     * @return void
     */
    public function assignPermissions(array $permissions, $context = null): void
    {
        foreach ($permissions as $permission) {
            // Skip null or empty values
            if (empty($permission)) {
                continue;
            }
            
            try {
                $this->assignPermission($permission, $context);
            } catch (\InvalidArgumentException $e) {
                // Skip invalid permissions in batch operations
                continue;
            }
        }
    }

    /**
     * Remove a permission from this model.
     *
     * @param RolloPermission|string $permission
     * @param RolloContext|int|null $context
     * @return void
     */
    public function removePermission($permission, $context = null): void
    {
        // Authorize the action
        $this->authorizeRolloAction('revokePermission', $this);
        
        $contextId = $this->resolveContextId($context);

        if (is_string($permission)) {
            $permissionName = $permission;
            $permission = RolloPermission::findByName($permissionName);
            if (!$permission) {
                return;
            }
        }

        $query = $this->permissions()->where('permission_id', $permission->id);
        
        if ($contextId !== null) {
            $query->wherePivot('context_id', $contextId);
        } else {
            $query->wherePivotNull('context_id');
        }

        $query->detach();
    }

    /**
     * Remove multiple permissions from this model.
     *
     * @param array $permissions Array of permission names, IDs or models
     * @param RolloContext|int|null $context
     * @return void
     */
    public function removePermissions(array $permissions, $context = null): void
    {
        foreach ($permissions as $permission) {
            $this->removePermission($permission, $context);
        }
    }

    /**
     * Check if the model has a specific direct permission.
     *
     * @param RolloPermission|string $permission
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function hasPermission($permission, $context = null): bool
    {
        $contextId = $this->resolveContextId($context);

        if (is_string($permission)) {
            $query = $this->permissions()->where('rollo_permissions.name', $permission);
        } else {
            $query = $this->permissions()->where('rollo_permissions.id', $permission->id);
        }

        if ($contextId !== null) {
            $query->wherePivot('context_id', $contextId);
        } else {
            $query->wherePivotNull('context_id');
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
            if ($this->hasPermission($permission, $context)) {
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
            if (!$this->hasPermission($permission, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove all permissions from this model.
     *
     * @param RolloContext|int|null $context
     * @return void
     */
    public function removeAllPermissions($context = null): void
    {
        $contextId = $this->resolveContextId($context);

        if ($contextId !== null) {
            $this->permissions()->wherePivot('context_id', $contextId)->detach();
        } else {
            $this->permissions()->detach();
        }
    }

    /**
     * Check if the model can perform a specific action (permission).
     * This checks both direct permissions and permissions through roles.
     *
     * @param string $permission
     * @param RolloContext|int|null $context
     * @return bool
     */
    public function canPerform(string $permission, $context = null): bool
    {
        return app('rollo')->can($this, $permission, $context);
    }
}