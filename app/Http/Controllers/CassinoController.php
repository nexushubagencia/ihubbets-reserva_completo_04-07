<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Casino\CasinoGame;
use App\Models\Casino\CasinoProvider;

class CassinoController extends Controller
{
    public function index(Request $request)
    {
        $providers = CasinoProvider::withCount(['games' => fn ($q) => $q->where('status', 1)])
            ->having('games_count', '>', 0)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        if ($request->filled('provider')) {
            return $this->showProvider($request, $providers);
        }

        $search = $request->input('search');

        $topGamesQuery = CasinoGame::with('provider')->where('status', 1);
        if ($search) {
            $topGamesQuery->where(function ($q) use ($search) {
                $q->where('game_name', 'like', "%{$search}%")
                  ->orWhere('game_code', 'like', "%{$search}%")
                  ->orWhereHas('provider', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }
        $topGames = $topGamesQuery->orderByDesc('views')
            ->orderBy('game_name')
            ->limit(18)
            ->get();

        $gamesByProvider = [];
        foreach ($providers as $provider) {
            $query = CasinoGame::where('provider_id', $provider->id)
                ->where('status', 1);
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('game_name', 'like', "%{$search}%")
                      ->orWhere('game_code', 'like', "%{$search}%");
                });
            }
            $games = $query->orderByDesc('is_featured')
                ->orderByDesc('views')
                ->orderBy('game_name')
                ->limit(18)
                ->get();
            if ($games->isNotEmpty()) {
                $gamesByProvider[$provider->name] = $games;
            }
        }

        return view('cassino.index', compact('providers', 'topGames', 'gamesByProvider'));
    }

    protected function showProvider(Request $request, $providers)
    {
        $provider = CasinoProvider::where('name', $request->provider)
            ->orWhere('code', $request->provider)
            ->firstOrFail();

        $games = CasinoGame::with('provider')
            ->where('provider_id', $provider->id)
            ->where('status', 1)
            ->orderByDesc('is_featured')
            ->orderByDesc('views')
            ->orderBy('game_name')
            ->paginate(48)
            ->withQueryString();

        return view('cassino.provider', compact('providers', 'provider', 'games'));
    }
}
