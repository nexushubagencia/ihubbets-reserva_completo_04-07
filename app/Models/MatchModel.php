<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchModel extends Model
{
    protected $table = 'matchs';
 
    protected $fillable = [
       'site_id',
       'event_id',
       'our_event_id',
       'sport_id',
       'sport_name',
       'league_id',
       'league',
       'home',
       'away',
       'home_true',
       'away_true',
       'image_id_home',
       'image_id_away',
       'score',
       'time_status',
       'time',
       'date',
       'confronto',
       'visible',
       'order',
       'schedule',
       'live_status'
    ];

    /**
     * Relacionamento com Odds (1X2 Padrão)
     */
    public function odds() {
        return $this->hasMany(Odd::class, 'event_id', 'event_id')
                   ->where('market_name', 'Vencedor do Encontro')
                   ->orderBy('id', 'asc');
    }

    /**
     * Relacionamento com todas as Odds do mercado
     */
    public function fullOdds() {
       return $this->hasMany(Odd::class, 'event_id', 'event_id')
                   ->orderBy('market_name', 'asc');
    }
}
