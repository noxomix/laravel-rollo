<?php

namespace Noxomix\LaravelRollo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Noxomix\LaravelRollo\Models\RolloContext;
use Noxomix\LaravelRollo\Models\RolloPermission;
use Noxomix\LaravelRollo\Models\RolloRole;

class Rollo
{
    /**
     * Check if a model can perform a specific action (permission).
     * This checks both direct permissions and permissions through roles (with recursive inheritance).
     *
     * @param Model $model
     * @param string $permissionName
     * @param mixed|null $context
     * @return bool
     */
    public function can(Model $model, string $permissionName, mixed $context = null): bool
    {
        $contextId = $this->resolveContextId($context);

        // Check direct permissions
        if ($this->hasDirectPermission($model, $permissionName, $contextId)) {
            return true;
        }

        // Check permissions through roles (with recursive inheritance)
        return $this->hasPermissionThroughRoles($model, $permissionName, $contextId);
    }

    /**
     * Alias for can() method for backward compatibility.
     *
     * @param Model $model
     * @param string $permissionName
     * @param mixed|null $context
     * @return bool
     */
    public function has(Model $model, string $permissionName, mixed $context = null): bool
    {
        return $this->can($model, $permissionName, $context);
    }

    /**
     * Get all effective permissions for a model.
     * This includes direct permissions and permissions through roles (with recursive inheritance).
     *
     * @param Model $model
     * @param mixed|null $context
     * @return Collection
     */
    public function permissionsFor(Model $model, mixed $context = null): Collection
    {
        $contextId = $this->resolveContextId($context);
        
        // Get direct permissions
        $directPermissions = $this->getDirectPermissions($model, $contextId);
        
        // Get permissions through roles
        $rolePermissions = $this->getPermissionsThroughRoles($model, $contextId);
        
        // Merge and return unique permissions
        return $directPermissions->merge($rolePermissions)->unique('id');
    }

    /**
     * Get all effective roles for a model.
     * This includes directly assigned roles and inherited roles through recursive resolution.
     *
     * @param Model $model
     * @param mixed $context
     * @return Collection
     */
    public function rolesFor(Model $model, $context = null): Collection
    {
        $contextId = $this->resolveContextId($context);
        
        // Get directly assigned roles
        $directRoles = $this->getDirectRoles($model, $contextId);
        
        // Resolve all inherited roles recursively
        $allRoles = collect();
        $resolved = [];
        
        foreach ($directRoles as $role) {
            $this->resolveRoleHierarchy($role, $resolved, $allRoles);
        }
        
        return $allRoles;
    }


    /**
     * Check if model has direct permission.
     *
     * @param Model $model
     * @param string $permissionName
     * @param int|null $contextId
     * @return bool
     */
    protected function hasDirectPermission(Model $model, string $permissionName, ?int $contextId): bool
    {
        if (!method_exists($model, 'permissions')) {
            return false;
        }

        $query = $model->permissions()->where('name', $permissionName);
        
        if ($contextId !== null) {
            $query->wherePivot('context_id', $contextId);
        }
        
        return $query->exists();
    }

    /**
     * Check if model has permission through roles.
     *
     * @param Model $model
     * @param string $permissionName
     * @param int|null $contextId
     * @return bool
     */
    protected function hasPermissionThroughRoles(Model $model, string $permissionName, ?int $contextId): bool
    {
        if (!method_exists($model, 'roles')) {
            return false;
        }

        // Get all roles (including inherited ones)
        $allRoles = $this->rolesFor($model, $contextId);
        
        // Check if any role has the permission
        foreach ($allRoles as $role) {
            if ($role->permissions()
                ->where('name', $permissionName)
                ->when($contextId !== null, function ($query) use ($contextId) {
                    $query->wherePivot('context_id', $contextId);
                })
                ->exists()) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get direct permissions for a model.
     *
     * @param Model $model
     * @param int|null $contextId
     * @return Collection
     */
    protected function getDirectPermissions(Model $model, ?int $contextId): Collection
    {
        if (!method_exists($model, 'permissions')) {
            return collect();
        }

        return $model->permissions()
            ->when($contextId !== null, function ($query) use ($contextId) {
                $query->wherePivot('context_id', $contextId);
            })
            ->get();
    }

    /**
     * Get permissions through roles for a model.
     *
     * @param Model $model
     * @param int|null $contextId
     * @return Collection
     */
    protected function getPermissionsThroughRoles(Model $model, ?int $contextId): Collection
    {
        $allRoles = $this->rolesFor($model, $contextId);
        $permissions = collect();
        
        foreach ($allRoles as $role) {
            $rolePermissions = $role->permissions()
                ->when($contextId !== null, function ($query) use ($contextId) {
                    $query->wherePivot('context_id', $contextId);
                })
                ->get();
            
            $permissions = $permissions->merge($rolePermissions);
        }
        
        return $permissions->unique('id');
    }

    /**
     * Get directly assigned roles for a model.
     *
     * @param Model $model
     * @param int|null $contextId
     * @return Collection
     */
    protected function getDirectRoles(Model $model, ?int $contextId): Collection
    {
        if (!method_exists($model, 'roles')) {
            return collect();
        }

        return $model->roles()
            ->when($contextId !== null, function ($query) use ($contextId) {
                $query->where('context_id', $contextId);
            })
            ->get();
    }

    /**
     * Recursively resolve role hierarchy.
     *
     * @param RolloRole $role
     * @param array &$resolved
     * @param Collection &$collection
     * @return void
     */
    protected function resolveRoleHierarchy(RolloRole $role, array &$resolved, Collection &$collection): void
    {
        // Prevent circular references
        if (in_array($role->id, $resolved)) {
            return;
        }
        
        $resolved[] = $role->id;
        $collection->push($role);
        
        // Get child roles (roles that this role inherits from)
        $childRoles = $role->childRoles;
        
        foreach ($childRoles as $childRole) {
            $this->resolveRoleHierarchy($childRole, $resolved, $collection);
        }
    }

    /**
     * Resolve context ID from various input types.
     *
     * @param mixed $context
     * @return int|null
     */
    protected function resolveContextId(mixed $context): ?int
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