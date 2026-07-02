<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchEvent extends Model
{
    protected $table = 'matchs'; // Compatibilidade com a tabela legada que Game.php também asssina
    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function odds()
    {
        return $this->hasMany(Odd::class, 'event_id', 'event_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
