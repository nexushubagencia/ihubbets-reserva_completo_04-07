<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promocao extends Model
{
    use HasFactory;

    protected $table = 'promocoes';

    protected $fillable = [
        'nome',
        'tipo',
        'porcentagem',
        'valor_maximo',
        'rollover_multiplicador',
        'status',
        'site_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'porcentagem' => 'float',
        'valor_maximo' => 'float',
        'rollover_multiplicador' => 'float',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'promocao_ativa_id');
    }
}
