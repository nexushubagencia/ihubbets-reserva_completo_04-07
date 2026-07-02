<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PixDeposit;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PixController extends Controller
{
    public function confirmarDeposito(Request $request)
    {
        try {
            \DB::beginTransaction();

            $referenceCode = $request->message['reference_code'] ?? null;
            if (!$referenceCode) {
                return response()->json(['result' => false, 'error' => 'Reference code missing'], 400);
            }

            $deposit = PixDeposit::where('gateway_ref', $referenceCode)->first();
            if (!$deposit) {
                // Tenta buscar por reference_id
                $deposit = PixDeposit::where('reference_id', $referenceCode)->first();
            }

            if (!$deposit) {
                \DB::rollBack();
                return response()->json(['result' => false, 'error' => 'Deposit not found'], 404);
            }

            $deposit->status = 'Pago';
            $deposit->save();

            $user = User::find($deposit->user_id);
            if (!$user) {
                \DB::rollBack();
                return response()->json(['result' => false, 'error' => 'User not found'], 404);
            }

            $valorDeposito = ($request->message['value_cents'] ?? 0) / 100;

            // Creditar saldo
            if (property_exists($user, 'credito')) {
                $user->credito = ($user->credito ?? 0) + $valorDeposito;
            } else {
                $user->balance = ($user->balance ?? 0) + $valorDeposito;
            }

            // Aplicar bônus se promoção ativa
            if (method_exists($user, 'giveBonusOnDeposit')) {
                $user->giveBonusOnDeposit($valorDeposito);
            } elseif (method_exists($user, 'promocao_ativa_id') && $user->promocao_ativa_id) {
                $promocao = \App\Models\Promocao::find($user->promocao_ativa_id);
                if ($promocao && $promocao->status) {
                    $bonusAmount = ($valorDeposito * $promocao->porcentagem) / 100;
                    $bonusAmount = min($bonusAmount, $promocao->valor_maximo ?? PHP_FLOAT_MAX);
                    $user->saldo_bonus = ($user->saldo_bonus ?? 0) + $bonusAmount;
                    $user->rollover_meta = $bonusAmount * ($promocao->rollover_multiplicador ?? 1);
                    $user->rollover_atual = 0;
                }
            }

            $user->save();

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Ok']);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('PixController confirmarDeposito error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao processar'], 422);
        }
    }
}
