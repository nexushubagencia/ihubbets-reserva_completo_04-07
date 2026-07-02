<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rodada extends Model
{
    protected $fillable = [
        'site_id', 'nome', 'status', 'premio_max', 'premio_primeiro',
        'premio_segundo', 'premio_terceiro', 'quantidade', 'arrecadado',
        'data_fechamento',
    ];

    public function palpites()
    {
        return $this->hasMany(PalpiteBolao::class, 'rodada_id');
    }

    public function apostas()
    {
        return $this->hasMany(ApostaBolao::class, 'rodada_id');
    }
}
