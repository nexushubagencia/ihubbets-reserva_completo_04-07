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
        'cashout_delay_seconds',
        'theme_color',
        'button_odds_color',
        'allow_mixed_bets',
        'google_analytics_enabled',
        'google_analytics_script',
        'meta_pixel_enabled',
        'meta_pixel_id',
        'api_provider',
        'cambista_pode_cancelar',
        'tempo_limite_camb_cancela_aposta',
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
