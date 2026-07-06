<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'site_id',
        'user_id',
        'subject',
        'message',
        'status',
        'admin_response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
