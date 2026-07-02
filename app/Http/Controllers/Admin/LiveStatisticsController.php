<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LiveStatisticsController extends Controller
{
    /**
     * Dashboard Principal de Estatísticas Real-time
     */
    public function index()
    {
        return view('admin.statistics.live');
    }

    /**
     * API que alimenta os gráficos (Refresh via AJAX/Vue)
     */
    public function getLiveData()
    {
        $siteId = config('tenant.site_id', 1);
        $today = Carbon::today();
        $oneHourAgo = Carbon::now()->subHour();

        // 1. KPIs Rápidos (Hoje)
        $kpis = [
            'total_in'       => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $today)->sum('amount'),
            'total_out'      => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $today)->where('status', 'won')->sum('potential_payout'),
            'lucro_hoje'     => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $today)->where('status', '!=', 'cancelled')->selectRaw('SUM(amount) - SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as lucro')->value('lucro'),
            'bets_count'     => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $today)->count(),
            'bets_open'      => DB::table('bets')->where('site_id', $siteId)->where('status', 'open')->count(),
            'active_players' => User::where('site_id', $siteId)->where('updated_at', '>', Carbon::now()->subMinutes(15))->count(),
        ];

        // 2. Gráfico de Volume (Últimas 12 Horas)
        $chartVolume = DB::table('bets')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('SUM(amount) as total'))
            ->where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // 3. Alertas de Alto Risco (Últimos 30 min)
        $alerts = Bet::where('site_id', $siteId)
            ->where('created_at', '>', Carbon::now()->subMinutes(30))
            ->where('amount', '>', 500) // Exemplo: Apostas acima de 500 reais
            ->limit(5)
            ->get();

        return response()->json([
            'kpis' => $kpis,
            'chart_volume' => $chartVolume,
            'recent_alerts' => $alerts,
            'server_time' => Carbon::now()->toTimeString()
        ]);
    }
}
