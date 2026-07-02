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
        'valor',
        'pix',
        'tipo_pix',
    ];

    protected $casts = [
        'valor' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
