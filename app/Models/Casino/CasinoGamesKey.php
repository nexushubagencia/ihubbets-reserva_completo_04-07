<?php

namespace App\Models\Casino;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasinoGamesKey extends Model
{
    use HasFactory;

    protected $table = 'casino_games_keys';

    protected $fillable = [
        'merchant_url',
        'merchant_id',
        'merchant_key',

        // VeniX
        'venix_agent_code',
        'venix_agent_token',
        'venix_agent_secret',

        // PlayIGaming
        'pig_agent_code',
        'pig_agent_token',
        'pig_agent_secret',

        // PlayGaming
        'play_gaming_hall',
        'play_gaming_key',
        'play_gaming_login',

        // Games2Api
        'games2_agent_code',
        'games2_agent_token',
        'games2_agent_secret_key',
        'games2_api_endpoint',

        // EverGame
        'evergame_agent_code',
        'evergame_agent_token',
        'evergame_api_endpoint',

        // WorldSlot
        'worldslot_agent_code',
        'worldslot_agent_token',
        'worldslot_agent_secret_key',
        'worldslot_api_endpoint',

        // Fivers
        'agent_code',
        'agent_token',
        'agent_secret_key',
        'api_endpoint',

        // Salsa
        'salsa_base_uri',
        'salsa_pn',
        'salsa_key',

        // Vibra
        'vibra_site_id',
        'vibra_game_mode',

        // MaxApiGames
        'maxapigames_agent_code',
        'maxapigames_agent_token',
        'maxapigames_agent_secret',
    ];

    protected $hidden = ['updated_at'];
}
