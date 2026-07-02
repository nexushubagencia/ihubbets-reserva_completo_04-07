<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerTemplate extends Model
{
    protected $fillable = [
        'name', 'type', 'accent_color', 'overlay_opacity',
        'active', 'background_url', 'preview_url',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'overlay_opacity' => 'float',
    ];
}
