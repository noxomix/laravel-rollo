<?php

namespace Noxomix\LaravelRollo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isEnabled()
 * @method static bool shouldAuditEvent(string $event)
 * @method static void log(string $event, ?\Illuminate\Database\Eloquent\Model $auditable = null, ?\Illuminate\Database\Eloquent\Model $subject = null, array $oldValues = [], array $newValues = [], array $metadata = [])
 * @method static void logPermissionAssigned(\Illuminate\Database\Eloquent\Model $user, string $permissionName, ?int $contextId = null)
 * @method static void logPermissionRemoved(\Illuminate\Database\Eloquent\Model $user, string $permissionName, ?int $contextId = null)
 * @method static void logRoleAssigned(\Illuminate\Database\Eloquent\Model $user, string $roleName, ?int $contextId = null)
 * @method static void logRoleRemoved(\Illuminate\Database\Eloquent\Model $user, string $roleName, ?int $contextId = null)
 * @method static void logModelCreated(\Illuminate\Database\Eloquent\Model $model)
 * @method static void logModelUpdated(\Illuminate\Database\Eloquent\Model $model, array $oldValues)
 * @method static void logModelDeleted(\Illuminate\Database\Eloquent\Model $model)
 *
 * @see \Noxomix\LaravelRollo\Services\RolloAuditService
 */
class RolloAudit extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'rollo.audit';
    }
}