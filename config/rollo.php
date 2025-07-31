<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rollo Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Laravel Rollo package.
    |
    */
    
    /*
    |--------------------------------------------------------------------------
    | Allowed Models
    |--------------------------------------------------------------------------
    |
    | Define which models in your application can have roles and permissions.
    | These are the models that can use the HasRolloRoles and HasRolloPermissions traits.
    | This does NOT include Rollo's own models (RolloRole, RolloPermission, etc.).
    | 
    | This provides an additional security layer against unauthorized model usage.
    | Set to null to allow any Eloquent model to have roles/permissions (less secure).
    |
    | Example: User::class, Team::class, Organization::class
    |
    */
    'allowed_models' => [
        \App\Models\User::class,
        \Noxomix\LaravelRollo\Models\RolloRole::class,
        // Add more models as needed
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Configure audit logging for all Rollo operations. You can enable/disable
    | auditing and choose which drivers to use for storing audit logs.
    |
    */
    'audit' => [
        // Enable or disable audit logging completely
        'enabled' => env('ROLLO_AUDIT_ENABLED', false),
        
        // Which drivers to use for audit logging
        'drivers' => [
            // Store audit logs in database (rollo_audits table)
            'database' => env('ROLLO_AUDIT_DATABASE', true),
            
            // Store audit logs in Laravel log files
            'log' => env('ROLLO_AUDIT_LOG', false),
        ],
        
        // Log channel to use when 'log' driver is enabled
        'log_channel' => env('ROLLO_AUDIT_LOG_CHANNEL', 'daily'),
        
        // How many days to keep audit records (null = keep forever)
        'retention_days' => env('ROLLO_AUDIT_RETENTION_DAYS', 90),
        
        // Include additional metadata (IP address, user agent, etc.)
        'include_metadata' => env('ROLLO_AUDIT_INCLUDE_METADATA', true),
        
        // Events to audit (null = audit all events)
        'events' => env('ROLLO_AUDIT_EVENTS', null) ? explode(',', env('ROLLO_AUDIT_EVENTS')) : null,
    ],
];