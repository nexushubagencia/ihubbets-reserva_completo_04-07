<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayfiverProvider extends Model
{
    protected $table = 'playfiver_providers';

    protected $fillable = [
        'provider_id',
        'name',
        'image_url',
        'wallet_name',
        'status',
        'site_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
