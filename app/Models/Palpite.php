<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Palpite extends Model
{
    protected $fillable = [
        'id',
        'aposta_id',
        'match_id',
        'home_team',
        'away_team',
        'market_name',
        'selection_label',
        'selection_odd',
        'status'
    ];
}
