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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_drive' => [
        'folder_fisik_id' => env('GOOGLE_DRIVE_FOLDER_FISIK_ID', '1bbK_kpW2QGm8D9u740mLtpyunJbSNFxS'),
        'folder_tagging_id' => env('GOOGLE_DRIVE_FOLDER_TAGGING_ID', '15Dp7vco1OTTWcQzogFJpcXzVSu7gTDT6'),
        'service_account_json' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON', storage_path('app/google-service-account.json')),
        'service_account_key' => env('GOOGLE_DRIVE_SERVICE_ACCOUNT_KEY', null),
    ],

];
