<?php

use Illuminate\Support\Str;

return [
    'paths' => [
        resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views')) ?: storage_path('framework/views')
    ),

    'cache' => [
        'enabled' => env('VIEW_CACHE', true),
        'prefix' => env('VIEW_CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_view'),
    ],
];
