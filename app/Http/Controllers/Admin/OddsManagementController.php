<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Odd;
use Illuminate\Support\Facades\DB;

class OddsManagementController extends Controller
{
    /**
     * View de Gerenciamento de Odds e Mercados
     */
    public function index()
    {
        return view('admin.markets.odds');
    }

    /**
     * Ajuste em Massa (%) para uma Liga específica
     */
    public function adjustLeagueOdds(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $leagueId = $request->league_id;
        $multiplier = 1 + ($request->percentage / 100);

        // Atualiza todas as odds daquela liga para o site
        DB::table('odds')
            ->where('site_id', $siteId)
            ->where('league_id', $leagueId)
            ->update([
                'cotacao' => DB::raw("cotacao * $multiplier"),
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => "Odds da liga ajustadas em {$request->percentage}%"]);
    }

    /**
     * Bloquear/Desbloquear um Mercado Globalmente
     */
    public function toggleMarket(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $marketName = $request->market_name;
        $status = $request->status; // active / blocked

        DB::table('site_market_configs')->updateOrInsert(
            ['site_id' => $siteId, 'market_name' => $marketName],
            ['status' => $status, 'updated_at' => now()]
        );

        return response()->json(['success' => true, 'message' => "Mercado {$marketName} atualizado para {$status}"]);
    }
}
