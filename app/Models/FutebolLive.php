<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FutebolLive extends Model
{
    use HasFactory;

    protected $table = 'futebol_lives';

    protected $fillable = [
        'dados',
        'site_id',
    ];

    protected $casts = [
        'dados' => 'array',
    ];
}
