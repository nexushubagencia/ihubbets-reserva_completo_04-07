<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Gera uma solicitação de Depósito via PIX
     */
    public function deposit(Request $request)
    {
        $user = auth()->user();
        $amount = $request->amount;

        if ($amount < 1.00) {
            return response()->json(['message' => 'Valor mínimo de depósito é R$ 1,00'], 422);
        }

        // Simulação de Integração com Gateway (SuitPay/GamesPay)
        // Aqui chamamos a API do gateway para gerar o QR Code
        $txid = Str::uuid();
        $qrCode = "00020126580014BR.GOV.BCB.PIX0114+55119999999995204000053039865405" . $amount . "5802BR5913IHUB_BETS6009SAO_PAULO62070503***6304ABCD";

        // Registra a transação pendente
        DB::table('payments')->insert([
            'user_id' => $user->id,
            'site_id' => config('tenant.site_id', 1),
            'amount' => $amount,
            'type' => 'deposit',
            'gateway_reference' => $txid,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'qrCode' => $qrCode,
            'qrcode_base64' => 'base64_string_here', // Imagem do QR Code
            'txid' => $txid
        ]);
    }

    /**
     * Webhook de Confirmação (Chamado pelo Banco/Gateway)
     */
    public function webhook(Request $request)
    {
        // 1. Validar Token de Segurança do Gateway (Simulado/Configurável)
        $token = $request->header('X-Gateway-Token');
        $secret = config('services.gateway.webhook_secret', 'ihub_secret_key');

        if ($token !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // 2. Localizar transação e usuário
        $txid = $request->txid;
        $payment = DB::table('payments')->where('gateway_reference', $txid)->first();

        if ($payment && $payment->status === 'pending') {
            DB::beginTransaction();
            try {
                // Atualiza Status do Pagamento
                DB::table('payments')->where('id', $payment->id)->update(['status' => 'paid', 'updated_at' => now()]);

                // Adiciona Saldo ao Usuário
                $user = User::find($payment->user_id);
                $user->balance += $payment->amount;

                // Lógica de Bônus de Boas-Vindas (Se for o 1º depósito)
                $isFirstDeposit = DB::table('payments')->where('user_id', $user->id)->where('status', 'paid')->count() == 1;
                if ($isFirstDeposit) {
                    $bonus = $payment->amount; // 100% de bônus
                    $user->bonus_balance += $bonus;
                }

                $user->save();
                DB::commit();
                return response()->json(['status' => 'success']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error'], 500);
            }
        }

        return response()->json(['status' => 'not_found'], 404);
    }
}
