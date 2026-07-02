<?php

namespace App\Core\Unified;

use App\Models\Bet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 📈 DashboardEngine - Motor de Analytics do Painel
 */
class DashboardEngine
{
    /**
     * Retorna estatísticas rápidas para o topo do dashboard
     */
    public static function getQuickStats($user, $siteId)
    {
        $query = Bet::where('site_id', $siteId);

        if ($user->role === 'manager') {
            $query->where('manager_id', $user->id);
        } elseif ($user->role === 'seller') {
            $query->where('user_id', $user->id);
        }

        $totalBets = (clone $query)->count();
        $totalVolume = (clone $query)->whereNotIn('status', ['cancelled'])->sum('amount');
        $totalWon = (clone $query)->where('status', 'won')->sum('potential_payout');
        $totalOpen = (clone $query)->where('status', 'open')->sum('amount');

        return [
            'total_bets'   => $totalBets,
            'total_volume' => $totalVolume,
            'total_won'    => $totalWon,
            'total_open'   => $totalOpen,
            'profit'       => $totalVolume - $totalWon
        ];
    }

    /**
     * Retorna dados para o gráfico de performance semanal
     */
    public static function getWeeklyPerformance($user, $siteId)
    {
        $days = [];
        $entradas = [];
        $saidas = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $days[] = Carbon::now()->subDays($i)->translatedFormat('D');

            $query = Bet::where('site_id', $siteId)
                        ->whereDate('created_at', $date)
                        ->whereNotIn('status', ['cancelled']);

            if ($user->role === 'manager') {
                $query->where('manager_id', $user->id);
            } elseif ($user->role === 'seller') {
                $query->where('user_id', $user->id);
            }

            $entradas[] = (clone $query)->sum('amount');
            $saidas[]   = (clone $query)->where('status', 'won')->sum('potential_payout');
        }

        return [
            'labels'   => $days,
            'entradas' => $entradas,
            'saidas'   => $saidas
        ];
    }
}
