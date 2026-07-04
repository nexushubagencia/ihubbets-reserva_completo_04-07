<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saque extends Model
{
    protected $table = 'saques';

    protected $fillable = [
        'user_id',
        'site_id',
        'status',
        'paid_at',
        'paid_amount',
        'gateway_response',
        'valor',
        'pix',
        'tipo_pix',
        'admin_note',
    ];

    protected $casts = [
        'valor' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
