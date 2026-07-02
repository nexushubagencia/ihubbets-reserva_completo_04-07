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

            $clientId = env('PRIMEPAG_CLIENT_ID');
            $clientSecret = env('PRIMEPAG_CLIENT_SECRET');

            // Gerar token PrimePag
            $credentials = base64_encode("$clientId:$clientSecret");
            $tokenResponse = Http::withHeaders([
                'Authorization' => "Basic {$credentials}",
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post('https://api.primepag.com.br/auth/generate_token', [
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
            ->post('https://api.primepag.com.br/v1/pix/payments', [
                'initiation_type' => 'dict',
                'idempotent_id' => (string) $saque->id,
                'pix_key_type' => $saque->tipo_pix,
                'pix_key' => $saque->pix,
                'value_cents' => (int) ($saque->valor * 100),
                'authorized' => true,
            ]);

            \DB::beginTransaction();
            $saque->status = 'Aprovado';
            $saque->save();
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
                if (property_exists($user, 'credito')) {
                    $user->credito = ($user->credito ?? 0) + $saque->valor;
                } else {
                    $user->balance = ($user->balance ?? 0) + $saque->valor;
                }
                $user->save();
            }

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Saque recusado e saldo devolvido.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('SaquesAdminController reject error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao recusar saque'], 422);
        }
    }
}
