<?php

namespace App\Http\Controllers\Cambista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CambistaHomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siteId = app('tenant.site_id');
        $today = Carbon::today();

        $apostasHoje = DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->count();

        $entradasHoje = (float) DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->whereNotIn('status', ['cancelled'])
            ->sum('amount');

        $saidasHoje = (float) DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->where('status', 'won')
            ->sum('potential_payout');

        $comissoesHoje = (float) DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->whereNotIn('status', ['cancelled'])
            ->sum('commission_amount');

        $bilhetesAbertos = DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->count();

        $ultimasApostas = DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('cambista.home', compact(
            'user',
            'apostasHoje',
            'entradasHoje',
            'saidasHoje',
            'comissoesHoje',
            'bilhetesAbertos',
            'ultimasApostas'
        ));
    }
}
