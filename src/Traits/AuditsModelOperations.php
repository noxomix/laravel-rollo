<?php

namespace Noxomix\LaravelRollo\Traits;

use Noxomix\LaravelRollo\Facades\RolloAudit;

trait AuditsModelOperations
{
    /**
     * Boot the audits model operations trait.
     *
     * @return void
     */
    public static function bootAuditsModelOperations()
    {
        // Log when model is created
        static::created(function ($model) {
            RolloAudit::logModelCreated($model);
        });

        // Log when model is updated
        static::updating(function ($model) {
            // Store original values before update
            $model->auditOldValues = $model->getOriginal();
        });

        static::updated(function ($model) {
            // Get the old values we stored
            $oldValues = $model->auditOldValues ?? [];
            unset($model->auditOldValues);
            
            // Only log if there were actual changes
            if (!empty($oldValues)) {
                RolloAudit::logModelUpdated($model, $oldValues);
            }
        });

        // Log when model is deleted
        static::deleting(function ($model) {
            // Store values before deletion
            $model->auditDeletedValues = $model->getAttributes();
        });

        static::deleted(function ($model) {
            // Use the stored values
            $deletedValues = $model->auditDeletedValues ?? $model->getAttributes();
            unset($model->auditDeletedValues);
            
            RolloAudit::logModelDeleted($model);
        });
    }
}