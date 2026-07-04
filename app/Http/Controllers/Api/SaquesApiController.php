<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SaquesApiController extends Controller
{
    public function listSaques()
    {
        try {
            $userId = auth()->user()->id;
            $saques = Saque::where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->with('user')
                ->get();

            return response()->json(['result' => $saques]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['result' => []], 500);
        }
    }

    public function novoSaque(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric|min:1',
            'pix' => 'required|string',
            'tipo_pix' => 'required|string|in:cpf,cnpj,email,phone,random',
        ]);

        try {
            $user = auth()->user();
            $saldo = ($user->balance ?? 0) + ($user->credito ?? 0);

            if ($request->valor > $saldo) {
                return response()->json(['error' => 'Valor superior ao saldo disponível.'], 422);
            }

            // Verificar limite mínimo de saque
            $config = \App\Models\Configuracao::where('site_id', config('tenant.site_id', 1))->first();
            if ($config && isset($config->min_withdrawal) && $request->valor < $config->min_withdrawal) {
                return response()->json(['error' => "Valor mínimo de saque: R$ {$config->min_withdrawal}"], 422);
            }

            \DB::beginTransaction();

            $saque = Saque::create([
                'user_id' => $user->id,
                'site_id' => config('tenant.site_id', 1),
                'valor' => $request->valor,
                'status' => 'Em processamento',
                'pix' => $request->pix,
                'tipo_pix' => $request->tipo_pix,
            ]);

            // Debitar saldo (prioriza saldo real)
            $remaining = $request->valor;
            $fromBalance = min($remaining, $user->balance ?? 0);
            $user->balance = ($user->balance ?? 0) - $fromBalance;
            $remaining -= $fromBalance;

            if ($remaining > 0) {
                $user->credito = ($user->credito ?? 0) - $remaining;
            }

            $user->save();

            // Registra transação de solicitação
            \DB::table('transactions')->insert([
                'site_id'     => $saque->site_id,
                'user_id'     => $user->id,
                'type'        => 'withdrawal_request',
                'amount'      => $saque->valor,
                'gateway_ref' => (string) $saque->id,
                'status'      => 'pending',
                'description' => "Solicitação de saque PIX (Ref: {$saque->id})",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Saque solicitado com sucesso!']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('SaquesController error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao processar saque'], 422);
        }
    }
}
