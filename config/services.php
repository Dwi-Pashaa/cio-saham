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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'qontak' => [
        'base_url' => env('QONTAK_BASE_URL', 'https://api.mekari.com/qontak/chat'),
        'api_token' => env('QONTAK_API_TOKEN'),
        'client_id' => env('QONTAK_CLIENT_ID'),
        'client_secret' => env('QONTAK_CLIENT_SECRET'),
        'channel_integration_id' => env('QONTAK_CHANNEL_INTEGRATION_ID'),
        'template_id' => env('QONTAK_TEMPLATE_ID'),
        'otp_template_id' => env('QONTAK_OTP_TEMPLATE_ID'),
        'enabled' => env('QONTAK_ENABLED', true),
        'oauth_url' => env('QONTAK_OAUTH_URL', 'https://api.mekari.com/oauth/token'),
    ],

    'cio_finance' => [
        'base_url'   => env('CIO_FINANCE_BASE_URL', 'https://finance.cionetwork.id'),
        'client_id'  => env('CIO_FINANCE_CLIENT_ID', 'test_web_saham_18dcf3aab5a0d552f5670a3978c7cd22'),
        'key_id'     => env('CIO_FINANCE_KEY_ID', 'kid_4e0479ba4b715ac5'),
        'secret_key' => env('CIO_FINANCE_SECRET_KEY', 'b60777bc6d6569ad65f875e81f824cda74b3c1cb05a188081ef974ee6c943ed7'),
        'timeout'    => (int) env('CIO_FINANCE_TIMEOUT', 30),
    ],

];

