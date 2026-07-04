<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayfiverGame;
use App\Models\PlayfiverProvider;
use App\Models\Site;

class CassinoController extends Controller
{
    public function index(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $site = Site::find($siteId) ?? Site::first();

        $providers = PlayfiverProvider::where('site_id', $siteId)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $gamesQuery = PlayfiverGame::where('site_id', $siteId)
            ->where('status', 1);

        if ($request->filled('provider')) {
            $gamesQuery->where('provider', $request->provider);
        }

        if ($request->filled('search')) {
            $gamesQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('provider', 'like', "%{$request->search}%");
            });
        }

        $games = $gamesQuery->orderBy('is_popular', 'desc')
            ->orderBy('name')
            ->paginate(48)
            ->withQueryString();

        $popularGames = PlayfiverGame::where('site_id', $siteId)
            ->where('status', 1)
            ->where('is_popular', 1)
            ->orderBy('name')
            ->limit(12)
            ->get();

        return view('cassino.index', compact('site', 'providers', 'games', 'popularGames'));
    }
}
