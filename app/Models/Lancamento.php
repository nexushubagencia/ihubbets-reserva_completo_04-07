<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    protected $fillable = ['user_id', 'tipo', 'descricao', 'valor', 'site_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
