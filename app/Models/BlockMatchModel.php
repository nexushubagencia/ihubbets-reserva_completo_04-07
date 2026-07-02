<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockMatchModel extends Model
{
    protected $table = 'block_matchs';

    protected $fillable = ['event_id', 'site_id', 'date', 'sport', 'confronto'];
}
