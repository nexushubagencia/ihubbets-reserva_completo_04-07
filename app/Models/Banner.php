<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'site_id',
        'title',
        'image_path',
        'link',
        'link_url',
        'position',
        'order_index',
        'display_to',
        'status',
        'start_date',
        'end_date'
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
