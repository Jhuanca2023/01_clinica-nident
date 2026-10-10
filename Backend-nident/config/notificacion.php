<?php

return [
    'frontend_url' => rtrim(env('FRONTEND_URL', env('APP_URL_FROND', env('APP_URL', 'http://localhost'))), '/'),

    'vapid' => [
        'subject'     => env('VAPID_SUBJECT', env('MAIL_FROM_ADDRESS', 'mailto:ventas@rossety.pe')),
        'public_key'  => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],
];
