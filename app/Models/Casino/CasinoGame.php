<?php

namespace App\Models\Casino;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasinoGame extends Model
{
    use HasFactory;

    protected $table = 'casino_games';

    protected $fillable = [
        'provider_id',
        'game_server_url',
        'game_id',
        'game_id_maxapi',
        'game_name',
        'game_code',
        'game_type',
        'description',
        'cover',
        'status',
        'technology',
        'has_lobby',
        'is_mobile',
        'has_freespins',
        'has_tables',
        'only_demo',
        'rtp',
        'distribution',
        'views',
        'is_featured',
        'show_home',
    ];

    protected $casts = [
        'status' => 'boolean',
        'has_lobby' => 'boolean',
        'is_mobile' => 'boolean',
        'has_freespins' => 'boolean',
        'has_tables' => 'boolean',
        'only_demo' => 'boolean',
        'is_featured' => 'boolean',
        'show_home' => 'boolean',
        'views' => 'integer',
        'rtp' => 'decimal:2',
    ];

    protected $appends = ['has_favorite', 'has_like', 'created_at_formatted'];

    public function getHasFavoriteAttribute(): bool
    {
        if (!auth()->check() || empty($this->attributes['id'])) {
            return false;
        }
        return CasinoGameFavorite::where('user_id', auth()->id())
            ->where('game_id', $this->attributes['id'])
            ->exists();
    }

    public function getHasLikeAttribute(): bool
    {
        if (!auth()->check() || empty($this->attributes['id'])) {
            return false;
        }
        return CasinoGameLike::where('user_id', auth()->id())
            ->where('game_id', $this->attributes['id'])
            ->exists();
    }

    public function getCreatedAtFormattedAttribute(): ?string
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d') : null;
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CasinoProvider::class, 'provider_id', 'id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CasinoCategory::class, 'casino_category_game', 'game_id', 'category_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(CasinoGameFavorite::class, 'game_id', 'id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CasinoGameLike::class, 'game_id', 'id');
    }
}
