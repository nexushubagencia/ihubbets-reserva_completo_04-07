<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockOddMatchModel extends Model
{
    protected $table = 'block_odd_matches';

    protected $fillable = ['odd_id', 'odd_uid', 'odd', 'cotacao', 'status', 'site_id'];
}
