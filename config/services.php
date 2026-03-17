<?php

return [
    'mpesa' => [
        'env' => env('MPESA_ENV', 'sandbox'),
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'callback_url' => env('MPESA_CALLBACK_URL'),
    ],

    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME', 'sandbox'),
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'from' => env('AFRICASTALKING_FROM', 'FEEYANGU'),
    ],

    'bank' => [
        'name' => env('BANK_NAME', 'KCB Bank Kenya'),
        'account_name' => env('BANK_ACCOUNT_NAME', 'Feeyangu Limited'),
        'account_number' => env('BANK_ACCOUNT_NUMBER'),
        'branch' => env('BANK_BRANCH', 'Nairobi'),
    ],
];
