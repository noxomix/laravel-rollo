<?php

namespace Noxomix\LaravelRollo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Noxomix\LaravelRollo\Helpers\ModelValidator;
use Noxomix\LaravelRollo\Traits\AuditsModelOperations;

class RolloContext extends Model
{
    use AuditsModelOperations;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rollo_contexts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contextable_type',
        'contextable_id',
    ];

    /**
     * Get the owning contextable model.
     *
     * @return MorphTo
     */
    public function contextable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all roles in this context.
     *
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(RolloRole::class, 'context_id');
    }

    /**
     * Find a context by its contextable model.
     *
     * @param Model $model
     * @return static|null
     */
    public static function findByModel(Model $model): ?self
    {
        return static::where('contextable_type', get_class($model))
            ->where('contextable_id', $model->getKey())
            ->first();
    }

    /**
     * Create or get a context for a model.
     *
     * @param Model $model
     * @param string|null $name
     * @return static
     */
    public static function findOrCreateForModel(Model $model, ?string $name = null): self
    {
        $context = static::findByModel($model);

        if (!$context) {
            $context = static::create([
                'name' => $name ?? class_basename($model) . ' ' . $model->getKey(),
                'contextable_type' => get_class($model),
                'contextable_id' => $model->getKey(),
            ]);
        }

        return $context;
    }

    /**
     * Get all permissions in this context.
     *
     * @return \Illuminate\Support\Collection
     */
    public function permissions()
    {
        return RolloPermission::query()
            ->join('rollo_model_has_permissions', 'rollo_permissions.id', '=', 'rollo_model_has_permissions.permission_id')
            ->where('rollo_model_has_permissions.context_id', $this->id)
            ->distinct()
            ->get();
    }

    /**
     * Get all models with permissions in this context.
     *
     * @param string $modelClass
     * @return \Illuminate\Support\Collection
     */
    public function modelsWithPermissions(string $modelClass)
    {
        // Validate model class
        ModelValidator::validateModelClass($modelClass);
        
        $model = new $modelClass;
        $tableName = ModelValidator::sanitizeTableName($model->getTable());
        
        return $modelClass::query()
            ->join('rollo_model_has_permissions', function ($join) use ($modelClass, $tableName) {
                $join->on('rollo_model_has_permissions.model_id', '=', $tableName . '.id')
                    ->where('rollo_model_has_permissions.model_type', '=', $modelClass);
            })
            ->where('rollo_model_has_permissions.context_id', $this->id)
            ->distinct()
            ->select($tableName . '.*')
            ->get();
    }

    /**
     * Get all models with roles in this context.
     *
     * @param string $modelClass
     * @return \Illuminate\Support\Collection
     */
    public function modelsWithRoles(string $modelClass)
    {
        // Validate model class
        ModelValidator::validateModelClass($modelClass);
        
        $model = new $modelClass;
        $tableName = ModelValidator::sanitizeTableName($model->getTable());
        
        return $modelClass::query()
            ->join('rollo_model_has_roles', function ($join) use ($modelClass, $tableName) {
                $join->on('rollo_model_has_roles.model_id', '=', $tableName . '.id')
                    ->where('rollo_model_has_roles.model_type', '=', $modelClass);
            })
            ->join('rollo_roles', 'rollo_model_has_roles.role_id', '=', 'rollo_roles.id')
            ->where('rollo_roles.context_id', $this->id)
            ->distinct()
            ->select($tableName . '.*')
            ->get();
    }
}