<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalMarketAdjustment extends Model
{
    protected $fillable = ['site_id', 'market_name', 'sport', 'adjustment_percent', 'status'];
}
