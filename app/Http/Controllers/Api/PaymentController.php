<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Site;
use App\Models\PixDeposit;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function createPix(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Usuário não autenticado.'], 401);
        }

        $siteId = $user->site_id;
        $config = \Illuminate\Support\Facades\DB::table('configuracaos')->where('site_id', $siteId)->first();
        
        $minDep = $config->min_deposit ?? 20.00;
        $maxDep = $config->max_deposit ?? 10000.00;

        $request->validate([
            'amount' => "required|numeric|min:{$minDep}|max:{$maxDep}",
        ], [
            'amount.min' => "O valor mínimo para depósito é R$ " . number_format($minDep, 2, ',', '.'),
            'amount.max' => "O valor máximo para depósito é R$ " . number_format($maxDep, 2, ',', '.'),
        ]);

        $site = Site::find($siteId);
        if (!$site || !$site->pix_client_secret) {
            return response()->json(['status' => 'error', 'message' => 'Método de pagamento não configurado para esta banca.'], 400);
        }

        $amount = (float) $request->amount;
        $externalReference = Str::uuid()->toString();

        $payload = [
            "transaction_amount" => $amount,
            "description" => "Depósito na Plataforma " . ($site->name ?? ''),
            "payment_method_id" => "pix",
            "external_reference" => $externalReference,
            "payer" => [
                "email" => $user->email,
                "first_name" => $user->name,
                "identification" => [
                    "type" => "CPF",
                    "number" => preg_replace('/[^0-9]/', '', $user->cpf ?? '')
                ]
            ],
            "notification_url" => url('/api/webhook/mercadopago/' . $siteId)
        ];

        try {
            // --- MERCADO PAGO (Exclusive) ---
            $response = Http::withToken($site->pix_client_secret)
                ->post('https://api.mercadopago.com/v1/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();

                $deposit = PixDeposit::create([
                    'site_id' => $siteId,
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'external_reference' => $externalReference,
                    'mp_payment_id' => $data['id'],
                    'qr_code' => $data['point_of_interaction']['transaction_data']['qr_code'],
                    'qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'],
                    'status' => 'pending'
                ]);

                return response()->json([
                    'status' => 'success',
                    'qr_code' => $deposit->qr_code,
                    'qr_code_base64' => $deposit->qr_code_base64,
                    'payment_id' => $deposit->mp_payment_id
                ]);
            }

            Log::error('Erro ao gerar PIX MP: ' . $response->body());
            return response()->json(['status' => 'error', 'message' => 'Falha na comunicação com o Mercado Pago.'], 500);

        } catch (\Exception $e) {
            Log::error('Exception PIX: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Erro interno ao processar pagamento.'], 500);
        }
    }

    public function webhookMercadoPago(Request $request, $siteId)
    {
        $action = $request->input('action');
        $type = $request->input('type');
        
        if ($action == 'payment.updated' || $type == 'payment') {
            $paymentId = $request->input('data.id') ?? $request->input('id');

            // 🛡️ Webhook HMAC Validation (ESTRITO quando MP_WEBHOOK_SECRET configurado)
            $secret = env('MP_WEBHOOK_SECRET');
            $signatureHeader = $request->header('x-signature');
            
            if ($secret) {
                // Se a secret está configurada, a assinatura é OBRIGATÓRIA
                if (!$signatureHeader) {
                    Log::warning("Webhook MP rejeitado: x-signature ausente para site_id: {$siteId}", [
                        'ip' => $request->ip(),
                        'payload' => $request->all(),
                    ]);
                    return response()->json(['status' => 'error', 'message' => 'Signature required'], 403);
                }

                // Parse do header: ts=...,v1=...
                $parts = explode(',', $signatureHeader);
                $ts = null;
                $v1 = null;
                
                foreach ($parts as $part) {
                    if (str_starts_with(trim($part), 'ts=')) $ts = substr(trim($part), 3);
                    if (str_starts_with(trim($part), 'v1=')) $v1 = substr(trim($part), 3);
                }

                if (!$ts || !$v1) {
                    Log::warning("Webhook MP rejeitado: assinatura malformada para site_id: {$siteId}");
                    return response()->json(['status' => 'error', 'message' => 'Malformed signature'], 403);
                }

                // Proteção contra replay attack (tolerância de 5 minutos)
                if (abs(time() - (int)$ts) > 300) {
                    Log::warning("Webhook MP rejeitado: timestamp expirado para site_id: {$siteId}");
                    return response()->json(['status' => 'error', 'message' => 'Expired timestamp'], 403);
                }

                $dataId = $request->input('data.id', '');
                $manifestData = "id:{$dataId};request-id:{$request->header('x-request-id')};ts:{$ts};";
                $expectedSignature = hash_hmac('sha256', $manifestData, $secret);
                
                if (!hash_equals($expectedSignature, $v1)) {
                    Log::warning("Webhook MP rejeitado: assinatura inválida para site_id: {$siteId}", [
                        'ip' => $request->ip(),
                    ]);
                    return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
                }

                Log::info("Webhook MP: assinatura validada com sucesso para site_id: {$siteId}");
            }

            $site = Site::find($siteId);
            if (!$site) return response()->json(['status' => 'error'], 404);

            $response = Http::withToken($site->pix_client_secret)
                ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            if ($response->successful()) {
                $payment = $response->json();

                // 🛡️ BLINDAGEM ATÔMICA: Só atualiza se ainda estiver pendente
                $affected = PixDeposit::where('mp_payment_id', $paymentId)
                                     ->where('site_id', $siteId)
                                     ->where('status', 'pending')
                                     ->update(['status' => 'approved']);

                if ($affected > 0 && $payment['status'] == 'approved') {
                    $deposit = PixDeposit::where('mp_payment_id', $paymentId)->first();
                    
                    // Adiciona saldo ao usuário
                    \Illuminate\Support\Facades\DB::table('master_users')
                        ->where('id', $deposit->user_id)
                        ->increment('balance', $deposit->amount);

                    // Log de Transação
                    \Illuminate\Support\Facades\DB::table('transactions')->insert([
                        'site_id'    => $siteId,
                        'user_id'    => $deposit->user_id,
                        'type'       => 'deposit',
                        'amount'     => $deposit->amount,
                        'gateway_ref'=> "mp_{$paymentId}",
                        'status'     => 'completed',
                        'description'=> "Depósito via PIX aprovado (Ref: {$deposit->external_reference})",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Consulta o status atual de um PIX.
     * Pode receber o UUID (external_reference) ou o ID do banco.
     */
    public function checkPixStatus($id)
    {
        $deposit = PixDeposit::where('id', $id)
            ->orWhere('external_reference', $id)
            ->first();

        if (!$deposit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Depósito não encontrado'
            ], 404);
        }

        return response()->json([
            'status' => $deposit->status, // pending, approved, rejected, cancelled
            'amount' => $deposit->amount,
            'mp_payment_id' => $deposit->mp_payment_id
        ]);
    }
}
