<?php

namespace App\Models\Casino;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoOrder extends Model
{
    use HasFactory;

    protected $table = 'casino_orders';

    protected $fillable = [
        'user_id',
        'session_id',
        'transaction_id',
        'game',
        'game_uuid',
        'type',
        'type_money',
        'amount',
        'providers',
        'refunded',
        'round_id',
        'status',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded' => 'boolean',
        'status' => 'integer',
        'payload' => 'array',
    ];

    protected $appends = ['created_at_formatted'];

    public function getCreatedAtFormattedAttribute(): ?string
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('d/m/Y H:i') : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
