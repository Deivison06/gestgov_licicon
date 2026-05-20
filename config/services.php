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

    'pncp' => [
        'base_url' => env('PNCP_BASE_URL', 'https://pncp.gov.br/api/consulta/v1'),
        'search_url' => env('PNCP_SEARCH_URL', 'https://pncp.gov.br/api/search/'),
        'cache_ttl' => env('PNCP_CACHE_TTL', 3600),
    ],

    'openai' => [
        'api_key'     => env('OPENAI_API_KEY'),
        'base_url'    => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout'     => (int) env('OPENAI_TIMEOUT', 30),
        'max_tokens'  => (int) env('OPENAI_MAX_TOKENS', 1200),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.3),
    ],

];
