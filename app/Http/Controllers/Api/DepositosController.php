<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PixDeposit;
use App\Models\Configuracao;
use App\Models\User;
use App\Models\Promocao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DepositosController extends Controller
{
    private function generateToken(): ?string
    {
        $clientId = config('services.primepag.client_id');
        $clientSecret = config('services.primepag.client_secret');
        $baseUrl = config('services.primepag.base_url', 'https://api.primepag.com.br');

        if (empty($clientId) || empty($clientSecret)) {
            Log::error('DepositosController: PrimePag credentials not configured');
            return null;
        }

        $credentials = base64_encode("$clientId:$clientSecret");

        try {
            $response = Http::withHeaders([
                'Authorization' => "Basic {$credentials}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post("{$baseUrl}/auth/generate_token", [
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            Log::error('PrimePag token error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('DepositosController token error: ' . $e->getMessage());
        }

        return null;
    }

    public function novoDeposito(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric|min:1',
            'nome' => 'required|string',
            'cpf' => 'required|string',
        ]);

        try {
            \DB::beginTransaction();

            $token = $this->generateToken();
            if (!$token) {
                \DB::rollBack();
                return response()->json(['error' => 'Erro ao autenticar com gateway de pagamento'], 500);
            }

            $user = auth()->user();
            $depositoId = uniqid('dep_');
            $baseUrl = config('services.primepag.base_url', 'https://api.primepag.com.br');

            // Aplica promoção se selecionada
            $promocaoId = $request->input('promocao_id');
            if ($promocaoId) {
                $user->promocao_ativa_id = $promocaoId;
                $user->saldo_bonus = 0;
                $user->rollover_atual = 0;
                $user->rollover_meta = 0;
                $user->save();
            }

            // Gera QR Code PIX
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post("{$baseUrl}/v1/pix/qrcodes", [
                'value_cents' => (int) ($request->valor * 100),
                'generator_name' => $request->nome,
                'generator_document' => preg_replace('/\D/', '', $request->cpf),
                'deposit_id' => $depositoId,
            ]);

            if (!$response->successful()) {
                \DB::rollBack();
                Log::error('PrimePag QR error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Erro ao gerar QR Code'], 500);
            }

            $qrData = $response->json();
            $qrCode = $qrData['qrcode'] ?? $qrData;
            $referenceCode = $qrCode['reference_code'] ?? ($qrData['reference_code'] ?? null);

            // Registra depósito
            $deposito = PixDeposit::create([
                'user_id' => $user->id,
                'site_id' => config('tenant.site_id', 1),
                'status' => 'Aguardando pagamento',
                'amount' => $request->valor,
                'reference_id' => $depositoId,
                'gateway_ref' => $referenceCode,
            ]);

            \DB::commit();

            return response()->json([
                'qrcode' => $qrCode,
                'deposito_id' => $deposito->id,
                'reference_id' => $depositoId,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('DepositosController error: ' . $e->getMessage());
            return response()->json(['error' => 'Houve um problema ao processar seu pedido.'], 422);
        }
    }

    public function listDepositos()
    {
        try {
            $userId = auth()->user()->id;
            $depositos = PixDeposit::where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->with('user')
                ->get();

            return response()->json(['result' => $depositos]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['result' => []], 500);
        }
    }
}
