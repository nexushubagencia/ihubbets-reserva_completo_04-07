<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function caixaCambista(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $dateStart = $request->get('date_start', Carbon::now()->startOfDay()->toDateTimeString());
        $dateEnd = $request->get('date_end', Carbon::now()->endOfDay()->toDateTimeString());

        // Estatísticas para os cards
        $stats = DB::table('bets')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('SUM(amount) as total_entradas'),
                DB::raw('SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as total_saidas'),
                DB::raw('SUM(amount) - SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as saldo_liquido')
            )
            ->first();

        // Lista de movimentações por cambista
        $movimentacoes = DB::table('master_users')
            ->where('master_users.site_id', $siteId)
            ->where('master_users.role', 'seller')
            ->leftJoin('bets', function($join) use ($dateStart, $dateEnd) {
                $join->on('master_users.id', '=', 'bets.user_id')
                    ->whereBetween('bets.created_at', [$dateStart, $dateEnd])
                    ->where('bets.status', '!=', 'cancelled');
            })
            ->select(
                'master_users.username',
                DB::raw('COUNT(bets.id) as total_apostas'),
                DB::raw('SUM(bets.amount) as entradas'),
                DB::raw('SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END) as saidas'),
                DB::raw('SUM(bets.commission_amount) as total_comissao'),
                DB::raw('SUM(bets.amount) - (SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END) + SUM(bets.commission_amount)) as saldo')
            )
            ->groupBy('master_users.id', 'master_users.username')
            ->get();

        return view('admin.caixa-adm-cambista', compact('stats', 'movimentacoes', 'dateStart', 'dateEnd'));
    }

    public function caixaGerente(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $dateStart = $request->get('date_start', Carbon::now()->startOfDay()->toDateTimeString());
        $dateEnd = $request->get('date_end', Carbon::now()->endOfDay()->toDateTimeString());

        // Estatísticas para os cards (Escopado por gerente se não for admin)
        $query = DB::table('bets')
            ->where('bets.site_id', $siteId)
            ->whereBetween('bets.created_at', [$dateStart, $dateEnd])
            ->where('bets.status', '!=', 'cancelled');

        if (auth()->user()->role === 'manager') {
            $query->where('bets.manager_id', auth()->id());
        }

        $stats = $query->select(
            DB::raw('SUM(amount) as total_entradas'),
            DB::raw('SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as total_saidas'),
            DB::raw('SUM(amount) - SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as saldo_liquido')
        )->first();

        // Lista de movimentações por gerente
        $gerentesQuery = DB::table('master_users')
            ->where('master_users.site_id', $siteId)
            ->where('master_users.role', 'manager');

        if (auth()->user()->role === 'manager') {
            $gerentesQuery->where('master_users.id', auth()->id());
        }

        $movimentacoes = $gerentesQuery
            ->leftJoin('bets', function($join) use ($dateStart, $dateEnd) {
                $join->on('master_users.id', '=', 'bets.manager_id')
                    ->whereBetween('bets.created_at', [$dateStart, $dateEnd])
                    ->where('bets.status', '!=', 'cancelled');
            })
            ->select(
                'master_users.username',
                DB::raw('SUM(bets.amount) as entradas'),
                DB::raw('SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END) as saidas'),
                DB::raw('SUM(bets.commission_amount) as comissao_cambistas'),
                DB::raw('SUM(bets.amount) - (SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END) + SUM(bets.commission_amount)) as valor_liquido')
            )
            ->groupBy('master_users.id', 'master_users.username')
            ->get();

        return view('admin.caixa-adm-gerente', compact('stats', 'movimentacoes', 'dateStart', 'dateEnd'));
    }
}
