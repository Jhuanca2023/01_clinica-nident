<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vite Build Path
    |--------------------------------------------------------------------------
    |
    | Directorio donde Vite colocará los assets compilados.
    | Relativo a la carpeta public de Laravel.
    |
    */
    //Este error ocurre porque el paquete Laravel Vite no incluye archivos de configuración publicables por defecto. 

     'build_path' => env('VITE_BUILD_PATH', 'build'),
    //'build_path' => env('VITE_BUILD_PATH', '../../public_html/build'),


    /*
    |--------------------------------------------------------------------------
    | Vite Server URL
    |--------------------------------------------------------------------------
    |
    | URL del servidor de desarrollo Vite (usado en local).
    |
    */
    'dev_server_url' => env('VITE_DEV_SERVER_URL', 'http://localhost:5173'),

    /*
    |--------------------------------------------------------------------------
    | Vite Manifest
    |--------------------------------------------------------------------------
    |
    | Nombre del archivo manifest generado por Vite.
    |
    */
    'manifest' => 'manifest.json',

    /*
    |--------------------------------------------------------------------------
    | Vite Assets URL
    |--------------------------------------------------------------------------
    |
    | URL base para los assets compilados.
    |
    */
    'assets_url' => env('VITE_ASSETS_URL', null),
];