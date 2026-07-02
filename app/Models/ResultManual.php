<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResultManual extends Model
{
    use HasFactory;

    protected $table = 'result_manuals';

    protected $fillable = [
        'event_id',
        'score_ful_home',
        'score_ful_away',
        'score_half_home',
        'score_half_away',
        'site_id',
    ];
}
