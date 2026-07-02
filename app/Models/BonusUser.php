<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusUser extends Model
{
    protected $table = 'bonus_user';
    
    protected $fillable = [
        'user_id', 'bonus_id', 'initial_value', 'current_balance', 
        'target_rollover', 'current_rollover', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }
}
