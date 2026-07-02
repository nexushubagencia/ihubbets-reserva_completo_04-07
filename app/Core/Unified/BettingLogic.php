<?php

namespace App\Core\Unified;

use App\Models\Bet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ⚽ BettingLogic - Lógica de Apostas e Liquidação
 * 
 * Gerencia o ciclo de vida dos bilhetes (Criação, Cancelamento, Liquidação).
 */
class BettingLogic
{
    /**
     * Cancela um bilhete e estorna os valores
     */
    public static function cancel($betId, User $performer)
    {
        return DB::transaction(function () use ($betId, $performer) {
            $siteId = config('tenant.site_id', 1);
            $bet = Bet::where('id', $betId)->where('site_id', $siteId)->lockForUpdate()->first();

            if (!$bet || $bet->status === 'cancelled') {
                throw new \Exception('Bilhete não encontrado ou já cancelado.');
            }

            // SEGURANÇA: Hierarquia
            if ($performer->role === 'manager' && $bet->manager_id !== $performer->id) {
                throw new \Exception('Não autorizado (equipe diferente).');
            }

            if ($performer->role === 'seller' && !$performer->can_cancel_tickets) {
                throw new \Exception('Você não tem permissão para cancelar bilhetes.');
            }

            // Atualizar contadores do Cambista (Vendedor)
            $seller = User::find($bet->user_id);
            if ($seller) {
                $seller->decrement('entradas', $bet->amount);
                $seller->decrement('comissoes', $bet->commission_amount ?? 0);
                $seller->decrement('quantidade_aposta', 1);
                
                // Se for aposta simples/casadinha, decrementa o contador específico se existir
                if (isset($seller->entrada_simples)) {
                    // Lógica para separar simples/casadinha aqui
                }
            }

            // Atualizar contadores do Gerente
            $manager = User::find($bet->manager_id);
            if ($manager) {
                $manager->decrement('entradas', $bet->amount);
                $manager->decrement('comissoes', $bet->commission_amount ?? 0);
                $manager->decrement('quantidade_aposta', 1);
            }

            $bet->update(['status' => 'cancelled']);

            return true;
        });
    }

    /**
     * Liquida um bilhete (Vencido ou Perdido)
     */
    public static function settle($betId, $status)
    {
        return DB::transaction(function () use ($betId, $status) {
            $bet = Bet::findOrFail($betId);
            if ($bet->status !== 'open') return;

            $bet->status = $status;
            $bet->save();

            if ($status === 'won') {
                // Incrementar 'saidas' (prêmios pagos) nos contadores
                $seller = User::find($bet->user_id);
                if ($seller) $seller->increment('saidas', $bet->potential_payout);

                $manager = User::find($bet->manager_id);
                if ($manager) $manager->increment('saidas', $bet->potential_payout);
            }
        });
    }
}
