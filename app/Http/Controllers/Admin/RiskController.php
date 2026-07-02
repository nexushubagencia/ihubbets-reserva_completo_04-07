<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use Illuminate\Support\Facades\DB;

class RiskController extends Controller
{
    /**
     * Dashboard de Gerenciamento de Riscos
     */
    public function dashboard(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $option = $request->get('filter', 'potential_payout');

        $query = Bet::withoutGlobalScopes()
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->withCount('items as total_selections');

        // Ordenação dinâmica baseada no risco
        if ($option === 'potential_payout' || $option === 'potential_return') {
            $query->orderBy('potential_payout', 'desc');
        } elseif ($option === 'amount') {
            $query->orderBy('amount', 'desc');
        } elseif ($option === 'selections') {
            $query->orderBy('total_selections', 'desc');
        }

        $topRisks = $query->limit(50)->get();

        return view('admin.risk.dashboard', compact('topRisks'));
    }

    /**
     * Mapa de Apostas (Exposição por Confronto)
     * Agrupa por jogo + seleção para revelar onde a banca tem mais risco.
     */
    public function betMap()
    {
        $siteId = config('tenant.site_id', 1);

        // bet_items é a tabela de palpites no schema V2
        $mapData = DB::table('bets')
            ->join('bet_items', 'bets.id', '=', 'bet_items.bet_id')
            ->join('manual_events', 'bet_items.match_id', '=', 'manual_events.id')
            ->select(
                'manual_events.title as confronto',
                'manual_events.start_time as data_evento',
                'bet_items.market_name as mercado',
                'bet_items.selection_label as opcao',
                DB::raw('SUM(bets.amount) as total_apostado'),
                DB::raw('SUM(bets.potential_payout) as exposicao_maxima'),
                DB::raw('COUNT(bets.id) as quantidade_bilhetes')
            )
            ->where('bets.site_id', $siteId)
            ->where('bets.status', 'open')
            ->where('bet_items.status', 'pending')
            ->groupBy('manual_events.id', 'manual_events.title', 'manual_events.start_time', 'bet_items.market_name', 'bet_items.selection_label')
            ->orderBy('total_apostado', 'desc')
            ->get();

        return view('admin.risk.map', compact('mapData'));
    }
}
