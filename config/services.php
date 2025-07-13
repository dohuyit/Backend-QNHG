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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'vnpay' => [
        'tmn_code' => env('VNPAY_ADMIN_TMN_CODE'),
        'hash_secret' => env('VNPAY_ADMIN_HASH_SECRET'),
        'url' => env('VNPAY_ADMIN_URL'),
        'return_url' => env('VNPAY_ADMIN_RETURN_URL'),
    ],
    'momo' => [
        'partner_code' => env('MOMO_ADMIN_PARTNER_CODE'),
        'access_key' => env('MOMO_ADMIN_ACCESS_KEY'),
        'secret_key' => env('MOMO_ADMIN_SECRET_KEY'),
        'endpoint' => env('MOMO_ADMIN_ENDPOINT'),
        'return_url' => env('MOMO_ADMIN_RETURN_URL'),
        'notify_url' => env('MOMO_ADMIN_NOTIFY_URL'),
    ],
    'vnpay_client' => [
        'tmn_code' => env('VNPAY_CLIENT_TMN_CODE'),
        'hash_secret' => env('VNPAY_CLIENT_HASH_SECRET'),
        'url' => env('VNPAY_CLIENT_URL'),
        'return_url' => env('VNPAY_CLIENT_RETURN_URL'),
    ],
    'momo_client' => [
        'partner_code' => env('MOMO_CLIENT_PARTNER_CODE'),
        'access_key' => env('MOMO_CLIENT_ACCESS_KEY'),
        'secret_key' => env('MOMO_CLIENT_SECRET_KEY'),
        'endpoint' => env('MOMO_CLIENT_ENDPOINT'),
        'return_url' => env('MOMO_CLIENT_RETURN_URL'),
        'notify_url' => env('MOMO_CLIENT_NOTIFY_URL'),
    ],



];
