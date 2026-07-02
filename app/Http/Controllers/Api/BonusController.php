<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Promocao;
use Illuminate\Support\Facades\Auth;

class BonusController extends Controller
{
    public function getActivePromotions()
    {
        try {
            $user = auth('api')->user();
            $promocoes = Promocao::where('status', true)->get();

            return response()->json([
                'promocoes' => $promocoes,
                'saldo_bonus' => $user->saldo_bonus ?? 0,
                'rollover_meta' => $user->rollover_meta ?? 0,
                'rollover_atual' => $user->rollover_atual ?? 0,
                'promocao_ativa_id' => $user->promocao_ativa_id ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([], 500);
        }
    }

    public function claimBonus(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!empty($user->promocao_ativa_id)) {
                return response()->json(['message' => 'Você já possui um bônus ativo!'], 400);
            }

            $promocao = Promocao::find($request->promocao_id);
            if (!$promocao || !$promocao->status) {
                return response()->json(['message' => 'Promoção indisponível.'], 404);
            }

            $user->promocao_ativa_id = $promocao->id;
            $user->save();

            return response()->json(['message' => 'Bônus reivindicado com sucesso! Ele será ativado em seu próximo depósito.']);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['message' => 'Erro interno.'], 500);
        }
    }

    public function cancelBonus()
    {
        try {
            $user = auth('api')->user();

            $user->saldo_bonus = 0;
            $user->rollover_meta = 0;
            $user->rollover_atual = 0;
            $user->promocao_ativa_id = null;
            $user->save();

            return response()->json(['message' => 'Bônus cancelado.']);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['message' => 'Erro interno.'], 500);
        }
    }

    /**
     * Aplica código de bônus/promoção
     */
    public function applyCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        try {
            $user = auth('api')->user();

            if (!empty($user->promocao_ativa_id)) {
                return response()->json(['success' => false, 'message' => 'Você já possui um bônus ativo.'], 400);
            }

            $promocao = Promocao::where('codigo', $request->code)->where('status', true)->first();
            if (!$promocao) {
                return response()->json(['success' => false, 'message' => 'Código de bônus inválido ou expirado.'], 404);
            }

            $user->promocao_ativa_id = $promocao->id;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Bônus ativado com sucesso!',
                'promocao' => $promocao,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['success' => false, 'message' => 'Erro ao aplicar bônus.'], 500);
        }
    }

    /**
     * Retorna status do bônus do usuário
     */
    public function myBonus()
    {
        try {
            $user = auth('api')->user();

            $promocao = null;
            if (!empty($user->promocao_ativa_id)) {
                $promocao = Promocao::find($user->promocao_ativa_id);
            }

            return response()->json([
                'success' => true,
                'saldo_bonus' => $user->saldo_bonus ?? 0,
                'rollover_meta' => $user->rollover_meta ?? 0,
                'rollover_atual' => $user->rollover_atual ?? 0,
                'promocao_ativa' => $promocao,
                'promocao_ativa_id' => $user->promocao_ativa_id ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['success' => false, 'message' => 'Erro ao consultar bônus.'], 500);
        }
    }
}
