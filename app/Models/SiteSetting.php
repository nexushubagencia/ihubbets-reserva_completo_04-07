<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_id',
        'min_bet_amount',
        'max_bet_amount',
        'max_payout',
        'min_withdrawal',
        'cashout_tax',
        'cashout_delay_seconds'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('site', function ($query) {
            $query->where('site_id', config('tenant.site_id', 1));
        });
    }
}
