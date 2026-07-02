<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Usuário solicita um saque
     */
    public function requestWithdrawal(Request $request)
    {
        $user = Auth::user();
        $siteId = $user->site_id;
        
        // Busca configurações dinâmicas da banca
        $config = DB::table('configuracaos')->where('site_id', $siteId)->first();
        $site = DB::table('sites')->where('id', $siteId)->first();

        // 1. Verificação de Rollover de Bônus
        $activeBonus = DB::table('bonus_user')
                         ->where('user_id', $user->id)
                         ->where('status', 'active')
                         ->first();
        
        if ($activeBonus) {
            if ($activeBonus->current_rollover < $activeBonus->target_rollover) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Você possui um bônus ativo. Cumpra o rollover de R$ ' . 
                                 number_format($activeBonus->target_rollover, 2, ',', '.') . 
                                 ' para liberar saques. Progresso: R$ ' . 
                                 number_format($activeBonus->current_rollover, 2, ',', '.')
                ], 403);
            }
        }

        // 2. Verificação de Limites de Saque da Banca (Dinâmico)
        $minWithdrawal = $site->min_withdrawal ?? $config->min_withdrawal ?? 20.00;
        $maxWithdrawal = $site->max_withdrawal ?? $config->max_withdrawal ?? 1000.00;

        $request->validate([
            'amount' => "required|numeric|min:{$minWithdrawal}|max:{$maxWithdrawal}"
        ], [
            'amount.min' => 'O valor mínimo para saque é R$ ' . number_format($minWithdrawal, 2, ',', '.'),
            'amount.max' => 'O valor máximo por saque é R$ ' . number_format($maxWithdrawal, 2, ',', '.')
        ]);

        // 3. Verificação de Limite Diário (Dinâmico)
        $dailyLimit = $config->withdrawal_limit_day ?? 3; // Supondo que seja quantidade ou valor? 
        // Se for valor, usamos withdrawal_limit_day como teto de valor.
        $dailyTotal = DB::table('withdrawal_requests')
                        ->where('user_id', $user->id)
                        ->whereIn('status', ['pending', 'approved'])
                        ->whereDate('created_at', now()->toDateString())
                        ->sum('amount');
        
        // Se houver um limite de valor diário configurado
        $maxDailyAmount = $site->withdrawal_limit_day ?? $config->withdrawal_limit_day ?? 5000.00;

        if (($dailyTotal + $request->amount) > $maxDailyAmount) {
            return response()->json(['status' => 'error', 'message' => 'Você atingiu seu limite diário de valor de saques.'], 400);
        }

        if ($user->balance < $request->amount) {
            return response()->json(['status' => 'error', 'message' => 'Saldo insuficiente.'], 400);
        }

        DB::beginTransaction();
        try {
            // Deduz o saldo imediatamente para evitar gasto duplo
            DB::table('master_users')->where('id', $user->id)->decrement('balance', $request->amount);

            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'site_id' => $siteId,
                'amount' => $request->amount,
                'pix_key' => $user->pix_key,
                'pix_key_type' => $user->pix_key_type ?? 'cpf',
                'status' => 'pending'
            ]);

            // Log de Transação
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => 'withdrawal',
                'amount'     => $request->amount,
                'gateway_ref'=> "wd_{$withdrawal->id}",
                'status'     => 'pending',
                'description'=> "Solicitação de saque via PIX",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitação de saque enviada com sucesso.',
                'withdrawal' => $withdrawal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Erro ao processar saque.'], 500);
        }
    }

    /**
     * Lista saques do usuário
     */
    public function listWithdrawals()
    {
        $user = Auth::user();
        $withdrawals = WithdrawalRequest::where('user_id', $user->id)
                                        ->orderBy('created_at', 'desc')
                                        ->get();

        return response()->json($withdrawals);
    }

    /**
     * Admin: Aprovar um pedido de saque (Suporte SuitPay)
     */
    public function approve(Request $request, $id)
    {
        // 🛡️ SEGURANÇA: Apenas ADMIN pode aprovar saques
        $auth = Auth::user();
        if ($auth->nivel !== 'adm' && $auth->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $withdrawal = WithdrawalRequest::findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return response()->json(['error' => 'Pedido já processado'], 422);
        }

        $siteId = $withdrawal->site_id;
        $config = DB::table('configuracaos')->where('site_id', $siteId)->first();
        DB::beginTransaction();
        try {
            // Saque manual (Default)
            $gatewayRef = "manual_" . time();
            $gatewayName = $config->active_withdrawal_gateway ?? 'manual';

            $withdrawal->update([
                'status' => 'approved',
                'gateway_ref' => $gatewayRef,
                'admin_note' => $request->admin_note
            ]);

            // Log de Transação Finalizada
            DB::table('transactions')
                ->where('gateway_ref', "wd_{$withdrawal->id}")
                ->update([
                    'status' => 'completed',
                    'gateway_ref' => $gatewayRef,
                    'description' => "Saque de R$ " . number_format($withdrawal->amount, 2, ',', '.') . " Aprovado (" . ucfirst($gatewayName) . ")",
                    'updated_at' => now()
                ]);

            DB::commit();
            return response()->json(['message' => 'Saque aprovado com sucesso!', 'gateway' => $gatewayName]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro interno ao aprovar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Rejeitar um pedido de saque e devolver o saldo ao usuário
     */
    public function reject(Request $request, $id)
    {
        // 🛡️ SEGURANÇA: Apenas ADMIN pode rejeitar saques
        $auth = Auth::user();
        if ($auth->nivel !== 'adm' && $auth->role !== 'admin') {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $withdrawal = WithdrawalRequest::findOrFail($id);
        if ($withdrawal->status !== 'pending') {
            return response()->json(['error' => 'Pedido já processado'], 422);
        }

        DB::beginTransaction();
        try {
            $withdrawal->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note
            ]);

            // Devolve o saldo ao usuário
            DB::table('master_users')->where('id', $withdrawal->user_id)->increment('balance', $withdrawal->amount);

            // Log de Transação (Estorno)
            DB::table('transactions')->insert([
                'site_id'    => $withdrawal->site_id,
                'user_id'    => $withdrawal->user_id,
                'type'       => 'withdrawal_refund',
                'amount'     => $withdrawal->amount,
                'gateway_ref'=> "wd_{$withdrawal->id}_ref",
                'status'     => 'completed',
                'description'=> "Estorno de saque rejeitado: " . ($request->admin_note ?? 'Sem motivo informado'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Saque rejeitado e saldo devolvido.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao rejeitar saque.'], 500);
        }
    }
}
