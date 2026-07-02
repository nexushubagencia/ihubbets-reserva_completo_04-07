<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualEvent extends Model
{
    protected $fillable = [
        'category_id',
        'site_id',
        'title',
        'home_team',
        'away_team',
        'league_name',
        'home_flag',
        'away_flag',
        'odd_home',
        'odd_draw',
        'odd_away',
        'odd_btts_yes',
        'odd_btts_no',
        'odd_over_25',
        'odd_under_25',
        'has_extra_markets',
        'score',
        'start_time',
        'status',
        'is_featured',
        'img_featured',
        'cor_badge',
        'extra_markets'
    ];

    protected $casts = [
        'extra_markets' => 'array',
        'start_time' => 'datetime'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function category()
    {
        return $this->belongsTo(ManualCategory::class, 'category_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('site', function ($query) {
            $query->where('site_id', config('tenant.site_id', 1));
        });
    }
}
