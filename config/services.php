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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'redsys' => [
        'merchant_code' => env('REDSYS_MERCHANT_CODE'),
        'terminal' => env('REDSYS_TERMINAL', '001'),
        'secret_key' => env('REDSYS_SECRET_KEY'),
        'environment' => env('REDSYS_ENVIRONMENT', 'test'),
    ],

    'revolut' => [
        'api_key' => env('REVOLUT_MERCHANT_API_KEY'),
        'api_version' => env('REVOLUT_API_VERSION', '2023-09-01'),
        'sandbox' => env('REVOLUT_SANDBOX', true),
        'webhook_secret' => env('REVOLUT_WEBHOOK_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],

];
