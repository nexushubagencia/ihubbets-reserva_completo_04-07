<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'site_id', 'code', 'type', 'value', 'min_deposit', 
        'rollover_multiplier', 'expires_at', 'is_active'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
