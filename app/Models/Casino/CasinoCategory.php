<?php

namespace App\Models\Casino;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CasinoCategory extends Model
{
    use HasFactory;

    protected $table = 'casino_categories';

    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
    ];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(CasinoGame::class, 'casino_category_game', 'category_id', 'game_id');
    }
}
