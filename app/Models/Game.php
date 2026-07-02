<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'matchs';
    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function odds()
    {
        return $this->hasMany(Odd::class, 'event_id', 'event_id');
    }
}
