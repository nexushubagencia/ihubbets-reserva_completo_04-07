<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfiguracaoController extends Controller
{
    /**
     * Retornar configuração atual do site
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $siteId = $user->site_id ?? config('tenant.site_id', 1);

            $config = Configuracao::where('site_id', $siteId)->first();

            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Configuração não encontrada para este site.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'configuracao' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar configurações.'
            ], 500);
        }
    }

    /**
     * Atualizar valores de configuração
     */
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->nivel !== 'adm' && $user->role !== 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acesso negado. Apenas administradores podem alterar configurações.'
                ], 403);
            }

            $siteId = $user->site_id ?? config('tenant.site_id', 1);

            $config = Configuracao::where('site_id', $siteId)->first();

            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Configuração não encontrada.'
                ], 404);
            }

            $validated = $request->validate([
                'valor_mini_aposta' => 'nullable|numeric|min:0',
                'valor_max_aposta' => 'nullable|numeric|min:0',
                'menor_valor_loto' => 'nullable|numeric|min:0',
                'max_valor_loto' => 'nullable|numeric|min:0',
                'premio_max' => 'nullable|numeric|min:0',
                'cotacao_mini_bilhete' => 'nullable|numeric|min:0',
                'cotacao_max_bilhete' => 'nullable|numeric|min:0',
                'bloquear_odd_abaixo' => 'nullable|numeric|min:0',
                'travar_odd_acima' => 'nullable|numeric|min:0',
                'quantidade_jogos_mini_bilhete' => 'nullable|integer|min:1',
                'quantidade_jogos_max_bilhete' => 'nullable|integer|min:1',
                'aposta_ativa' => 'nullable|boolean',
                'bloq_aposta_madrugada' => 'nullable|boolean',
                'min_deposit' => 'nullable|numeric|min:0',
                'max_deposit' => 'nullable|numeric|min:0',
                'min_withdrawal' => 'nullable|numeric|min:0',
                'max_withdrawal' => 'nullable|numeric|min:0',
                'withdrawal_limit_day' => 'nullable|numeric|min:0',
                'max_bonus_conversion' => 'nullable|numeric|min:0',
                'futebol_ao_vivo' => 'nullable|boolean',
                'op_futebol' => 'nullable|boolean',
                'op_ufcbox' => 'nullable|boolean',
                'op_quininha' => 'nullable|boolean',
                'op_seninha' => 'nullable|boolean',
                'op_basquete' => 'nullable|boolean',
                'op_tenis' => 'nullable|boolean',
                'op_volei' => 'nullable|boolean',
                'active_deposit_gateway' => 'nullable|string',
                'active_withdrawal_gateway' => 'nullable|string',
            ]);

            $config->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Configurações atualizadas com sucesso!',
                'configuracao' => $config->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retornar limites de aposta e saque
     */
    public function getLimits(Request $request)
    {
        try {
            $user = $request->user();
            $siteId = $user->site_id ?? config('tenant.site_id', 1);

            $config = Configuracao::where('site_id', $siteId)->first();

            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Configuração não encontrada.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'limits' => [
                    'bet' => [
                        'min' => (float) ($config->valor_mini_aposta ?? 1.00),
                        'max' => (float) ($config->valor_max_aposta ?? 10000.00),
                    ],
                    'loto' => [
                        'min' => (float) ($config->menor_valor_loto ?? 1.00),
                        'max' => (float) ($config->max_valor_loto ?? 500.00),
                        'prize_max' => (float) ($config->premio_max ?? 50000.00),
                    ],
                    'deposit' => [
                        'min' => (float) ($config->min_deposit ?? 20.00),
                        'max' => (float) ($config->max_deposit ?? 10000.00),
                    ],
                    'withdrawal' => [
                        'min' => (float) ($config->min_withdrawal ?? 20.00),
                        'max' => (float) ($config->max_withdrawal ?? 1000.00),
                        'daily_limit' => (float) ($config->withdrawal_limit_day ?? 5000.00),
                    ],
                    'odds' => [
                        'min' => (float) ($config->cotacao_mini_bilhete ?? 1.01),
                        'max' => (float) ($config->cotacao_max_bilhete ?? 100.00),
                        'block_below' => (float) ($config->bloquear_odd_abaixo ?? 1.00),
                        'lock_above' => (float) ($config->travar_odd_acima ?? 150.00),
                    ],
                    'bilhete' => [
                        'min_games' => (int) ($config->quantidade_jogos_mini_bilhete ?? 2),
                        'max_games' => (int) ($config->quantidade_jogos_max_bilhete ?? 30),
                    ],
                    'bonus' => [
                        'max_conversion' => (float) ($config->max_bonus_conversion ?? 5000.00),
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar limites.'
            ], 500);
        }
    }
}
