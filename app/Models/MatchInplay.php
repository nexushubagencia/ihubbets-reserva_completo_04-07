<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MatchInplay extends Model
{
    use HasFactory;

    protected $table = 'match_inplays';

    protected $fillable = [
        'match_id',
        'matchs',
        'site_id',
    ];

    protected $casts = [
        'matchs' => 'array',
    ];
}
