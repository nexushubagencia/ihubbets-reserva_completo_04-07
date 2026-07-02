<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalMarketAdjustment;
use App\Models\GlobalOddAdjustment;
use App\Models\UserMarketAdjustment;
use App\Models\UserOddAdjustment;
use App\Models\User;

class OddsMarketsController extends Controller
{
    // --- GERAL / GLOBAL ---

    public function getGlobalMarkets(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $markets = GlobalMarketAdjustment::where('site_id', $siteId)->get();
        return response()->json($markets);
    }

    public function updateGlobalMarket(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'market_name' => 'required|string',
            'sport' => 'required|string',
            'adjustment_percent' => 'required|numeric',
            'status' => 'required|integer',
        ]);

        $adjustment = GlobalMarketAdjustment::updateOrCreate(
            ['site_id' => $siteId, 'market_name' => $data['market_name'], 'sport' => $data['sport']],
            ['adjustment_percent' => $data['adjustment_percent'], 'status' => $data['status']]
        );

        return response()->json($adjustment);
    }

    public function getGlobalOdds(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $odds = GlobalOddAdjustment::where('site_id', $siteId)->get();
        return response()->json($odds);
    }

    // --- POR USUÁRIO ---

    public function getUserMarkets($userId)
    {
        $siteId = config('tenant.site_id', 1);
        $markets = UserMarketAdjustment::where('site_id', $siteId)
            ->where('user_id', $userId)
            ->get();
        return response()->json($markets);
    }

    public function updateUserMarket(Request $request, $userId)
    {
        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'market_name' => 'required|string',
            'adjustment_percent' => 'required|numeric',
            'status' => 'required|integer',
        ]);

        $adjustment = UserMarketAdjustment::updateOrCreate(
            ['site_id' => $siteId, 'user_id' => $userId, 'market_name' => $data['market_name']],
            ['adjustment_percent' => $data['adjustment_percent'], 'status' => $data['status']]
        );

        return response()->json($adjustment);
    }
}
