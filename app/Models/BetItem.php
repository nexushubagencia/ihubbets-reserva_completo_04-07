<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetItem extends Model
{
    protected $fillable = [
        'bet_id', 'match_id', 'league_name', 'home_team', 'away_team',
        'market_name', 'selection_label', 'selection_odd', 'status'
    ];

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }
}
