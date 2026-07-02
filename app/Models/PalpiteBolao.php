<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PalpiteBolao extends Model
{
    protected $table = 'palpite_bolao';

    protected $fillable = [
        'aposta_id', 'rodada_id', 'match_id', 'home', 'away',
        'mercado', 'palpite', 'status', 'resultado',
    ];

    public function aposta()
    {
        return $this->belongsTo(Aposta::class, 'aposta_id');
    }

    public function rodada()
    {
        return $this->belongsTo(Rodada::class, 'rodada_id');
    }
}
