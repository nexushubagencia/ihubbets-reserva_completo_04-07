<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApifootballLeague extends Model
{
    use HasFactory;

    protected $table = 'apifootball_leagues';

    protected $fillable = [
        'league_id',
        'name',
        'country',
        'logo',
        'season',
        'sport',
        'active',
        'site_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
