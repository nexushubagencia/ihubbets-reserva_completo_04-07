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
            $userId = auth('api')->user()->id;
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
            $user = auth('api')->user();
            $saldo = $user->credito ?? $user->balance ?? 0;

            if ($request->valor > $saldo) {
                return response()->json(['error' => 'Valor superior ao saldo disponível.'], 422);
            }

            // Verificar limite mínimo de saque
            $config = \App\Models\Configuracao::where('site_id', config('tenant.site_id', 1))->first();
            if ($config && isset($config->min_withdrawal) && $request->valor < $config->min_withdrawal) {
                return response()->json(['error' => "Valor mínimo de saque: R$ {$config->min_withdrawal}"], 422);
            }

            \DB::beginTransaction();

            Saque::create([
                'user_id' => $user->id,
                'site_id' => config('tenant.site_id', 1),
                'valor' => $request->valor,
                'status' => 'Em processamento',
                'pix' => $request->pix,
                'tipo_pix' => $request->tipo_pix,
            ]);

            // Debitar saldo
            if (property_exists($user, 'credito')) {
                $user->credito = ($user->credito ?? 0) - $request->valor;
            } else {
                $user->balance = ($user->balance ?? 0) - $request->valor;
            }
            $user->save();

            \DB::commit();

            return response()->json(['result' => true, 'message' => 'Saque solicitado com sucesso!']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('SaquesController error: ' . $e->getMessage());
            return response()->json(['result' => false, 'error' => 'Erro ao processar saque'], 422);
        }
    }
}
