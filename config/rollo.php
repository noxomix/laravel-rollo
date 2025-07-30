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
];