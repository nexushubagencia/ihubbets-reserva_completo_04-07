<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $fillable = [
        'site_id', 'legacy_aposta_id', 'user_id', 'online_user_id', 'manager_id',
        'cambista_id', 'client_name', 'amount', 'commission_percent', 'commission_amount',
        'manager_commission_amount', 'potential_payout', 'status', 'selections',
        'ticket_code', 'cashout_pin', 'cash_out_amount', 'can_cash_out', 'is_bonus_bet'
    ];

    protected $casts = [
        'selections' => 'json',
    ];

    public function items()
    {
        return $this->hasMany(BetItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('site', function ($query) {
            $query->where('site_id', app('tenant.site_id'));
        });
    }
}
