<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Football Configuration (api-sports.io)
    |--------------------------------------------------------------------------
    |
    | Configurações da API-Football para o sistema IHUB.
    | Rate limit: 100 requests/dia (plano Free)
    |
    */

    'api_url' => env('APIFOOTBALL_API_URL', 'https://v3.football.api-sports.io'),
    'api_key' => env('APIFOOTBALL_API_KEY', ''),

    'urls' => [
        'football'   => 'https://v3.football.api-sports.io',
        'basketball' => 'https://v1.basketball.api-sports.io',
        'volleyball' => 'https://v1.volleyball.api-sports.io',
        'mma'        => 'https://v1.mma.api-sports.io',
    ],

    'proxy' => env('API_FOOTBALL_PROXY', null),

    // Rate limiting
    'max_daily_requests' => (int) env('APIFOOTBALL_MAX_DAILY', 95),
    'request_delay_seconds' => (int) env('APIFOOTBALL_DELAY', 7),
    'counter_file' => 'logs/apifootball_requests_today.log',
    'lock_file' => 'logs/apifootball_insert.lock',
    'log_file' => 'logs/apifootball_insert.log',

    // Sports mapping
    'sports' => [
        'football'   => ['sport_id' => 1, 'sport_name' => 'Futebol'],
        'basketball' => ['sport_id' => 2, 'sport_name' => 'Basquete'],
        'volleyball' => ['sport_id' => 3, 'sport_name' => 'Volei'],
        'mma'        => ['sport_id' => 4, 'sport_name' => 'MMA/UFC'],
    ],

    // Football-data.org (fallback)
    'footballdata_url' => env('FOOTBALLDATA_API_URL', 'https://api.football-data.org/v4'),
    'footballdata_key' => env('FOOTBALLDATA_API_KEY', ''),

    // Assets CDN (team/league logos)
    'assets_cdn_url' => env('ASSETS_CDN_URL', 'https://assets.betsapi.com/v1/'),

];
