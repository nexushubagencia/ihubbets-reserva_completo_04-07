<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'file_path'
    ];
}
