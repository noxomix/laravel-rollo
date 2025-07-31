<?php

namespace Noxomix\LaravelRollo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class RolloAudit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rollo_audits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'subject_type',
        'subject_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'user_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($audit) {
            $audit->created_at = $audit->created_at ?? now();
        });
    }

    /**
     * Get the auditable model.
     *
     * @return MorphTo
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the subject model (the model that was affected).
     *
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    /**
     * Scope a query to only include audits for a specific user.
     *
     * @param Builder $query
     * @param int|Model $user
     * @return Builder
     */
    public function scopeForUser(Builder $query, $user): Builder
    {
        $userId = is_object($user) ? $user->getKey() : $user;
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include audits for a specific event.
     *
     * @param Builder $query
     * @param string|array $event
     * @return Builder
     */
    public function scopeForEvent(Builder $query, $event): Builder
    {
        if (is_array($event)) {
            return $query->whereIn('event', $event);
        }
        
        return $query->where('event', $event);
    }

    /**
     * Scope a query to only include recent audits.
     *
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope a query to only include audits for a specific model.
     *
     * @param Builder $query
     * @param Model|string $model
     * @param int|null $modelId
     * @return Builder
     */
    public function scopeForModel(Builder $query, $model, $modelId = null): Builder
    {
        if (is_object($model)) {
            return $query->where('auditable_type', get_class($model))
                        ->where('auditable_id', $model->getKey());
        }
        
        $query->where('auditable_type', $model);
        
        if ($modelId !== null) {
            $query->where('auditable_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope a query to only include audits for a specific subject.
     *
     * @param Builder $query
     * @param Model|string $subject
     * @param int|null $subjectId
     * @return Builder
     */
    public function scopeForSubject(Builder $query, $subject, $subjectId = null): Builder
    {
        if (is_object($subject)) {
            return $query->where('subject_type', get_class($subject))
                        ->where('subject_id', $subject->getKey());
        }
        
        $query->where('subject_type', $subject);
        
        if ($subjectId !== null) {
            $query->where('subject_id', $subjectId);
        }
        
        return $query;
    }

    /**
     * Scope a query to only include audits older than a certain number of days.
     *
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeOlderThan(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '<', Carbon::now()->subDays($days));
    }

    /**
     * Get the changes between old and new values.
     *
     * @return array
     */
    public function getChanges(): array
    {
        $changes = [];
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        
        // Find all unique keys
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        
        foreach ($keys as $key) {
            $oldValue = $old[$key] ?? null;
            $newValue = $new[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Check if a specific field was changed.
     *
     * @param string $field
     * @return bool
     */
    public function wasChanged(string $field): bool
    {
        $old = $this->old_values[$field] ?? null;
        $new = $this->new_values[$field] ?? null;
        
        return $old !== $new;
    }

    /**
     * Get a human-readable description of the event.
     *
     * @return string
     */
    public function getDescription(): string
    {
        $descriptions = [
            'permission.assigned' => 'assigned permission',
            'permission.removed' => 'removed permission',
            'permission.synced' => 'synced permissions',
            'role.assigned' => 'assigned role',
            'role.removed' => 'removed role',
            'role.synced' => 'synced roles',
            'role.created' => 'created role',
            'role.updated' => 'updated role',
            'role.deleted' => 'deleted role',
            'permission.created' => 'created permission',
            'permission.updated' => 'updated permission',
            'permission.deleted' => 'deleted permission',
            'context.created' => 'created context',
            'context.updated' => 'updated context',
            'context.deleted' => 'deleted context',
        ];
        
        return $descriptions[$this->event] ?? $this->event;
    }

    /**
     * Format the audit entry for display.
     *
     * @return string
     */
    public function format(): string
    {
        $user = $this->user ? $this->user->name : 'System';
        $description = $this->getDescription();
        $auditable = $this->auditable ? class_basename($this->auditable) . ' #' . $this->auditable->getKey() : 'Unknown';
        
        if ($this->subject) {
            $subject = class_basename($this->subject) . ' #' . $this->subject->getKey();
            return "{$user} {$description} {$auditable} for {$subject}";
        }
        
        return "{$user} {$description} {$auditable}";
    }
}