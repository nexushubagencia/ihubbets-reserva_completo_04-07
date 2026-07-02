<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMarketAdjustment extends Model
{
    protected $fillable = ['site_id', 'user_id', 'market_name', 'adjustment_percent', 'status'];
}
