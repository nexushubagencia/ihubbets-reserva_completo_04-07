<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aposta extends Model
{
    protected $with = ['palpites'];

    protected $fillable = [
        'id',
        'site_id',
        'user_id',
        'gerente_id',
        'tipo',
        'modalidade',
        'valor_apostado',
        'retorno_possivel',
        'retorno_cambista',
        'comicao',
        'cash_out_amount',
        'status',
        'codigo_bilhete',
        'total_palpites',
        'vendedor',
        'cliente',
        'cotacao',
        'andamento_palpites',
        'acertos_palpites',
        'erros_palpites',
        'devolvidos_palpites',
        'resultado_loto',
        'rodada_id'
    ];


        public function user() 
        {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function palpites() 
        {
            return $this->hasMany(Palpite::class);
        }

        public function palpitesLoto() 
        {
            return $this->hasMany(PalpiteLoto::class)->orderBy('dezena', 'asc');
        }

        public function palpitesBolao() 
        {
            return $this->hasMany(PalpiteBolao::class, 'aposta_id');
        }

        public function rodada()
        {
            return $this->belongsTo(Rodada::class, 'rodada_id');
        }
}
