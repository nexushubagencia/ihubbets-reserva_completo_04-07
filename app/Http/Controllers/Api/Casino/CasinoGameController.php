<?php

namespace App\Http\Controllers\Api\Casino;

use App\Http\Controllers\Controller;
use App\Models\Casino\CasinoCategory;
use App\Models\Casino\CasinoGame;
use App\Models\Casino\CasinoGameFavorite;
use App\Models\Casino\CasinoGameLike;
use App\Models\Casino\CasinoProvider;
use App\Traits\CasinoProviders\FiversTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CasinoGameController extends Controller
{
    use FiversTrait;

    public function index(Request $request)
    {
        $providers = CasinoProvider::withCount(['games' => fn ($q) => $q->where('status', 1)])
            ->having('games_count', '>', 0)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $categories = CasinoCategory::orderBy('name')->get();

        return response()->json([
            'providers' => $providers,
            'categories' => $categories,
        ]);
    }

    public function featured()
    {
        $games = CasinoGame::with('provider')
            ->where('is_featured', 1)
            ->where('status', 1)
            ->orderBy('views', 'desc')
            ->limit(12)
            ->get();

        return response()->json(['featured_games' => $games]);
    }

    public function allGames(Request $request)
    {
        $query = CasinoGame::query()
            ->with(['provider', 'categories'])
            ->where('status', 1);

        if ($request->filled('provider') && $request->provider !== 'all') {
            $query->whereHas('provider', fn ($q) => $q->where('code', $request->provider));
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search') && strlen($request->search) > 2) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('game_name', 'like', "%{$term}%")
                  ->orWhere('game_code', 'like', "%{$term}%")
                  ->orWhereHas('provider', fn ($pq) => $pq->where('name', 'like', "%{$term}%"));
            });
        }

        $games = $query->orderBy('views', 'desc')
            ->paginate(48)
            ->appends($request->query());

        return response()->json(['games' => $games]);
    }

    public function show(string $id)
    {
        $game = CasinoGame::with(['categories', 'provider'])
            ->where('status', 1)
            ->find($id);

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        return response()->json(['game' => $game]);
    }

    public function launch(Request $request, string $gameId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $game = CasinoGame::with('provider')->find($gameId);

        if (!$game || !$game->provider) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        $distribution = $game->distribution ?? 'fivers';
        $lang = app()->getLocale();

        try {
            switch ($distribution) {
                case 'fivers':
                case 'play_fiver':
                    $result = self::GameLaunchFivers($game->provider->code, $game->game_id, $lang, $user->id);
                    if (!empty($result['launch_url'])) {
                        $game->increment('views');
                        return response()->json(['gameUrl' => $result['launch_url'], 'game' => $game]);
                    }
                    Log::error('Fivers launch failed', $result ?? []);
                    return response()->json(['error' => $result['msg'] ?? 'Launch failed'], 400);

                case 'maxapigames':
                    $maxApiGameCode = $game->game_id_maxapi ?: $game->game_id;
                    $result = self::GameLaunchMaxApi($game->provider->code, $maxApiGameCode, $lang, $user->id);
                    if (!empty($result['launch_url'])) {
                        $game->increment('views');
                        return response()->json(['gameUrl' => $result['launch_url'], 'game' => $game]);
                    }
                    Log::error('MaxApi launch failed', $result ?? []);
                    return response()->json(['error' => $result['error'] ?? 'Launch failed'], 400);

                default:
                    Log::warning('Unsupported distribution: ' . $distribution . ' for game ' . $game->id);
                    return response()->json(['error' => 'Distribution not supported: ' . $distribution], 400);
            }
        } catch (\Exception $e) {
            Log::error('Casino launch error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    public function toggleFavorite($id)
    {
        if (!auth()->check()) {
            return response()->json(['status' => false], 401);
        }

        $exists = CasinoGameFavorite::where('user_id', auth()->id())->where('game_id', $id)->first();

        if ($exists) {
            $exists->delete();
            return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
        }

        CasinoGameFavorite::create(['user_id' => auth()->id(), 'game_id' => $id]);
        return response()->json(['status' => true, 'message' => 'Adicionado com sucesso']);
    }

    public function toggleLike($id)
    {
        if (!auth()->check()) {
            return response()->json(['status' => false], 401);
        }

        $exists = CasinoGameLike::where('user_id', auth()->id())->where('game_id', $id)->first();

        if ($exists) {
            $exists->delete();
            return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
        }

        CasinoGameLike::create(['user_id' => auth()->id(), 'game_id' => $id]);
        return response()->json(['status' => true, 'message' => 'Adicionado com sucesso']);
    }
}
