<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaqueApiController extends Controller
{
    /**
     * Solicitar um saque
     */
    public function request(Request $request)
    {
        $user = Auth::user();
        $siteId = $user->site_id;

        try {
            $config = DB::table('configuracaos')->where('site_id', $siteId)->first();
            $site = DB::table('sites')->where('id', $siteId)->first();

            $activeBonus = DB::table('bonus_user')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($activeBonus && $activeBonus->current_rollover < $activeBonus->target_rollover) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Você possui um bônus ativo. Cumpra o rollover de R$ ' .
                        number_format($activeBonus->target_rollover, 2, ',', '.') .
                        ' para liberar saques. Progresso: R$ ' .
                        number_format($activeBonus->current_rollover, 2, ',', '.')
                ], 403);
            }

            $minWithdrawal = $site->min_withdrawal ?? $config->min_withdrawal ?? 20.00;
            $maxWithdrawal = $site->max_withdrawal ?? $config->max_withdrawal ?? 1000.00;

            $request->validate([
                'amount' => "required|numeric|min:{$minWithdrawal}|max:{$maxWithdrawal}"
            ], [
                'amount.min' => 'O valor mínimo para saque é R$ ' . number_format($minWithdrawal, 2, ',', '.'),
                'amount.max' => 'O valor máximo por saque é R$ ' . number_format($maxWithdrawal, 2, ',', '.')
            ]);

            $dailyLimit = $site->withdrawal_limit_day ?? $config->withdrawal_limit_day ?? 5000.00;
            $dailyTotal = DB::table('withdrawal_requests')
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('created_at', now()->toDateString())
                ->sum('amount');

            if (($dailyTotal + $request->amount) > $dailyLimit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Você atingiu seu limite diário de saques.'
                ], 400);
            }

            if ($user->balance < $request->amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Saldo insuficiente.'
                ], 400);
            }

            DB::beginTransaction();

            DB::table('master_users')->where('id', $user->id)->decrement('balance', $request->amount);

            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'site_id' => $siteId,
                'amount' => $request->amount,
                'pix_key' => $user->pix_key,
                'pix_key_type' => $user->pix_key_type ?? 'CPF',
                'status' => 'pending'
            ]);

            DB::table('transactions')->insert([
                'site_id' => $siteId,
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'gateway_ref' => "wd_{$withdrawal->id}",
                'status' => 'pending',
                'description' => "Solicitação de saque via API",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Solicitação de saque enviada com sucesso.',
                'withdrawal' => [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'pix_key' => $withdrawal->pix_key,
                    'created_at' => $withdrawal->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('SaqueApiController error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar saque.'
            ], 500);
        }
    }

    /**
     * Consultar status de um saque específico
     */
    public function status(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $withdrawal = WithdrawalRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$withdrawal) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Solicitação de saque não encontrada.'
                ], 404);
            }

            $statusLabels = [
                'pending' => 'Pendente',
                'approved' => 'Aprovado',
                'rejected' => 'Rejeitado',
                'processing' => 'Processando',
                'completed' => 'Concluído',
            ];

            return response()->json([
                'status' => 'success',
                'withdrawal' => [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'status_label' => $statusLabels[$withdrawal->status] ?? $withdrawal->status,
                    'pix_key' => $withdrawal->pix_key,
                    'pix_key_type' => $withdrawal->pix_key_type,
                    'gateway_ref' => $withdrawal->gateway_ref,
                    'admin_note' => $withdrawal->admin_note,
                    'created_at' => $withdrawal->created_at,
                    'updated_at' => $withdrawal->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao consultar status do saque.'
            ], 500);
        }
    }

    /**
     * Histórico de saques do usuário
     */
    public function history(Request $request)
    {
        try {
            $user = Auth::user();

            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);
            $statusFilter = $request->get('status');

            $query = WithdrawalRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            $withdrawals = $query->paginate($perPage, ['*'], 'page', $page);

            $statusLabels = [
                'pending' => 'Pendente',
                'approved' => 'Aprovado',
                'rejected' => 'Rejeitado',
                'processing' => 'Processando',
                'completed' => 'Concluído',
            ];

            $formatted = $withdrawals->getCollection()->map(function ($w) use ($statusLabels) {
                return [
                    'id' => $w->id,
                    'amount' => $w->amount,
                    'status' => $w->status,
                    'status_label' => $statusLabels[$w->status] ?? $w->status,
                    'pix_key' => $w->pix_key,
                    'created_at' => $w->created_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'withdrawals' => $formatted,
                'pagination' => [
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'per_page' => $withdrawals->perPage(),
                    'total' => $withdrawals->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar histórico de saques.'
            ], 500);
        }
    }
}
