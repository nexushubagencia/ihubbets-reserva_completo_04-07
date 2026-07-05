<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedMatch extends Model
{
    protected $fillable = [
        'site_id',
        'match_id',
        'manual_event_id',
        'is_manual',
        'sport',
        'home_team',
        'away_team',
        'league_name',
        'match_date',
        'badge_color',
        'background_path',
        'order'
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
