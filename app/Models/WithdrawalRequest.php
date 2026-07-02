<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'user_id',
        'amount',
        'pix_key',
        'pix_key_type',
        'status',
        'admin_note',
        'gateway_ref',
        'type',
        'receipt_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
