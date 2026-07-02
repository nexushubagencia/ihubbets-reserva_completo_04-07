<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomTheme extends Model
{
    protected $fillable = ['site_id', 'name', 'colors'];

    protected $casts = [
        'colors' => 'array'
    ];
}
