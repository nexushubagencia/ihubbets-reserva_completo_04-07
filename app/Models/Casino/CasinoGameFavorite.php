<?php

namespace App\Models\Casino;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoGameFavorite extends Model
{
    use HasFactory;

    protected $table = 'casino_game_favorites';

    protected $fillable = ['user_id', 'game_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(CasinoGame::class, 'game_id', 'id');
    }
}
