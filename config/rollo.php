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
    | Define which models are allowed to use Rollo permissions and roles.
    | This provides an additional security layer against unauthorized model usage.
    | Set to null to allow any Eloquent model (less secure).
    |
    */
    'allowed_models' => [
        \App\Models\User::class,
        \App\Models\Tenant::class,
        // Add more models as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Configuration
    |--------------------------------------------------------------------------
    |
    | Configure authorization for Rollo admin operations like assigning roles
    | and permissions. When enabled, only authorized users can perform these
    | operations.
    |
    */
    'authorization' => [
        // Enable/disable authorization checks
        'enabled' => env('ROLLO_AUTHORIZATION_ENABLED', true),

        // Permission that grants full Rollo management access
        'super_admin_permission' => 'rollo.manage',

        // Role that grants full Rollo management access
        'super_admin_role' => 'super-admin',

        // Custom callback to determine if a user is an admin
        // Return true to grant access, false to deny
        'admin_callback' => null, // Example: fn($user) => $user->is_admin

        // If true, users can only assign permissions/roles they have
        'restrict_permission_assignment' => false,
        'restrict_role_assignment' => false,
    ],
];