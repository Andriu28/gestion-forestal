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

    'gfw' => [
        'base_uri'        => env('GFW_API_BASE_URI', 'https://data-api.globalforestwatch.org'),
        'api_key'         => env('GFW_API_KEY'),
        'timeout'         => (int) env('GFW_TIMEOUT', 30),
        'connect_timeout' => (int) env('GFW_CONNECT_TIMEOUT', 10),
        'dataset'         => env('GFW_DATASET', 'umd_tree_cover_loss'),
        'version'         => env('GFW_DATASET_VERSION', 'latest'),
    ],

];
