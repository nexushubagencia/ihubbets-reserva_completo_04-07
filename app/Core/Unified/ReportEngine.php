<?php

namespace App\Core\Unified;

use App\Models\User;
use App\Models\Bet;
use Illuminate\Support\Facades\DB;

/**
 * 📊 ReportEngine - Gerador de Relatórios Unificado
 * 
 * Processa dados históricos por períodos, gerentes e cambistas.
 */
class ReportEngine
{
    /**
     * Relatório por Gerente(s)
     */
    public static function getManagerReport(array $filters)
    {
        $siteId = config('tenant.site_id', 1);
        $date1 = $filters['date_start'] . ' 00:00:00';
        $date2 = $filters['date_end'] . ' 23:59:59';
        
        $query = User::where('site_id', $siteId)->where('role', 'manager');
        if (isset($filters['manager_id']) && $filters['manager_id'] !== 'Todos') {
            $query->where('id', $filters['manager_id']);
        }

        $managers = $query->get();
        $report = [];

        foreach ($managers as $manager) {
            $stats = Bet::select(
                DB::raw('SUM(amount) as total_apostado'),
                DB::raw('SUM(commission_amount) as total_comissoes'),
                DB::raw('COUNT(id) as total_bilhetes')
            )
            ->where('site_id', $siteId)
            ->where('manager_id', $manager->id)
            ->whereBetween('created_at', [$date1, $date2])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->first();

            $prizes = Bet::where('site_id', $siteId)
                ->where('manager_id', $manager->id)
                ->whereBetween('created_at', [$date1, $date2])
                ->where('status', 'won')
                ->sum('potential_payout');

            $totalApostado = $stats->total_apostado ?? 0;
            $totalComissoes = $stats->total_comissoes ?? 0;
            $totalPrizes = $prizes ?? 0;
            
            $netRevenue = $totalApostado - ($totalComissoes + $totalPrizes);
            
            $managerCommission = 0;
            if ($netRevenue > 0) {
                $managerCommission = ($netRevenue * $manager->manager_commission_rate) / 100;
            }

            $report[] = [
                'name' => $manager->name,
                'quantidade' => $stats->total_bilhetes ?? 0,
                'entradas' => $totalApostado,
                'saidas' => $totalPrizes,
                'comissoes_vendedores' => $totalComissoes,
                'comissao_gerente' => $managerCommission,
                'saldo_liquido' => $netRevenue - $managerCommission
            ];
        }

        return $report;
    }

    /**
     * Relatório por Cambista(s)
     */
    public static function getSellerReport(array $filters)
    {
        $siteId = config('tenant.site_id', 1);
        $date1 = $filters['date_start'] . ' 00:00:00';
        $date2 = $filters['date_end'] . ' 23:59:59';
        
        $query = User::where('site_id', $siteId)->where('role', 'seller');
        
        if (auth()->user()->role === 'manager') {
            $query->where('manager_id', auth()->user()->id);
        }

        if (isset($filters['seller_id']) && $filters['seller_id'] !== 'Todos') {
            $query->where('id', $filters['seller_id']);
        }

        $sellers = $query->get();
        $report = [];

        foreach ($sellers as $seller) {
            $stats = Bet::select(
                DB::raw('SUM(amount) as total_apostado'),
                DB::raw('SUM(commission_amount) as total_comissoes'),
                DB::raw('COUNT(id) as total_bilhetes')
            )
            ->where('site_id', $siteId)
            ->where('user_id', $seller->id)
            ->whereBetween('created_at', [$date1, $date2])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->first();

            $prizes = Bet::where('site_id', $siteId)
                ->where('user_id', $seller->id)
                ->whereBetween('created_at', [$date1, $date2])
                ->where('status', 'won')
                ->sum('potential_payout');

            $report[] = [
                'name' => $seller->name,
                'quantidade' => $stats->total_bilhetes ?? 0,
                'entradas' => $stats->total_apostado ?? 0,
                'saidas' => $prizes ?? 0,
                'comissoes' => $stats->total_comissoes ?? 0,
                'saldo' => ($stats->total_apostado ?? 0) - (($stats->total_comissoes ?? 0) + ($prizes ?? 0))
            ];
        }

        return $report;
    }
}
