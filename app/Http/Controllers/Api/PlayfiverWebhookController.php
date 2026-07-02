<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ApostasCassino;
use App\Models\PlayfiverGame;
use Illuminate\Support\Facades\Log;

class PlayfiverWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $rawContent = $request->getContent();
        $data = json_decode($rawContent, true);

        if (!$data) {
            $data = $request->all();
        }

        Log::info('Playfiver Webhook: ' . $rawContent);

        $type = $data['type'] ?? $request->input('type');

        return match ($type) {
            'BALANCE' => $this->handleBalance($data),
            'WinBet'  => $this->handleWinBet($data),
            default   => response()->json(['status' => false, 'msg' => 'INVALID_TYPE'], 400),
        };
    }

    private function handleBalance(array $data)
    {
        $userCode = $data['user_code'] ?? '';
        $user = User::where('id', $userCode)->orWhere('email', $userCode)->first();

        if (!$user) {
            return response()->json(['balance' => 0, 'msg' => 'INVALID_USER'], 404);
        }

        return response()->json([
            'msg' => 'SUCCESS',
            'balance' => (float) ($user->credito ?? $user->balance ?? 0),
        ], 200);
    }

    private function handleWinBet(array $data)
    {
        $agentSecret = config('services.playfiver.secret', env('API_PLAYFIVER_SECRET'));
        $reqSecret = $data['agent_secret'] ?? '';

        if ($reqSecret !== $agentSecret) {
            Log::warning('Playfiver Webhook: INVALID_CREDENTIALS');
            return response()->json(['msg' => 'INVALID_CREDENTIALS', 'balance' => 0], 401);
        }

        $userCode = $data['user_code'] ?? '';
        $user = User::where('id', $userCode)->orWhere('email', $userCode)->first();

        if (!$user) {
            return response()->json(['balance' => 0, 'msg' => 'INVALID_USER'], 404);
        }

        $slotData = $data['slot'] ?? [];
        $betAmount = (float) ($slotData['bet'] ?? 0);
        $winAmount = (float) ($slotData['win'] ?? 0);
        $txnId = $slotData['txn_id'] ?? uniqid();
        $gameCode = $slotData['game_code'] ?? null;

        $totalBalance = ($user->credito ?? 0) + ($user->saldo_bonus ?? 0);
        if ($betAmount > $totalBalance) {
            return response()->json(['balance' => 0, 'msg' => 'INSUFFICIENT_USER_FUNDS'], 400);
        }

        // Deduz aposta do saldo real, adiciona ganho
        $realBalance = $user->credito ?? $user->balance ?? 0;
        $realBalance = $realBalance - $betAmount + $winAmount;

        if (property_exists($user, 'credito')) {
            $user->credito = max(0, $realBalance);
        } else {
            $user->balance = max(0, $realBalance);
        }
        $user->save();

        // Registra aposta cassino
        try {
            $gameId = null;
            if ($gameCode) {
                $game = PlayfiverGame::where('game_code', $gameCode)->first();
                $gameId = $game?->id;
            }

            ApostasCassino::create([
                'bet_id' => $txnId,
                'user_login' => $user->id,
                'game_id' => $gameId ?? 0,
                'bet' => $betAmount,
                'win' => $winAmount,
                'bet_info' => substr(json_encode($slotData), 0, 250),
                'site_id' => config('tenant.site_id', 1),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar ApostasCassino: ' . $e->getMessage());
        }

        return response()->json([
            'msg' => 'SUCCESS',
            'balance' => (float) ($user->credito ?? $user->balance ?? 0),
        ], 200);
    }
}
