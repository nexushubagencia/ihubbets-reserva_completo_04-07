<?php

namespace App\Models\Casino;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasinoCategoryGame extends Model
{
    use HasFactory;

    protected $table = 'casino_category_game';

    protected $fillable = [
        'game_id',
        'category_id',
    ];
}
