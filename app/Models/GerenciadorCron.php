<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GerenciadorCron extends Model
{
    use HasFactory;

    protected $table = 'gerenciador_crons';

    protected $fillable = [
        'action',
    ];
}
