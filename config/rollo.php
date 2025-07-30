<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for permission and role lookups. This can significantly
    | improve performance for applications with many permissions and roles.
    |
    */
    'cache' => [
        'enabled' => env('ROLLO_CACHE_ENABLED', true),
        'key' => 'rollo.permissions',
        'ttl' => env('ROLLO_CACHE_TTL', 3600), // 1 hour
    ],
];