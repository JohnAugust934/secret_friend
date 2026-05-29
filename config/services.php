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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'ops' => [
        'alert_email'          => env('OPS_ALERT_EMAIL'),
        'status_allowed_emails'=> env('OPS_STATUS_ALLOWED_EMAILS', ''),
        'healthcheck_url'      => env('OPS_HEALTHCHECK_URL', ''),
        'healthcheck_secret'   => env('OPS_HEALTHCHECK_SECRET'),   // SEGURANÇA: Token opcional para o endpoint /healthz
        'backup_retention_days'=> (int) env('OPS_BACKUP_RETENTION_DAYS', 14),
        'mysql_dump_binary'    => env('OPS_MYSQL_DUMP_BINARY', ''),
        'mysql_restore_binary' => env('OPS_MYSQL_RESTORE_BINARY', ''),
    ],

];
