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
            $siteId = app('tenant.site_id');
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
                $seller->entradas = max(0, $seller->entradas - $bet->amount);
                $seller->comissoes = max(0, $seller->comissoes - ($bet->commission_amount ?? 0));
                $seller->quantidade_aposta = max(0, $seller->quantidade_aposta - 1);

                // Estorna saldo na carteira correta
                $selections = is_string($bet->selections) ? json_decode($bet->selections, true) : $bet->selections;
                $count = is_array($selections) ? count($selections) : 1;
                if ($count > 1) {
                    $seller->entrada_casadinha = max(0, $seller->entrada_casadinha - $bet->amount);
                    $seller->balance_bonus = ($seller->balance_bonus ?? 0) + $bet->amount;
                } else {
                    $seller->entrada_simples = max(0, $seller->entrada_simples - $bet->amount);
                    $seller->balance = ($seller->balance ?? 0) + $bet->amount;
                }

                $seller->save();
            }

            // Atualizar contadores do Gerente
            $manager = User::find($bet->manager_id);
            if ($manager) {
                $manager->entradas = max(0, $manager->entradas - $bet->amount);
                $manager->comissoes = max(0, $manager->comissoes - ($bet->commission_amount ?? 0));
                $manager->quantidade_aposta = max(0, $manager->quantidade_aposta - 1);
                $manager->save();
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
