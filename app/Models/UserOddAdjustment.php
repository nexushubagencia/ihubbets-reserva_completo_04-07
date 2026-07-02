<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOddAdjustment extends Model
{
    protected $fillable = ['site_id', 'user_id', 'match_id', 'adjustment_percent', 'status'];
}
