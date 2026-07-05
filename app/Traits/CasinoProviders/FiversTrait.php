<?php

namespace App\Traits\CasinoProviders;

use App\Models\Casino\CasinoGame;
use App\Models\Casino\CasinoGamesKey;
use App\Models\Casino\CasinoOrder;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait FiversTrait
{
    protected static $agentCode;
    protected static $agentToken;
    protected static $agentSecretKey;
    protected static $apiEndpoint;

    public static function getCredentials(): bool
    {
        $setting = CasinoGamesKey::first();

        if (!$setting) {
            return false;
        }

        self::$agentCode      = $setting->getAttributes()['agent_code'] ?? null;
        self::$agentToken     = $setting->getAttributes()['agent_token'] ?? null;
        self::$agentSecretKey = $setting->getAttributes()['agent_secret_key'] ?? null;
        self::$apiEndpoint    = $setting->getAttributes()['api_endpoint'] ?? null;

        return !empty(self::$agentCode) && !empty(self::$agentToken) && !empty(self::$apiEndpoint);
    }

    public static function GameLaunchFivers($providerCode, $gameCode, $lang, $userId)
    {
        if (!self::getCredentials()) {
            return ['status' => false, 'msg' => 'CREDENTIALS_NOT_CONFIGURED'];
        }

        $postArray = [
            "method"        => "game_launch",
            "agent_code"    => self::$agentCode,
            "agent_token"   => self::$agentToken,
            "user_code"     => (string) $userId,
            "provider_code" => $providerCode,
            "game_code"     => $gameCode,
            "lang"          => $lang,
        ];

        try {
            $response = Http::timeout(30)->get(self::$apiEndpoint, $postArray);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] == 0 && ($data['msg'] ?? '') === 'Invalid User') {
                    if (self::createUser($userId)) {
                        return self::GameLaunchFivers($providerCode, $gameCode, $lang, $userId);
                    }
                }

                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Fivers launch error: ' . $e->getMessage());
        }

        return ['status' => false, 'msg' => 'LAUNCH_FAILED'];
    }

    public static function createUser($userId): bool
    {
        if (!self::getCredentials()) {
            return false;
        }

        $postArray = [
            "method"      => "user_create",
            "agent_code"  => self::$agentCode,
            "agent_token" => self::$agentToken,
            "user_code"   => (string) $userId,
        ];

        try {
            $response = Http::timeout(30)->get(self::$apiEndpoint, $postArray);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Fivers create user error: ' . $e->getMessage());
            return false;
        }
    }

    public static function WebhooksFivers($request)
    {
        if (!self::getCredentials()) {
            return response()->json(['status' => 0, 'msg' => 'CREDENTIALS_NOT_CONFIGURED'], 500);
        }

        if ($request->input('agent_code') !== self::$agentCode || $request->input('agent_secret') !== self::$agentToken) {
            return response()->json(['status' => 0, 'msg' => 'INVALID_CREDENTIALS'], 401);
        }

        switch ($request->input('method')) {
            case 'user_balance':
                return self::GetBalanceInfo($request);
            case 'transaction':
                return self::SetTransaction($request);
            default:
                return response()->json(['status' => 0, 'msg' => 'INVALID_METHOD'], 400);
        }
    }

    private static function GetBalanceInfo($request)
    {
        $user = User::find($request->input('user_code'));
        $balance = (float) ($user->credito ?? $user->balance ?? 0);

        return response()->json([
            'status' => 1,
            'user_balance' => $balance,
        ]);
    }

    private static function SetTransaction($request)
    {
        $data = $request->all();
        $user = User::find($request->input('user_code'));

        if (!$user) {
            return response()->json(['status' => 0, 'msg' => 'INVALID_USER'], 404);
        }

        $section = $data['game_type'] === 'live' ? ($data['live'] ?? null) : ($data['slot'] ?? null);

        if (!$section) {
            return response()->json(['status' => 0, 'msg' => 'INVALID_DATA'], 400);
        }

        $txnId = $section['txn_id'] ?? null;
        $bet = (float) ($section['bet_money'] ?? 0);
        $win = (float) ($section['win_money'] ?? 0);
        $gameCode = $section['game_code'] ?? null;
        $providerCode = $section['provider_code'] ?? null;

        if (!$txnId || $bet <= 0) {
            return response()->json(['status' => 0, 'msg' => 'INVALID_TRANSACTION'], 400);
        }

        // Idempotency
        if (CasinoOrder::where('transaction_id', $txnId)->exists()) {
            return response()->json([
                'status' => 1,
                'user_balance' => (float) ($user->credito ?? $user->balance ?? 0),
            ]);
        }

        $balance = (float) ($user->credito ?? $user->balance ?? 0);

        if ($bet > $balance) {
            return response()->json(['status' => 0, 'msg' => 'INSUFFICIENT_USER_FUNDS'], 400);
        }

        $newBalance = $balance - $bet + $win;

        if (property_exists($user, 'credito')) {
            $user->credito = max(0, $newBalance);
        } else {
            $user->balance = max(0, $newBalance);
        }
        $user->save();

        CasinoOrder::create([
            'user_id'        => $user->id,
            'session_id'     => time(),
            'transaction_id' => $txnId,
            'type'           => $win > 0 ? 'win' : 'bet',
            'type_money'     => 'balance',
            'amount'         => $win > 0 ? $win : $bet,
            'providers'      => 'fivers',
            'game'           => $gameCode,
            'game_uuid'      => $gameCode,
            'round_id'       => 1,
            'status'         => 1,
            'payload'        => $data,
        ]);

        return response()->json([
            'status' => 1,
            'user_balance' => (float) ($user->credito ?? $user->balance ?? 0),
        ]);
    }
}
