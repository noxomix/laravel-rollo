<?php

namespace Noxomix\LaravelRollo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class RolloPermission extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rollo_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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
     * Get all models that have this permission.
     *
     * @param string $model
     * @return MorphToMany
     */
    public function models(string $model): MorphToMany
    {
        return $this->morphedByMany(
            $model,
            'model',
            'rollo_model_has_permissions',
            'permission_id',
            'model_id'
        )->withPivot('context_id');
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     * @return static|null
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Create or get a permission by name.
     *
     * @param string $name
     * @param array $attributes
     * @return static
     */
    public static function findOrCreate(string $name, array $attributes = []): self
    {
        $permission = static::findByName($name);

        if (!$permission) {
            $permission = static::create(array_merge(['name' => $name], $attributes));
        }

        return $permission;
    }
}