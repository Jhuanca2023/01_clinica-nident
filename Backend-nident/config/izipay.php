<?php
return [
    'env' => env('IZIPAY_ENV', 'sandbox'),
    'private_key' => env('IZIPAY_PRIVATE_KEY'),
    'merchant_id' => env('IZIPAY_MERCHANT_ID'),
    'base_url' => env('IZIPAY_ENV') === 'production'
        ? 'https://api.micuentaweb.pe'
        : 'https://api.micuentaweb.pe', // En ambos casos, igual según docs
];