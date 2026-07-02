<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Marcket extends Model
{
    use HasFactory;

    protected $table = 'marckets';

    protected $fillable = [
        'name',
        'order',
    ];

    public function oddMarckets()
    {
        return $this->hasMany(OddMarcket::class, 'mercado', 'name');
    }
}
