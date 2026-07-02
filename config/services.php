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

    'bets_api' => [
        'token' => env('BETS_API_TOKEN', '78614-HWTKKepUL8Ufpx'),
        'base_url' => 'https://api.b365api.com',
    ],

    'assets_cdn_url' => env('ASSETS_CDN_URL', 'https://assets.betsapi.com/v1/'),

    'paggue' => [
        'api_url'      => env('PAGGUE_API_URL', 'https://api.paggue.com.br'),
        'client_key'   => env('PAGGUE_CLIENT_KEY'),
        'client_secret'=> env('PAGGUE_CLIENT_SECRET'),
        'signature'    => env('PAGGUE_SIGNATURE'),
        'company_id'   => env('PAGGUE_COMPANY_ID'),
    ],

    'shipay' => [
        'api_url'    => env('SHIPAY_API_URL', 'https://api.shipay.com.br'),
        'access_key' => env('SHIPAY_ACCESS_KEY'),
        'secret_key' => env('SHIPAY_SECRET_KEY'),
        'client_id'  => env('SHIPAY_CLIENT_ID'),
    ],

    'apifootball' => [
        'api_url' => env('APIFOOTBALL_API_URL', 'https://v3.football.api-sports.io'),
        'api_key' => env('APIFOOTBALL_API_KEY'),
        'urls' => [
            'football'   => 'https://v3.football.api-sports.io',
            'basketball' => 'https://v1.basketball.api-sports.io',
            'volleyball' => 'https://v1.volleyball.api-sports.io',
            'mma'        => 'https://v1.mma.api-sports.io',
        ],
    ],

    'footballdata' => [
        'api_url' => env('FOOTBALLDATA_API_URL', 'https://api.football-data.org/v4'),
        'api_key' => env('FOOTBALLDATA_API_KEY'),
    ],

    'scraper' => [
        'mode'        => env('SCRAPER_MODE', 'master'),
        'enabled'     => env('SCRAPER_JOGADINHA_ENABLED', false),
        'master_url'  => env('SCRAPER_MASTER_URL'),
        'master_token'=> env('SCRAPER_MASTER_TOKEN'),
    ],

    'playfiver' => [
        'token'  => env('API_PLAYFIVER_TOKEN', ''),
        'secret' => env('API_PLAYFIVER_SECRET', ''),
    ],

    'primepag' => [
        'client_id'     => env('PRIMEPAG_CLIENT_ID', ''),
        'client_secret' => env('PRIMEPAG_CLIENT_SECRET', ''),
        'base_url'      => 'https://api.primepag.com.br',
    ],

];
