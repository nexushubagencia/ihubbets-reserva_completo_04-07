<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotoResult extends Model
{
    protected $table = 'loto_results';

    protected $fillable = ['concurso', 'tipo', 'data_sorteio', 'dezenas'];

    protected $casts = [
        'dezenas' => 'array',
        'data_sorteio' => 'date',
    ];
}
