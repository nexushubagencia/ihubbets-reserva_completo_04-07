<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeagueLive extends Model
{
    use HasFactory;

    protected $table = 'league_lives';

    protected $fillable = [
        'league_id',
        'league',
        'site_id',
    ];
}
