<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used
    | for processing payments. Supported: "paymob", "myfatoorah"
    |
    */

    'default_gateway' => env('PAYMENT_GATEWAY', 'paymob'),

    /*
    |--------------------------------------------------------------------------
    | Paymob Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Paymob payment gateway
    |
    */

    'paymob' => [
        'api_key' => env('PAYMOB_API_KEY'),
        'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
        'base_url' => env('PAYMOB_BASE_URL', 'https://ksa.paymob.com/api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MyFatoorah Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MyFatoorah payment gateway
    |
    */

    'myfatoorah' => [
        'api_key' => env('MYFATOORAH_API_KEY'),
        'base_url' => env('MYFATOORAH_BASE_URL', 'https://api.myfatoorah.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    |
    | General payment settings
    |
    */

    'currency' => env('PAYMENT_CURRENCY', 'USD'),

    'callback_url' => env('PAYMENT_CALLBACK_URL', '/payment/callback'),

    'webhook_url' => env('PAYMENT_WEBHOOK_URL', '/payment/webhook'),
];
