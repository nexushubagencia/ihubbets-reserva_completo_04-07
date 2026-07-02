<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teams extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'site_id',
    ];
}
