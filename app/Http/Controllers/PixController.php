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

            $message = $request->input('message', []);
            $referenceCode = $message['reference_code'] ?? null;
            $statusPagamento = $message['status'] ?? 'paid';

            if (!$referenceCode) {
                \DB::rollBack();
                return response()->json(['result' => false, 'error' => 'Reference code missing'], 400);
            }

            // Só processa status de pagamento confirmado
            if (!in_array(strtolower($statusPagamento), ['paid', 'pago', 'approved', 'completed'])) {
                \DB::rollBack();
                return response()->json(['result' => true, 'message' => 'Status ignorado: ' . $statusPagamento]);
            }

            $deposit = PixDeposit::where('gateway_ref', $referenceCode)
                ->orWhere('reference_id', $referenceCode)
                ->first();

            if (!$deposit) {
                \DB::rollBack();
                return response()->json(['result' => false, 'error' => 'Deposit not found'], 404);
            }

            // 🛡️ Proteção contra duplo crédito
            if ($deposit->status === 'Pago' || $deposit->paid_at) {
                \DB::rollBack();
                return response()->json(['result' => true, 'message' => 'Deposit already processed']);
            }

            $user = User::find($deposit->user_id);
            if (!$user) {
                \DB::rollBack();
                return response()->json(['result' => false, 'error' => 'User not found'], 404);
            }

            $valorDeposito = ($message['value_cents'] ?? ($deposit->amount * 100)) / 100;
            $valorDeposito = max(0, $valorDeposito);

            // Atualiza depósito
            $deposit->status = 'Pago';
            $deposit->paid_amount = $valorDeposito;
            $deposit->paid_at = now();
            $deposit->save();

            // Creditar saldo (clientes online usam balance)
            $user->balance = ($user->balance ?? 0) + $valorDeposito;

            // Aplicar bônus se promoção ativa
            if (method_exists($user, 'giveBonusOnDeposit')) {
                $user->giveBonusOnDeposit($valorDeposito);
            } elseif ($user->promocao_ativa_id) {
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

            // Registra transação financeira
            \DB::table('transactions')->insert([
                'site_id'     => $deposit->site_id,
                'user_id'     => $user->id,
                'type'        => 'deposit',
                'amount'      => $valorDeposito,
                'gateway_ref' => $deposit->reference_id,
                'status'      => 'completed',
                'description' => "Depósito PIX confirmado (Ref: {$referenceCode})",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Deposit confirmed']);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('PixController confirmarDeposito error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao processar'], 422);
        }
    }
}
