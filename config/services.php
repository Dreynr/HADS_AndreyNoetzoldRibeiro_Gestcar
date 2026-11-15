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

    /*
    |--------------------------------------------------------------------------
    | Z-API Omni - WhatsApp
    |--------------------------------------------------------------------------
    |
    | Credenciais do canal de WhatsApp (RNF11: so no .env, nunca no codigo).
    | Com 'enabled' desligado, o AppServiceProvider usa o LogWhatsAppSender e
    | nada sai para o celular de ninguem.
    |
    */

    'zapi' => [
        'enabled' => env('ZAPI_ENABLED', false),
        'base_url' => env('ZAPI_BASE_URL'),
        'channel_id' => env('ZAPI_CHANNEL_ID'),
        'secret_key' => env('ZAPI_SECRET_KEY'),
    ],

];
