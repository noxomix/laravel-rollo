<?php

namespace Noxomix\LaravelRollo\Policies;

use Illuminate\Database\Eloquent\Model;

class RolloPolicy
{
    /**
     * Determine if the user can manage permissions and roles.
     *
     * @param Model $user
     * @return bool
     */
    public function manage(Model $user): bool
    {
        // Check if authorization is enabled
        if (!config('rollo.authorization.enabled', true)) {
            return true;
        }

        // Check using configured callback
        $callback = config('rollo.authorization.admin_callback');
        if (is_callable($callback)) {
            return $callback($user);
        }

        // Check for super admin permission
        $superAdminPermission = config('rollo.authorization.super_admin_permission', 'rollo.manage');
        if (method_exists($user, 'hasPermission') && $user->hasPermission($superAdminPermission)) {
            return true;
        }

        // Check for super admin role
        $superAdminRole = config('rollo.authorization.super_admin_role', 'super-admin');
        if (method_exists($user, 'hasRole') && $user->hasRole($superAdminRole)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can assign permissions.
     *
     * @param Model $user
     * @param Model $target
     * @param string|null $permission
     * @return bool
     */
    public function assignPermission(Model $user, Model $target, ?string $permission = null): bool
    {
        if (!$this->manage($user)) {
            return false;
        }

        // Additional check: Can only assign permissions the user has
        if (config('rollo.authorization.restrict_permission_assignment', false)) {
            if ($permission && method_exists($user, 'hasPermission')) {
                return $user->hasPermission($permission);
            }
        }

        return true;
    }

    /**
     * Determine if the user can revoke permissions.
     *
     * @param Model $user
     * @param Model $target
     * @return bool
     */
    public function revokePermission(Model $user, Model $target): bool
    {
        return $this->manage($user);
    }

    /**
     * Determine if the user can assign roles.
     *
     * @param Model $user
     * @param Model $target
     * @param string|null $role
     * @return bool
     */
    public function assignRole(Model $user, Model $target, ?string $role = null): bool
    {
        if (!$this->manage($user)) {
            return false;
        }

        // Additional check: Can only assign roles the user has
        if (config('rollo.authorization.restrict_role_assignment', false)) {
            if ($role && method_exists($user, 'hasRole')) {
                return $user->hasRole($role);
            }
        }

        return true;
    }

    /**
     * Determine if the user can revoke roles.
     *
     * @param Model $user
     * @param Model $target
     * @return bool
     */
    public function revokeRole(Model $user, Model $target): bool
    {
        return $this->manage($user);
    }

    /**
     * Determine if the user can create permissions.
     *
     * @param Model $user
     * @return bool
     */
    public function createPermission(Model $user): bool
    {
        return $this->manage($user);
    }

    /**
     * Determine if the user can create roles.
     *
     * @param Model $user
     * @return bool
     */
    public function createRole(Model $user): bool
    {
        return $this->manage($user);
    }

    /**
     * Determine if the user can delete permissions.
     *
     * @param Model $user
     * @return bool
     */
    public function deletePermission(Model $user): bool
    {
        return $this->manage($user);
    }

    /**
     * Determine if the user can delete roles.
     *
     * @param Model $user
     * @return bool
     */
    public function deleteRole(Model $user): bool
    {
        return $this->manage($user);
    }
}