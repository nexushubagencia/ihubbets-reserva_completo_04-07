<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApostasCassino extends Model
{
    protected $table = 'apostas_cassino';

    protected $fillable = [
        'bet_id',
        'user_login',
        'game_id',
        'bet',
        'win',
        'bet_info',
        'site_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_login');
    }

    public function game()
    {
        return $this->belongsTo(PlayfiverGame::class, 'game_id');
    }
}
