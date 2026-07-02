<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalOddAdjustment extends Model
{
    protected $fillable = ['site_id', 'sport', 'league_id', 'league_name', 'adjustment_percent', 'status'];
}
