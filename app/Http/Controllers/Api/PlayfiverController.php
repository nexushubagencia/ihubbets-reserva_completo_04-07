<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlayfiverGame;
use App\Models\PlayfiverProvider;
use Illuminate\Support\Facades\Log;

class PlayfiverController extends Controller
{
    public function getGames()
    {
        $siteId = config('tenant.site_id', 1);
        $allGames = PlayfiverGame::where('status', 1)
            ->where('site_id', $siteId)
            ->get();

        $groupedGames = $allGames->groupBy('provider');

        $activeProviders = PlayfiverProvider::where('status', 1)
            ->where('site_id', $siteId)
            ->get();

        return response()->json([
            'games' => $allGames,
            'games_grouped' => $groupedGames,
            'providers' => $activeProviders,
        ]);
    }

    public function launchGame(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $gameCode = $request->input('game_code');
        $game = PlayfiverGame::where('game_code', $gameCode)->first();

        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        $agentToken = config('services.playfiver.token', env('API_PLAYFIVER_TOKEN'));
        $secretKey = config('services.playfiver.secret', env('API_PLAYFIVER_SECRET'));

        try {
            $payload = [
                'agentToken' => $agentToken,
                'secretKey' => $secretKey,
                'user_code' => (string) $user->id,
                'game_code' => $game->game_code,
                'provider' => $game->provider,
                'game_original' => (bool) $game->original,
                'user_balance' => (float) ($user->credito ?? $user->balance ?? 0),
                'lang' => 'pt',
            ];

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.playfivers.com/api/v2/game_launch', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['status'])) {
                    return response()->json([
                        'url' => $data['launch_url'] ?? '',
                    ]);
                }
                return response()->json(['error' => 'Failed to launch game', 'details' => $data], 400);
            }

            return response()->json(['error' => 'Failed to connect to API'], 500);
        } catch (\Exception $e) {
            Log::error('Playfiver launch game error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
