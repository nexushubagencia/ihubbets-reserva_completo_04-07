<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'pix_client_id', 'pix_client_secret', 'ga_code', 'pixel_id'
    ];

    protected $casts = [
        'pix_client_id' => 'encrypted',
        'pix_client_secret' => 'encrypted',
        'ga_code' => 'encrypted',
        'pixel_id' => 'encrypted',
        'seniha_enabled' => 'boolean',
        'queniha_enabled' => 'boolean',
        'custom_colors' => 'array',
        'active_custom_colors' => 'boolean',
        'custom_themes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function settings()
    {
        return $this->hasOne(SiteSetting::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
