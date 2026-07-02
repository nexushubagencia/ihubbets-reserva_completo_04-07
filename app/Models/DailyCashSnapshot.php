<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCashSnapshot extends Model
{
    protected $table = 'daily_cash_snapshots';

    protected $fillable = [
        'user_id',
        'site_id',
        'snapshot_date',
        'entradas_dia',
        'saidas_dia',
        'comissoes_dia',
        'lancamentos_dia',
        'apostas_dia',
        'lucro_dia',
        'saldo_fechamento',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'entradas_dia' => 'decimal:2',
            'saidas_dia' => 'decimal:2',
            'comissoes_dia' => 'decimal:2',
            'lancamentos_dia' => 'decimal:2',
            'lucro_dia' => 'decimal:2',
            'saldo_fechamento' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('snapshot_date', $date);
    }

    public function scopeBySite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }
}
