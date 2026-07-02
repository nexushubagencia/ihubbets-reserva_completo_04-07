<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierCloseout extends Model
{
    protected $table = 'cashier_closeouts';

    protected $fillable = [
        'user_id',
        'closed_by',
        'site_id',
        'turno',
        'total_entradas',
        'total_saidas',
        'total_comissoes',
        'total_lancamentos',
        'total_entradas_abertas',
        'quantidade_apostas',
        'total_liquido',
        'comissao_gerente',
        'saldo_anterior',
        'saldo_final',
        'detalhes',
    ];

    protected function casts(): array
    {
        return [
            'total_entradas' => 'decimal:2',
            'total_saidas' => 'decimal:2',
            'total_comissoes' => 'decimal:2',
            'total_lancamentos' => 'decimal:2',
            'total_entradas_abertas' => 'decimal:2',
            'total_liquido' => 'decimal:2',
            'comissao_gerente' => 'decimal:2',
            'saldo_anterior' => 'decimal:2',
            'saldo_final' => 'decimal:2',
            'detalhes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeByTurno($query, $turno)
    {
        return $query->where('turno', $turno);
    }

    public function getTurnoLabelAttribute(): string
    {
        return match($this->turno) {
            'manha' => 'Manha',
            'tarde' => 'Tarde',
            'noite' => 'Noite',
            'integral' => 'Integral',
            default => ucfirst($this->turno),
        };
    }
}
