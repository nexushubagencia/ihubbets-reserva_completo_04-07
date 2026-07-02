<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConfigMercados;
use App\Models\Odd;
use App\Models\BlockOddMatch;

class MercadosController extends Controller
{
    public function indexView()
    {
        return view('admin.bloqueio-mercado');
    }

    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        
        // Se não houver mercados configurados para o usuário atual, 
        // retorna os do site_id 1 (Super Admin) como fallback
        $mercados = ConfigMercados::where('site_id', $siteId)
            ->where('user_id', auth()->user()->id)
            ->get();

        if ($mercados->isEmpty()) {
            // Se o usuário logado não tem mercados personalizados, pega os do sistema (user_id=4 ou qualquer um do site)
            $mercados = ConfigMercados::where('site_id', $siteId)->get();
        }

        return $mercados;
    }

    public function mercadoUser($id)
    {
        return ConfigMercados::where('user_id', $id)->get();
    }

    /**
     * Mostra todas as odds de um confronto (game) agrupadas por mercado
     */
    public function show($id)
    {
        $siteId = config('tenant.site_id', 1);

        // Odds bloqueadas ou alteradas manualmente para este site
        $odds_alteradas = BlockOddMatch::where('site_id', $siteId)->get();
        $arr_odd_alterada = [];
        foreach ($odds_alteradas as $oa) {
            $arr_odd_alterada[] = $oa->odd_id . $oa->odd;
        }

        $mercados = Odd::select('market_name')
            ->where('event_id', $id)
            ->groupBy('market_name')
            ->get();

        $return = [];
        foreach ($mercados as $m) {
            $mercadoItem = [
                'name' => $m->market_name,
                'odds' => []
            ];

            $odds = Odd::where('event_id', $id)
                ->where('market_name', $m->market_name)
                ->orderBy('label', 'asc')
                ->get();

            foreach ($odds as $o) {
                $status = $o->status ?? 1;
                $cotacao = (float)$o->value;
                $alterada = 0;

                // Verifica se existe bloqueio manual na tabela legacy
                if (in_array($o->event_id . $o->label, $arr_odd_alterada)) {
                    $oa = BlockOddMatch::where('odd_id', $o->event_id)
                        ->where('odd', $o->label)
                        ->where('site_id', $siteId)
                        ->first();
                    if ($oa) {
                        $cotacao = (float)$oa->cotacao;
                        $status = $oa->status;
                        $alterada = 1;
                    }
                }

                $mercadoItem['odds'][] = [
                    'id' => $o->id,
                    'odd' => $o->label, // Nome da opção (Casa, Empate, etc)
                    'cotacao' => round($cotacao, 2),
                    'status' => $status,
                    'alterada' => $alterada
                ];
            }
            $return[] = $mercadoItem;
        }

        return response()->json($return);
    }

    public function update(Request $request, $id)
    {
        $mercado = ConfigMercados::find($id);
        if ($mercado) {
            $mercado->update($request->all());
            return response()->json(['success' => true, 'message' => 'Mercado atualizado!']);
        }
        return response()->json(['success' => false, 'message' => 'Mercado não encontrado.'], 404);
    }
}
