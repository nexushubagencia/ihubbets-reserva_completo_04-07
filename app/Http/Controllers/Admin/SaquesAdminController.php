<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SaquesAdminController extends Controller
{
    public function index()
    {
        return view('admin.saques');
    }

    public function list(Request $request)
    {
        try {
            $siteId = config('tenant.site_id', 1);
            $saques = Saque::where('site_id', $siteId)
                ->orderBy('id', 'desc')
                ->with('user')
                ->get();

            return response()->json(['result' => $saques]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['result' => []], 500);
        }
    }

    public function confirm(Request $request, Saque $saque)
    {
        try {
            if ($saque->status !== 'Em processamento') {
                return response()->json(['error' => 'Saque não pode mais ser alterado.'], 422);
            }

            $baseUrl = config('services.primepag.base_url', 'https://api.primepag.com.br');
            $clientId = config('services.primepag.client_id');
            $clientSecret = config('services.primepag.client_secret');

            // Gerar token PrimePag
            $credentials = base64_encode("$clientId:$clientSecret");
            $tokenResponse = Http::withHeaders([
                'Authorization' => "Basic {$credentials}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post("{$baseUrl}/auth/generate_token", [
                'grant_type' => 'client_credentials',
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json(['error' => 'Erro ao autenticar com gateway'], 500);
            }

            $token = $tokenResponse->json('access_token');

            // Enviar pagamento PIX
            $paymentResponse = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post("{$baseUrl}/v1/pix/payments", [
                'initiation_type' => 'dict',
                'idempotent_id' => (string) $saque->id,
                'pix_key_type' => $saque->tipo_pix,
                'pix_key' => $saque->pix,
                'value_cents' => (int) ($saque->valor * 100),
                'authorized' => true,
            ]);

            if (!$paymentResponse->successful()) {
                Log::error('PrimePag payment error', [
                    'saque_id' => $saque->id,
                    'status' => $paymentResponse->status(),
                    'body' => $paymentResponse->body(),
                ]);
                return response()->json([
                    'result' => false,
                    'error' => 'Gateway recusou o pagamento: ' . $paymentResponse->body()
                ], 422);
            }

            \DB::beginTransaction();

            $saque->status = 'Aprovado';
            $saque->paid_at = now();
            $saque->gateway_response = $paymentResponse->body();
            $saque->save();

            // Registra transação de saque
            \DB::table('transactions')->insert([
                'site_id'     => $saque->site_id,
                'user_id'     => $saque->user_id,
                'type'        => 'withdrawal',
                'amount'      => $saque->valor,
                'gateway_ref' => (string) $saque->id,
                'status'      => 'completed',
                'description' => "Saque PIX aprovado e enviado (Ref: {$saque->id})",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Saque aprovado e processado!']);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('SaquesAdminController confirm error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao processar saque'], 422);
        }
    }

    public function reject(Request $request, Saque $saque)
    {
        try {
            if ($saque->status !== 'Em processamento') {
                return response()->json(['error' => 'Saque não pode mais ser alterado.'], 422);
            }

            \DB::beginTransaction();

            $saque->status = 'Recusado';
            $saque->admin_note = $request->input('motivo', '');
            $saque->save();

            // Devolver saldo ao usuário
            $user = User::find($saque->user_id);
            if ($user) {
                $user->balance = ($user->balance ?? 0) + $saque->valor;
                $user->save();
            }

            // Registra transação de estorno
            \DB::table('transactions')->insert([
                'site_id'     => $saque->site_id,
                'user_id'     => $saque->user_id,
                'type'        => 'withdrawal_reversal',
                'amount'      => $saque->valor,
                'gateway_ref' => (string) $saque->id,
                'status'      => 'completed',
                'description' => "Saque recusado - saldo devolvido (Ref: {$saque->id})",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Saque recusado e saldo devolvido.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('SaquesAdminController reject error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao recusar saque'], 422);
        }
    }
}
