<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerTemplate extends Model
{
    protected $fillable = [
        'name', 'type', 'accent_color', 'overlay_opacity',
        'active', 'is_active', 'background_url', 'preview_url',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'is_active'       => 'boolean',
        'overlay_opacity' => 'float',
    ];

    public function getActiveAttribute()
    {
        return $this->attributes['active'] ?? $this->attributes['is_active'] ?? false;
    }
}
