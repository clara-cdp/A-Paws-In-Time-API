<?php

$csv = static function (string $key, string $default = ''): array {
    return array_values(array_filter(array_map(
        static fn (string $value): string => trim($value),
        explode(',', (string) env($key, $default)),
    )));
};

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The browser sends an OPTIONS preflight before requests that include JSON
    | payloads or Authorization headers. These settings tell Laravel which
    | frontend origins may call the API.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $csv(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:5173,http://127.0.0.1:5173,http://localhost:3000'
    ),

    'allowed_origins_patterns' => $csv('CORS_ALLOWED_ORIGIN_PATTERNS'),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];
