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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    /*
    |--------------------------------------------------------------------------
    | Moyasar Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Configuration for Moyasar payment gateway integration
    |
    */
    'moyasar' => [
        'live' => env('MOYASAR_LIVE', false),
        'test_publishable_key' => env('MOYASAR_TEST_PUBLISHABLE_KEY'),
        'test_secret_key' => env('MOYASAR_TEST_SECRET_KEY'),
        'live_publishable_key' => env('MOYASAR_LIVE_PUBLISHABLE_KEY'),
        'live_secret_key' => env('MOYASAR_LIVE_SECRET_KEY'),
        'webhook_secret' => env('MOYASAR_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | STC Pay
    |--------------------------------------------------------------------------
    |
    | Configuration for STC Pay integration
    |
    */
    'stc_pay' => [
        'live' => env('STC_PAY_LIVE', false),
        'test_merchant_id' => env('STC_PAY_TEST_MERCHANT_ID'),
        'test_secret_key' => env('STC_PAY_TEST_SECRET_KEY'),
        'live_merchant_id' => env('STC_PAY_LIVE_MERCHANT_ID'),
        'live_secret_key' => env('STC_PAY_LIVE_SECRET_KEY'),
        'webhook_secret' => env('STC_PAY_WEBHOOK_SECRET'),
    ],

];
