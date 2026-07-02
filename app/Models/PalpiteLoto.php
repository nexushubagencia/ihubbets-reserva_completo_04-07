<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PalpiteLoto extends Model
{
    protected $fillable = ['aposta_id', 'concurso', 'tipo', 'dezena', 'status'];

    public function aposta()
    {
        return $this->belongsTo(Aposta::class, 'aposta_id');
    }
}
