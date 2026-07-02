<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreBet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'site_id',
        'selections',
        'total_stake',
        'possible_return',
        'client_name',
        'modalidade',
        'tipo',
        'concurso'
    ];

    protected $casts = [
        'selections' => 'array'
    ];
}
