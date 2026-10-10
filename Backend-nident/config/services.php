<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('GOOGLE_REDIRECT')),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
        'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
    ],

    'culqi' => [
        'public_key' => env('CULQI_PUBLIC_KEY'),
        'secret_key' => env('CULQI_SECRET_KEY'),
        'rsa_id' => env('CULQI_RSA_ID'),
        'rsa_public_key' => env('CULQI_RSA_PUBLIC_KEY'),
        'webhook_secret' => env('CULQI_WEBHOOK_SECRET'),
    ],

    'recaptcha' => [
        'site' => env('RECAPTCHA_SITE_KEY'),
        'secret' => env('RECAPTCHA_SECRET_KEY'),
    ],

];
