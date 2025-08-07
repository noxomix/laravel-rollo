<?php

namespace Noxomix\LaravelRollo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Noxomix\LaravelRollo\Validators\RolloValidator;
use Noxomix\LaravelRollo\Traits\HasRolloPermissions;

class RolloRole extends Model
{
    use HasRolloPermissions;
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
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Validate on creating
        static::creating(function ($role) {
            RolloValidator::validateRoleName($role->name);
            if ($role->config !== null) {
                RolloValidator::validateConfig($role->config);
            }
        });

        // Validate on updating
        static::updating(function ($role) {
            if ($role->isDirty('name')) {
                RolloValidator::validateRoleName($role->name);
            }
            if ($role->isDirty('config')) {
                RolloValidator::validateConfig($role->config);
            }
        });
    }

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
     * Assign a child role (for inheritance).
     *
     * @param RolloRole|string $role
     * @return void
     */
    public function assignChildRole($role): void
    {
        if (is_string($role)) {
            $roleName = $role;
            $role = static::findByName($roleName, $this->context_id);
            if (!$role) {
                throw new \InvalidArgumentException("Role '{$roleName}' not found.");
            }
        }

        // Prevent circular references
        if ($role->id === $this->id) {
            throw new \InvalidArgumentException("A role cannot inherit from itself.");
        }

        // Check for circular inheritance
        if ($this->wouldCreateCircularInheritance($role)) {
            throw new \InvalidArgumentException("This would create a circular role inheritance.");
        }

        $this->childRoles()->attach($role->id);
    }

    /**
     * Check if assigning a child role would create circular inheritance.
     *
     * @param RolloRole $childRole
     * @return bool
     */
    protected function wouldCreateCircularInheritance(RolloRole $childRole): bool
    {
        // Check if this role is already in the child role's inheritance chain
        $visited = [];
        return $this->isInInheritanceChain($childRole, $this->id, $visited);
    }

    /**
     * Recursively check if a role ID exists in the inheritance chain.
     *
     * @param RolloRole $role
     * @param int $searchId
     * @param array &$visited
     * @return bool
     */
    protected function isInInheritanceChain(RolloRole $role, int $searchId, array &$visited): bool
    {
        // Prevent infinite loops
        if (in_array($role->id, $visited)) {
            return false;
        }
        
        $visited[] = $role->id;
        
        // Load child roles if not already loaded
        $role->load('childRoles');
        
        foreach ($role->childRoles as $childRole) {
            if ($childRole->id === $searchId) {
                return true;
            }
            
            if ($this->isInInheritanceChain($childRole, $searchId, $visited)) {
                return true;
            }
        }
        
        return false;
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
            $roleName = $role;
            $role = static::findByName($roleName, $this->context_id);
            if (!$role) {
                return;
            }
        }

        $this->childRoles()->detach($role->id);
    }


    /**
     * Get a config value by key (supports dot notation).
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }
}