<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BDPay Environment
    |--------------------------------------------------------------------------
    |
    | This value determines which BDPay environment to use.
    | Supported: "sandbox", "production"
    |
    */
    'environment' => env('BDPAY_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | BDPay API Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used to configure the BDPay API client.
    |
    */
    'api' => [
        'sandbox' => [
            'base_url' => 'https://dev-openapi.bdpay.co.id',
            'merchant_code' => env('BDPAY_SANDBOX_MERCHANT_CODE'),
            'public_key' => env('BDPAY_SANDBOX_PUBLIC_KEY'),
            'secret_key' => env('BDPAY_SANDBOX_SECRET_KEY'),
            'platform_public_key' => env('BDPAY_SANDBOX_PLATFORM_PUBLIC_KEY'),
        ],
        'production' => [
            'base_url' => 'https://openapi.bdpay.co.id',
            'merchant_code' => env('BDPAY_PRODUCTION_MERCHANT_CODE'),
            'public_key' => env('BDPAY_PRODUCTION_PUBLIC_KEY'),
            'secret_key' => env('BDPAY_PRODUCTION_SECRET_KEY'),
            'platform_public_key' => env('BDPAY_PRODUCTION_PLATFORM_PUBLIC_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used for webhook verification and handling.
    |
    */
    'webhook' => [
        'payment_callback_url' => env('BDPAY_PAYMENT_CALLBACK_URL'),
        'disbursement_callback_url' => env('BDPAY_DISBURSEMENT_CALLBACK_URL'),
        'verify_signature' => env('BDPAY_VERIFY_SIGNATURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default values for various BDPay operations.
    |
    */
    'defaults' => [
        'currency' => 'IDR',
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1000, // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for logging BDPay API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('BDPAY_LOGGING_ENABLED', true),
        'channel' => env('BDPAY_LOG_CHANNEL', 'daily'),
        'level' => env('BDPAY_LOG_LEVEL', 'info'),
    ],
];
