<?php

namespace App\Models\Casino;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasinoProvider extends Model
{
    use HasFactory;

    protected $table = 'casino_providers';

    protected $fillable = [
        'code',
        'name',
        'rtp',
        'status',
        'distribution',
        'views',
    ];

    protected $casts = [
        'status' => 'boolean',
        'views' => 'integer',
        'rtp' => 'decimal:2',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(CasinoGame::class, 'provider_id', 'id')
            ->where('status', 1);
    }
}
