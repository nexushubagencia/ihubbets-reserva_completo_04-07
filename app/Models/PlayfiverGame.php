<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayfiverGame extends Model
{
    protected $table = 'playfiver_games';

    protected $fillable = [
        'game_code',
        'name',
        'image_url',
        'provider',
        'status',
        'original',
        'is_popular',
        'site_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'original' => 'boolean',
        'is_popular' => 'boolean',
    ];
}
