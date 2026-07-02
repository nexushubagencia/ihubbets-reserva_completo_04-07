<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function daily()
    {
        $siteId = config('tenant.site_id', 1);
        $data = DB::table('bets')
            ->where('site_id', $siteId)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_bets'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as total_paid'),
                DB::raw('SUM(amount) - SUM(CASE WHEN status = "won" THEN potential_payout ELSE 0 END) as lucro_dia')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return view('admin.statistics.daily', compact('data'));
    }

    public function bySeller()
    {
        $siteId = config('tenant.site_id', 1);
        $user   = auth()->user();

        $query = DB::table('master_users')
            ->where('master_users.site_id', $siteId)
            ->where('master_users.role', 'seller');

        // Gerente vê apenas os cambistas dele
        if ($user->role === 'manager') {
            $query->where('master_users.manager_id', $user->id);
        }

        $sellers = $query
            ->leftJoin('bets', function ($join) {
                $join->on('master_users.id', '=', 'bets.user_id')
                     ->where('bets.status', '!=', 'cancelled');
            })
            ->select(
                'master_users.id',
                'master_users.name',
                'master_users.username',
                DB::raw('COUNT(bets.id) as total_apostas'),
                DB::raw('COALESCE(SUM(bets.amount), 0) as entradas'),
                DB::raw('COALESCE(SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END), 0) as saidas'),
                DB::raw('COALESCE(SUM(bets.commission_amount), 0) as comissoes'),
                DB::raw('COALESCE(SUM(bets.amount), 0) - COALESCE(SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END), 0) - COALESCE(SUM(bets.commission_amount), 0) as lucro')
            )
            ->groupBy('master_users.id', 'master_users.name', 'master_users.username')
            ->get();

        return view('admin.statistics.by_seller', compact('sellers'));
    }

    public function byManager()
    {
        $siteId = config('tenant.site_id', 1);

        $managers = DB::table('master_users')
            ->where('master_users.site_id', $siteId)
            ->where('master_users.role', 'manager')
            ->leftJoin('bets', function ($join) {
                $join->on('master_users.id', '=', 'bets.manager_id')
                     ->where('bets.status', '!=', 'cancelled');
            })
            ->select(
                'master_users.id',
                'master_users.name',
                'master_users.username',
                DB::raw('COUNT(bets.id) as total_apostas'),
                DB::raw('COALESCE(SUM(bets.amount), 0) as entradas'),
                DB::raw('COALESCE(SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END), 0) as saidas'),
                DB::raw('COALESCE(SUM(bets.commission_amount), 0) as comissoes_cambistas'),
                DB::raw('COALESCE(SUM(bets.amount), 0) - COALESCE(SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END), 0) - COALESCE(SUM(bets.commission_amount), 0) as lucro_banca')
            )
            ->groupBy('master_users.id', 'master_users.name', 'master_users.username')
            ->get();

        return view('admin.statistics.by_manager', compact('managers'));
    }
}
