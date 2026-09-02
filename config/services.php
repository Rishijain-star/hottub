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

    'opencage' => [
        'key' => env('OPENCAGE_API_KEY'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'firetext' => [
        'api_key' => env('FIRETEXT_API_KEY'),
        // Sender ID on the handset: 3–11 alphanumeric chars; must be allowed in your FireText account.
        'from' => env('FIRETEXT_FROM') ?: 'HotTub',
        // Optional (testing): send admin 2FA OTP to this UK number instead of the user’s phone. Leave empty in production.
        'admin_2fa_to' => env('FIRETEXT_ADMIN_2FA_TO'),
    ],

    'turnstile' => [
        'enabled' => (bool) env('TURNSTILE_ENABLED', true),
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
        'appearance' => env('TURNSTILE_APPEARANCE', 'always'),
        'size' => env('TURNSTILE_SIZE', 'normal'),
        'honeypot_field' => 'company_website',
    ],

    'stripe' => [
        'key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
