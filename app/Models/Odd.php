<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    protected $fillable = [
        'event_id', 
        'market_name', 
        'label', 
        'value', 
        'type',
        'status' // Adicionado status para controle de bloqueio
    ];

    /**
     * Relacionamento com o Jogo
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'event_id', 'event_id');
    }
}
