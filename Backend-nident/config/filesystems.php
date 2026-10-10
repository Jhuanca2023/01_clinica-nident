<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),//Jorge 
            //'root' => '/home/fabrijpv/rossety.pe/public', 
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'public_imagenes' => [
            'driver' => 'local',
            // En producción (cPanel) separas código en /laravel y assets en /public_html.
            // `public_path()` puede apuntar a /laravel/public al evaluarse config,
            // por eso el upload termina en laravel/public.
            // Usamos APP_PUBLIC_PATH directamente para que siempre guarde en public_html.
            'root' => env('APP_PUBLIC_PATH', public_path()),
            //'root' => '/home/fabrijpv/rossety.pe/public',
            'url' => env('APP_URL'),
            'visibility' => 'public',
        ],

        /*
         * Spatie Media Library — imágenes del CMS comprimidas a WebP.
         * Mismo root que public_imagenes (APP_PUBLIC_PATH en cPanel).
         */
        'media' => [
            'driver' => 'local',
            'root' => rtrim(str_replace('\\', '/', (string) env('APP_PUBLIC_PATH', public_path())), '/').'/storage_/media',
            'url' => rtrim((string) env('APP_URL'), '/').'/storage_/media',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        (env('APP_PUBLIC_PATH')
            ? rtrim(env('APP_PUBLIC_PATH'), '/\\') . '/storage'
            : public_path('storage')
        ) => storage_path('app/public'),
    ],

];
