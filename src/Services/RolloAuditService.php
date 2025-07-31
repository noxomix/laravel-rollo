<?php

namespace Noxomix\LaravelRollo\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Noxomix\LaravelRollo\Models\RolloAudit;

class RolloAuditService
{
    /**
     * Check if auditing is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('rollo.audit.enabled', false);
    }

    /**
     * Check if a specific event should be audited.
     *
     * @param string $event
     * @return bool
     */
    public function shouldAuditEvent(string $event): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $allowedEvents = config('rollo.audit.events');
        
        // If no specific events are configured, audit all events
        if ($allowedEvents === null) {
            return true;
        }

        return in_array($event, $allowedEvents);
    }

    /**
     * Log an audit event.
     *
     * @param string $event
     * @param Model|null $auditable
     * @param Model|null $subject
     * @param array $oldValues
     * @param array $newValues
     * @param array $metadata
     * @return void
     */
    public function log(
        string $event,
        ?Model $auditable = null,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): void {
        if (!$this->shouldAuditEvent($event)) {
            return;
        }

        $auditData = $this->prepareAuditData($event, $auditable, $subject, $oldValues, $newValues, $metadata);

        // Log to database if enabled
        if (config('rollo.audit.drivers.database', true)) {
            $this->logToDatabase($auditData);
        }

        // Log to file if enabled
        if (config('rollo.audit.drivers.log', false)) {
            $this->logToFile($auditData);
        }
    }

    /**
     * Prepare audit data.
     *
     * @param string $event
     * @param Model|null $auditable
     * @param Model|null $subject
     * @param array $oldValues
     * @param array $newValues
     * @param array $metadata
     * @return array
     */
    protected function prepareAuditData(
        string $event,
        ?Model $auditable,
        ?Model $subject,
        array $oldValues,
        array $newValues,
        array $metadata
    ): array {
        $data = [
            'event' => $event,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'user_id' => Auth::id(),
            'metadata' => $metadata,
        ];

        // Add auditable model
        if ($auditable) {
            $data['auditable_type'] = get_class($auditable);
            $data['auditable_id'] = $auditable->getKey();
        }

        // Add subject model
        if ($subject) {
            $data['subject_type'] = get_class($subject);
            $data['subject_id'] = $subject->getKey();
        }

        // Add metadata if enabled
        if (config('rollo.audit.include_metadata', true)) {
            $data['ip_address'] = Request::ip();
            $data['user_agent'] = Request::userAgent();
            
            // Add any additional metadata
            $data['metadata'] = array_merge($data['metadata'] ?? [], [
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'session_id' => session()->getId(),
            ]);
        }

        return $data;
    }

    /**
     * Log audit data to database.
     *
     * @param array $data
     * @return void
     */
    protected function logToDatabase(array $data): void
    {
        try {
            RolloAudit::create($data);
        } catch (\Exception $e) {
            // If database logging fails, log to error log
            Log::error('Failed to create Rollo audit log', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Log audit data to file.
     *
     * @param array $data
     * @return void
     */
    protected function logToFile(array $data): void
    {
        $channel = config('rollo.audit.log_channel', 'daily');
        
        // Format the log message
        $message = sprintf(
            'Rollo Audit: %s | User: %s | %s',
            $data['event'],
            $data['user_id'] ?? 'System',
            $this->formatAuditableInfo($data)
        );

        // Prepare context for structured logging
        $context = [
            'event' => $data['event'],
            'user_id' => $data['user_id'],
            'auditable' => [
                'type' => $data['auditable_type'] ?? null,
                'id' => $data['auditable_id'] ?? null,
            ],
            'subject' => [
                'type' => $data['subject_type'] ?? null,
                'id' => $data['subject_id'] ?? null,
            ],
            'changes' => $this->formatChanges($data['old_values'] ?? [], $data['new_values'] ?? []),
            'metadata' => $data['metadata'] ?? [],
        ];

        Log::channel($channel)->info($message, $context);
    }

    /**
     * Format auditable information for logging.
     *
     * @param array $data
     * @return string
     */
    protected function formatAuditableInfo(array $data): string
    {
        $parts = [];

        if (isset($data['auditable_type']) && isset($data['auditable_id'])) {
            $parts[] = sprintf(
                '%s #%s',
                class_basename($data['auditable_type']),
                $data['auditable_id']
            );
        }

        if (isset($data['subject_type']) && isset($data['subject_id'])) {
            $parts[] = sprintf(
                'for %s #%s',
                class_basename($data['subject_type']),
                $data['subject_id']
            );
        }

        return implode(' ', $parts);
    }

    /**
     * Format changes for logging.
     *
     * @param array $oldValues
     * @param array $newValues
     * @return array
     */
    protected function formatChanges(array $oldValues, array $newValues): array
    {
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        foreach ($allKeys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            if ($old !== $new) {
                $changes[$key] = [
                    'from' => $old,
                    'to' => $new,
                ];
            }
        }

        return $changes;
    }

    /**
     * Quick log methods for common events.
     */
    
    public function logPermissionAssigned(Model $user, string $permissionName, ?int $contextId = null): void
    {
        $this->log(
            'permission.assigned',
            null,
            $user,
            [],
            ['permission' => $permissionName, 'context_id' => $contextId]
        );
    }

    public function logPermissionRemoved(Model $user, string $permissionName, ?int $contextId = null): void
    {
        $this->log(
            'permission.removed',
            null,
            $user,
            ['permission' => $permissionName, 'context_id' => $contextId],
            []
        );
    }

    public function logRoleAssigned(Model $user, string $roleName, ?int $contextId = null): void
    {
        $this->log(
            'role.assigned',
            null,
            $user,
            [],
            ['role' => $roleName, 'context_id' => $contextId]
        );
    }

    public function logRoleRemoved(Model $user, string $roleName, ?int $contextId = null): void
    {
        $this->log(
            'role.removed',
            null,
            $user,
            ['role' => $roleName, 'context_id' => $contextId],
            []
        );
    }

    public function logModelCreated(Model $model): void
    {
        $event = strtolower(class_basename($model)) . '.created';
        $this->log($event, $model, null, [], $model->getAttributes());
    }

    public function logModelUpdated(Model $model, array $oldValues): void
    {
        $event = strtolower(class_basename($model)) . '.updated';
        $this->log($event, $model, null, $oldValues, $model->getAttributes());
    }

    public function logModelDeleted(Model $model): void
    {
        $event = strtolower(class_basename($model)) . '.deleted';
        $this->log($event, $model, null, $model->getAttributes(), []);
    }
}