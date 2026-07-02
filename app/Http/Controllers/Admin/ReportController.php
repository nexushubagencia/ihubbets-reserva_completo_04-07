<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bet;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Relatório de Gerentes (Modernizado para V2)
     */
    public function gerenteReport(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $dateStart = ($request->date1 ?? date('Y-m-d')) . ' 00:00:00';
        $dateEnd = ($request->date2 ?? date('Y-m-d')) . ' 23:59:59';

        $query = User::where('site_id', $siteId)->where('role', 'manager');
        
        if ($request->gerente && $request->gerente !== 'Todos') {
            $query->where('id', $request->gerente);
        }

        $managers = $query->get();
        $reportData = [];

        foreach ($managers as $manager) {
            $stats = DB::table('bets')
                ->select(
                    DB::raw('SUM(amount) as total_entregue'),
                    DB::raw('SUM(commission_amount) as total_comissoes_cambistas'),
                    DB::raw('COUNT(id) as total_bilhetes')
                )
                ->where('manager_id', $manager->id)
                ->where('site_id', $siteId)
                ->whereBetween('created_at', [$dateStart, $dateEnd])
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->first();

            $prizes = DB::table('bets')
                ->where('manager_id', $manager->id)
                ->where('site_id', $siteId)
                ->whereBetween('created_at', [$dateStart, $dateEnd])
                ->where('status', 'won')
                ->sum('potential_payout');

            $netProfit = ($stats->total_entregue ?? 0) - (($stats->total_comissoes_cambistas ?? 0) + $prizes);
            
            $managerCommission = 0;
            if ($netProfit > 0 && ($manager->manager_commission_rate ?? 0) > 0) {
                $managerCommission = ($netProfit * $manager->manager_commission_rate) / 100;
            }

            $reportData[] = [
                'name' => $manager->name,
                'tickets' => $stats->total_bilhetes ?? 0,
                'entradas' => (float)($stats->total_entregue ?? 0),
                'saidas' => (float)$prizes,
                'comissao_cambistas' => (float)($stats->total_comissoes_cambistas ?? 0),
                'comissao_gerente' => (float)$managerCommission,
                'saldo_banca' => (float)($netProfit - $managerCommission)
            ];
        }

        return response()->json($reportData);
    }

    /**
     * Relatório de Cambistas (Modernizado para V2)
     */
    public function cambistaReport(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $dateStart = ($request->date1 ?? date('Y-m-d')) . ' 00:00:00';
        $dateEnd = ($request->date2 ?? date('Y-m-d')) . ' 23:59:59';

        $query = User::where('site_id', $siteId)->where('role', 'seller');

        if ($request->cambista && $request->cambista !== 'Todos') {
            $query->where('id', $request->cambista);
        }

        if (auth()->user()->role === 'manager') {
            $query->where('manager_id', auth()->id());
        }

        $sellers = $query->get();
        $reportData = [];

        foreach ($sellers as $seller) {
            $stats = DB::table('bets')
                ->select(
                    DB::raw('SUM(amount) as total_apostado'),
                    DB::raw('SUM(commission_amount) as comissoes'),
                    DB::raw('COUNT(id) as quantidade')
                )
                ->where('user_id', $seller->id)
                ->where('site_id', $siteId)
                ->whereBetween('created_at', [$dateStart, $dateEnd])
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->first();

            $prizes = DB::table('bets')
                ->where('user_id', $seller->id)
                ->where('site_id', $siteId)
                ->whereBetween('created_at', [$dateStart, $dateEnd])
                ->where('status', 'won')
                ->sum('potential_payout');

            $reportData[] = [
                'name' => $seller->name,
                'entradas' => (float)($stats->total_apostado ?? 0),
                'comissao' => (float)($stats->comissoes ?? 0),
                'saidas' => (float)$prizes,
                'saldo' => (float)(($stats->total_apostado ?? 0) - (($stats->comissoes ?? 0) + $prizes)),
                'tickets' => $stats->quantidade ?? 0
            ];
        }

        return response()->json($reportData);
    }

    /**
     * Prepara os dados para o layout de impressão (PDF)
     */
    public function getPrintData(Request $request, $type, $id)
    {
        $siteId = config('tenant.site_id', 1);
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        if ($currentUser->role === 'manager') {
            if ($user->id !== $currentUser->id && $user->manager_id !== $currentUser->id) {
                abort(403);
            }
        } elseif ($currentUser->role === 'seller') {
            if ($user->id !== $currentUser->id) {
                abort(403);
            }
        }

        $dateStart = $request->date1 ?? date('Y-m-d');
        $dateEnd = $request->date2 ?? date('Y-m-d');

        if ($type === 'manager') {
            $stats = DB::table('bets')
                ->select(DB::raw('SUM(amount) as entradas'), DB::raw('SUM(commission_amount) as comissao_cambistas'))
                ->where('manager_id', $id)->whereBetween('created_at', [$dateStart.' 00:00:00', $dateEnd.' 23:59:59'])->first();
            
            $saidas = DB::table('bets')->where('manager_id', $id)->where('status', 'won')
                ->whereBetween('created_at', [$dateStart.' 00:00:00', $dateEnd.' 23:59:59'])->sum('potential_payout');

            $net = ($stats->entradas ?? 0) - (($stats->comissao_cambistas ?? 0) + $saidas);
            $commission = ($net > 0) ? ($net * ($user->manager_commission_rate ?? 0)) / 100 : 0;

            $data = [
                'name' => $user->name,
                'role' => 'manager',
                'period' => date('d/m/Y', strtotime($dateStart)) . ' até ' . date('d/m/Y', strtotime($dateEnd)),
                'entradas' => (float)($stats->entradas ?? 0),
                'saidas' => (float)$saidas,
                'comissao_cambistas_total' => (float)($stats->comissao_cambistas ?? 0),
                'comissao_gerente' => (float)$commission,
                'saldo' => (float)($net - $commission)
            ];
        } else {
            $stats = DB::table('bets')
                ->select(DB::raw('SUM(amount) as entradas'), DB::raw('SUM(commission_amount) as comissao'))
                ->where('user_id', $id)->whereBetween('created_at', [$dateStart.' 00:00:00', $dateEnd.' 23:59:59'])->first();
            
            $saidas = DB::table('bets')->where('user_id', $id)->where('status', 'won')
                ->whereBetween('created_at', [$dateStart.' 00:00:00', $dateEnd.' 23:59:59'])->sum('potential_payout');

            $data = [
                'name' => $user->name,
                'role' => 'seller',
                'period' => date('d/m/Y', strtotime($dateStart)) . ' até ' . date('d/m/Y', strtotime($dateEnd)),
                'entradas' => (float)($stats->entradas ?? 0),
                'saidas' => (float)$saidas,
                'comissao_cambista' => (float)($stats->comissao ?? 0),
                'saldo' => (float)(($stats->entradas ?? 0) - (($stats->comissao ?? 0) + $saidas))
            ];
        }

        return view('admin.reports.print_layout', compact('data'));
    }
}
