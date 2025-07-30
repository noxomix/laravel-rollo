<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rollo Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the settings for the Laravel Rollo package.
    |
    */

    'enabled' => env('ROLLO_ENABLED', true),
    
    'default_option' => env('ROLLO_DEFAULT_OPTION', 'value'),
    
    'cache' => [
        'enabled' => env('ROLLO_CACHE_ENABLED', true),
        'duration' => env('ROLLO_CACHE_DURATION', 3600),
    ],
];