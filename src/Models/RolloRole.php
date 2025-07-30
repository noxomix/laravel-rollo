<?php

namespace Noxomix\LaravelRollo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class RolloRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rollo_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'context_id',
        'config',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'config' => 'array',
        'order' => 'double',
    ];

    /**
     * Get the context that this role belongs to.
     *
     * @return BelongsTo
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(RolloContext::class, 'context_id');
    }

    /**
     * Get all models that have this role.
     *
     * @param string $model
     * @return MorphToMany
     */
    public function models(string $model): MorphToMany
    {
        return $this->morphedByMany(
            $model,
            'model',
            'rollo_model_has_roles',
            'role_id',
            'model_id'
        );
    }

    /**
     * Get permissions assigned to this role.
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
     * Get child roles (roles assigned to this role for inheritance).
     *
     * @return MorphToMany
     */
    public function childRoles(): MorphToMany
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
     * Get parent roles (roles that have this role assigned).
     *
     * @return MorphToMany
     */
    public function parentRoles(): MorphToMany
    {
        return $this->morphedByMany(
            RolloRole::class,
            'model',
            'rollo_model_has_roles',
            'role_id',
            'model_id'
        );
    }

    /**
     * Find a role by its name and optional context.
     *
     * @param string $name
     * @param int|null $contextId
     * @return static|null
     */
    public static function findByName(string $name, ?int $contextId = null): ?self
    {
        return static::where('name', $name)
            ->where('context_id', $contextId)
            ->first();
    }

    /**
     * Create or get a role by name and optional context.
     *
     * @param string $name
     * @param int|null $contextId
     * @param array $attributes
     * @return static
     */
    public static function findOrCreate(string $name, ?int $contextId = null, array $attributes = []): self
    {
        $role = static::findByName($name, $contextId);

        if (!$role) {
            $role = static::create(array_merge([
                'name' => $name,
                'context_id' => $contextId,
            ], $attributes));
        }

        return $role;
    }

    /**
     * Assign a permission to this role.
     *
     * @param RolloPermission|string $permission
     * @param int|null $contextId
     * @return void
     */
    public function givePermissionTo($permission, ?int $contextId = null): void
    {
        if (is_string($permission)) {
            $permission = RolloPermission::findByName($permission);
            if (!$permission) {
                throw new \InvalidArgumentException("Permission '{$permission}' not found.");
            }
        }

        $this->permissions()->attach($permission->id, ['context_id' => $contextId]);
    }

    /**
     * Remove a permission from this role.
     *
     * @param RolloPermission|string $permission
     * @return void
     */
    public function revokePermissionTo($permission): void
    {
        if (is_string($permission)) {
            $permission = RolloPermission::findByName($permission);
            if (!$permission) {
                return;
            }
        }

        $this->permissions()->detach($permission->id);
    }

    /**
     * Assign a child role (for inheritance).
     *
     * @param RolloRole|string $role
     * @return void
     */
    public function assignChildRole($role): void
    {
        if (is_string($role)) {
            $role = static::findByName($role, $this->context_id);
            if (!$role) {
                throw new \InvalidArgumentException("Role '{$role}' not found.");
            }
        }

        // Prevent circular references
        if ($role->id === $this->id) {
            throw new \InvalidArgumentException("A role cannot inherit from itself.");
        }

        $this->childRoles()->attach($role->id);
    }

    /**
     * Remove a child role.
     *
     * @param RolloRole|string $role
     * @return void
     */
    public function removeChildRole($role): void
    {
        if (is_string($role)) {
            $role = static::findByName($role, $this->context_id);
            if (!$role) {
                return;
            }
        }

        $this->childRoles()->detach($role->id);
    }
}