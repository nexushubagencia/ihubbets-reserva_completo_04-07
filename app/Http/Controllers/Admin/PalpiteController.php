<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Palpite;
use App\Models\Aposta;

class PalpiteController extends Controller
{
    public function show($id)
    {
        $siteId = app('tenant.site_id');
        $conf = \App\Models\Configuracao::where('site_id', $siteId)->first();
        $comissaoPremio = $conf->comissao_premio ?? 0;

        $b = Aposta::find($id);

        if (!$b) return response()->json([], 404);

        // Calcula dinamicamente o valor líquido para o modal
        $valorPremio = (float) $b->retorno_possivel;
        $taxaPagamento = ($valorPremio * $comissaoPremio) / 100;
        
        $b->retorno_cambista = $valorPremio - $taxaPagamento; // Injeta o valor líquido para o Bilhete.vue
        $b->taxa_pagamento_valor = $taxaPagamento;

        if ($b->modalidade == 'Loto') {
            return Aposta::where('id', $id)
                ->with('palpitesLoto')
                ->get()
                ->map(function($item) use ($b) {
                    $item->retorno_cambista = $b->retorno_cambista;
                    return $item;
                });
        } else {
            return Aposta::where('id', $id)
                ->with('palpites')
                ->get()
                ->map(function($item) use ($b) {
                    $item->retorno_cambista = $b->retorno_cambista;
                    return $item;
                });
        }
    }
}
