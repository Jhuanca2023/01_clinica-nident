<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Solo API; no se usa Sanctum (auth vía JWT)
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Orígenes: * en local (Fruitcake usa * solo si supports_credentials=false)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [
        '#^https?://valentostile\.com(:\d+)?$#',
        '#^https?://localhost(:\d+)?$#',
        '#^https?://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    // JWT usa Authorization header, no cookies; credentials=false evita conflictos CORS
    'supports_credentials' => false,

];
