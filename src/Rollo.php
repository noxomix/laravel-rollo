<?php

namespace Noxomix\LaravelRollo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Noxomix\LaravelRollo\Models\RolloContext;
use Noxomix\LaravelRollo\Models\RolloPermission;
use Noxomix\LaravelRollo\Models\RolloRole;

class Rollo
{
    /**
     * Check if a model has a specific permission.
     * This checks both direct permissions and permissions through roles (with recursive inheritance).
     *
     * @param Model $model
     * @param string $permissionName
     * @param mixed $context
     * @return bool
     */
    public function has(Model $model, string $permissionName, $context = null): bool
    {
        $contextId = $this->resolveContextId($context);
        
        // Check cache first if enabled
        if ($this->cacheEnabled()) {
            $cacheKey = $this->getCacheKey($model, $permissionName, $contextId);
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Check direct permissions
        if ($this->hasDirectPermission($model, $permissionName, $contextId)) {
            $this->cacheResult($model, $permissionName, $contextId, true);
            return true;
        }

        // Check permissions through roles (with recursive inheritance)
        $hasPermission = $this->hasPermissionThroughRoles($model, $permissionName, $contextId);
        
        $this->cacheResult($model, $permissionName, $contextId, $hasPermission);
        
        return $hasPermission;
    }

    /**
     * Get all effective permissions for a model.
     * This includes direct permissions and permissions through roles (with recursive inheritance).
     *
     * @param Model $model
     * @param mixed $context
     * @return Collection
     */
    public function permissionsFor(Model $model, $context = null): Collection
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
     * Clear permission cache for a model.
     *
     * @param Model|null $model
     * @param string|null $permissionName
     * @param mixed $context
     * @return void
     */
    public function clearCache(?Model $model = null, ?string $permissionName = null, $context = null): void
    {
        if (!$this->cacheEnabled()) {
            return;
        }

        if ($model === null) {
            // Clear all cache
            Cache::tags($this->getCacheTag())->flush();
        } else {
            $contextId = $this->resolveContextId($context);
            
            if ($permissionName === null) {
                // Clear all cache for the model
                $pattern = $this->getCacheKeyPattern($model, '*', $contextId);
                $this->clearCacheByPattern($pattern);
            } else {
                // Clear specific cache entry
                $cacheKey = $this->getCacheKey($model, $permissionName, $contextId);
                Cache::forget($cacheKey);
            }
        }
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

    /**
     * Check if caching is enabled.
     *
     * @return bool
     */
    protected function cacheEnabled(): bool
    {
        return config('rollo.cache.enabled', true);
    }

    /**
     * Get cache key for permission check.
     *
     * @param Model $model
     * @param string $permissionName
     * @param int|null $contextId
     * @return string
     */
    protected function getCacheKey(Model $model, string $permissionName, ?int $contextId): string
    {
        $modelType = get_class($model);
        $modelId = $model->getKey();
        $contextPart = $contextId ?? 'null';
        
        return sprintf(
            '%s:%s:%s:%s:%s',
            config('rollo.cache.key', 'rollo.permissions'),
            $modelType,
            $modelId,
            $permissionName,
            $contextPart
        );
    }

    /**
     * Get cache key pattern.
     *
     * @param Model $model
     * @param string $permissionPattern
     * @param int|null $contextId
     * @return string
     */
    protected function getCacheKeyPattern(Model $model, string $permissionPattern, ?int $contextId): string
    {
        $modelType = get_class($model);
        $modelId = $model->getKey();
        $contextPart = $contextId ?? 'null';
        
        return sprintf(
            '%s:%s:%s:%s:%s',
            config('rollo.cache.key', 'rollo.permissions'),
            $modelType,
            $modelId,
            $permissionPattern,
            $contextPart
        );
    }

    /**
     * Get cache tag.
     *
     * @return string
     */
    protected function getCacheTag(): string
    {
        return config('rollo.cache.key', 'rollo.permissions');
    }

    /**
     * Cache permission check result.
     *
     * @param Model $model
     * @param string $permissionName
     * @param int|null $contextId
     * @param bool $result
     * @return void
     */
    protected function cacheResult(Model $model, string $permissionName, ?int $contextId, bool $result): void
    {
        if (!$this->cacheEnabled()) {
            return;
        }

        $cacheKey = $this->getCacheKey($model, $permissionName, $contextId);
        $ttl = config('rollo.cache.ttl', 3600);
        
        Cache::put($cacheKey, $result, $ttl);
    }

    /**
     * Clear cache by pattern.
     *
     * @param string $pattern
     * @return void
     */
    protected function clearCacheByPattern(string $pattern): void
    {
        // This is a simplified implementation
        // In production, you might want to use Redis SCAN or similar
        Cache::forget($pattern);
    }
}