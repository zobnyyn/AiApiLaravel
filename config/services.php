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

    'llama4' => [
        'api_key' => env('LLAMA4_API_KEY'),
    ],
    'llama4scout' => [
        'api_key' => env('LLAMA4SCOUT_API_KEY'),
    ],
    'qwen' => [
        'api_key' => env('QWEN_API_KEY'),
    ],
    'deepseekr1' => [
        'api_key' => env('DEEPSEEKR1_API_KEY'),
        'model_id' => 'deepseek-ai/deepseek-r1',
    ],
];
