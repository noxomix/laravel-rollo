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
    | Package models MAY be included when they use these traits (e.g. RolloRole).
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
    
];
