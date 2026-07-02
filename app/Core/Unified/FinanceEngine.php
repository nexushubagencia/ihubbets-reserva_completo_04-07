<?php

namespace App\Core\Unified;

use App\Models\User;
use App\Models\Bet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * 💰 FinanceEngine - Motor Financeiro Unificado
 * 
 * Lógica de fechamento de caixa, lançamentos e cálculos de comissões.
 */
class FinanceEngine
{
    /**
     * Retorna o resumo do caixa para Gerentes ou Cambistas
     */
    public static function getSettlementData(string $role, $filterId = null)
    {
        $siteId = config('tenant.site_id', 1);
        
        $query = User::where('site_id', $siteId);
        
        if ($role === 'manager') {
            $query->where('role', 'manager');
            if ($filterId) $query->where('id', $filterId);
        } else {
            $query->where('role', 'seller');
            if ($filterId) {
                // Se for gerente logado vendo seus cambistas
                if (auth()->user()->role === 'manager') {
                    $query->where('manager_id', auth()->user()->id);
                }
                $query->where('id', $filterId);
            }
        }

        $users = $query->orderBy('quantidade_aposta', 'DESC')->get();
        $report = [];

        foreach ($users as $user) {
            // Entradas em Aberto (Apostas que ainda não foram liquidadas)
            $entradasAbertas = Bet::where('site_id', $siteId)
                ->where('status', 'open')
                ->when($role === 'manager', function($q) use ($user) {
                    return $q->where('manager_id', $user->id);
                }, function($q) use ($user) {
                    return $q->where('user_id', $user->id);
                })
                ->sum('amount');

            // Cálculo do Total: Entradas + Lançamentos - (Saídas + Comissões)
            // Nota: No legado, 'total' é o que o colaborador deve à banca (ou vice-versa)
            $total = ($user->entradas + $user->entrada_loto + $user->lancamentos) - ($user->saidas + $user->comissoes);
            
            $comissaoGerente = 0;
            if ($role === 'manager' && $total > 0) {
                $comissaoGerente = $total * ($user->manager_commission_rate / 100);
            }

            $report[] = [
                'id'                => $user->id,
                'colaborador'       => $user->name,
                'quantidade'        => $user->quantidade_aposta,
                'entradas'          => $user->entradas + $user->entrada_loto,
                'entradas_abertas'  => $entradasAbertas,
                'saidas'            => $user->saidas,
                'comissoes'         => $user->comissoes,
                'lancamentos'       => $user->lancamentos,
                'total'             => $total,
                'comissao_gerente'  => $comissaoGerente,
                'total_final'       => $total - $comissaoGerente
            ];
        }

        return $report;
    }

    /**
     * Realiza o fechamento (zeramento) do caixa de um usuário
     */
    public static function closeSettlement($userId)
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);
            
            // Log do fechamento antes de zerar (Opcional: Criar tabela de historico_caixa)
            
            // Zerar contadores
            $user->update([
                'entradas'           => 0,
                'saidas'             => 0,
                'comissoes'          => 0,
                'lancamentos'        => 0,
                'quantidade_aposta'  => 0,
                'entrada_loto'       => 0,
                'entrada_casadinha'  => 0,
                'entrada_simples'    => 0,
            ]);

            // No legado, removia-se os lançamentos. 
            // Na nova arquitetura, podemos marcar como 'settled' ou apenas seguir o padrão legado.
            // Vou seguir o legado para garantir que o "saldo de lançamentos" do usuário também zere.
            // Mas as transações no banco novo devem permanecer para histórico.
            
            return true;
        });
    }

    /**
     * Registra um lançamento manual (Crédito/Débito)
     */
    public static function addAdjustment($userId, $type, $amount, $description)
    {
        return DB::transaction(function () use ($userId, $type, $amount, $description) {
            $user = User::findOrFail($userId);
            $siteId = config('tenant.site_id', 1);

            if ($type === 'Crédito') {
                $user->increment('lancamentos', $amount);
            } else {
                $user->decrement('lancamentos', $amount);
            }

            // Registrar na tabela de transações do sistema novo
            Transaction::create([
                'site_id'     => $siteId,
                'user_id'     => $userId,
                'type'        => 'adjustment',
                'amount'      => ($type === 'Crédito' ? $amount : -$amount),
                'status'      => 'completed',
                'description' => $description
            ]);

            return true;
        });
    }
}
