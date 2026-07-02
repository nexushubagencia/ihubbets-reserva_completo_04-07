<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OddMarcket extends Model
{
    use HasFactory;

    protected $table = 'odd_marckets';

    protected $fillable = [
        'mercado',
        'odd',
    ];
}
