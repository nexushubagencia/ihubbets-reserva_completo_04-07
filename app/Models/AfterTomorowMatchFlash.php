<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AfterTomorowMatchFlash extends Model
{
    protected $table = 'afer_tomorow_match_flashes';

    protected $fillable = ['dados', 'site_id', 'sport_id'];
}
