<?php

namespace App\Services;

use App\Models\Aposta;
use App\Models\Bet;
use App\Models\BetItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UnifiedBetService — Camada de unificação entre apostas legado e modernas.
 *
 * Responsabilidade:
 *   - Toda aposta criada na tabela legado `apostas`/`palpites` deve ter um
 *     espelho na tabela moderna `bets`/`bet_items` para que a liquidação
 *     automática (SettleApiBets / ResultProcessor) funcione.
 *
 * Regras:
 *   - Nunca cria duplicatas (usa legacy_aposta_id como chave de ligação).
 *   - Nunca altera saldo/comissão (isso continua no fluxo legado).
 *   - Apenas mantém os dois mundos sincronizados em status e estrutura.
 */
class UnifiedBetService
{
    /**
     * Cria ou recupera o Bet moderno correspondente a uma Aposta legado.
     */
    public function createFromAposta(Aposta $aposta): ?Bet
    {
        if (empty($aposta->id)) {
            return null;
        }

        // Já existe? Retorna o existente.
        $existing = Bet::withoutGlobalScope('site')
            ->where('legacy_aposta_id', $aposta->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $palpites = $aposta->palpites ?? collect();
        $selections = $this->buildSelections($palpites);
        $status = $this->mapStatus($aposta->status);

        // Determina papéis do usuário e em qual tabela ele existe
        $owner = User::find($aposta->user_id);
        $isCambista = $owner && ($owner->nivel === 'cambista' || $owner->role === 'seller');

        // Se o dono da aposta não está em master_users (cliente online da tabela users),
        // usamos user_id do admin/sistema (1) e guardamos o ID real em online_user_id.
        $masterUserId = $aposta->user_id;
        $onlineUserId = null;
        $masterExists = User::where('id', $aposta->user_id)->exists();
        if (!$masterExists) {
            $onlineUserId = $aposta->user_id;
            $masterUserId = 1; // fallback para admin/sistema
        }

        DB::beginTransaction();
        try {
            $bet = Bet::create([
                'site_id'          => $aposta->site_id,
                'legacy_aposta_id' => $aposta->id,
                'user_id'          => $masterUserId,
                'online_user_id'   => $onlineUserId,
                'manager_id'       => $aposta->gerente_id ?: null,
                'cambista_id'      => $isCambista ? $aposta->user_id : null,
                'client_name'      => $aposta->cliente ?: 'Cliente',
                'amount'           => (float) $aposta->valor_apostado,
                'potential_payout' => (float) $aposta->retorno_possivel,
                'commission_amount'=> (float) ($aposta->comicao ?? 0),
                'status'           => $status,
                'selections'       => $selections,
                'ticket_code'      => $this->uniqueTicketCode($aposta),
                'is_bonus_bet'     => 0,
                'can_cash_out'     => 0,
            ]);

            foreach ($palpites as $palpite) {
                BetItem::create([
                    'bet_id'          => $bet->id,
                    'match_id'        => $palpite->match_id ?? 0,
                    'league_name'     => null,
                    'home_team'       => $palpite->home_team ?? 'Casa',
                    'away_team'       => $palpite->away_team ?? 'Fora',
                    'market_name'     => $palpite->market_name ?? '1x2',
                    'selection_label' => $palpite->selection_label ?? '',
                    'selection_odd'   => (float) ($palpite->selection_odd ?? 1.0),
                    'status'          => $status === 'open' ? 'pending' : $status,
                ]);
            }

            DB::commit();
            return $bet;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('UnifiedBetService::createFromAposta failed', [
                'aposta_id' => $aposta->id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sincroniza o status do Bet moderno com a Aposta legado.
     */
    public function syncStatus(Aposta $aposta, ?string $forceStatus = null): void
    {
        if (empty($aposta->id)) {
            return;
        }

        $bet = Bet::withoutGlobalScope('site')
            ->where('legacy_aposta_id', $aposta->id)
            ->first();

        if (!$bet) {
            // Se ainda não existe, cria agora e aplica o status.
            $bet = $this->createFromAposta($aposta);
            if (!$bet) {
                return;
            }
        }

        $newStatus = $forceStatus ?? $this->mapStatus($aposta->status);
        if ($bet->status === $newStatus) {
            return;
        }

        DB::transaction(function () use ($bet, $newStatus) {
            $bet->update(['status' => $newStatus]);

            // Mapeia status do Bet para status do BetItem
            $itemStatus = $newStatus === 'open' ? 'pending' : $newStatus;
            BetItem::where('bet_id', $bet->id)->update(['status' => $itemStatus]);
        });
    }

    /**
     * Marca o Bet moderno como cancelado.
     */
    public function cancel(Aposta $aposta): void
    {
        $this->syncStatus($aposta, 'cancelled');
    }

    /**
     * Marca o Bet moderno como cash-out.
     */
    public function cashOut(Aposta $aposta, float $returnAmount): void
    {
        if (empty($aposta->id)) {
            return;
        }

        $bet = Bet::withoutGlobalScope('site')
            ->where('legacy_aposta_id', $aposta->id)
            ->first();

        if (!$bet) {
            $bet = $this->createFromAposta($aposta);
            if (!$bet) {
                return;
            }
        }

        DB::transaction(function () use ($bet, $returnAmount) {
            $bet->update([
                'status'          => 'cancelled',
                'cash_out_amount' => $returnAmount,
            ]);
            BetItem::where('bet_id', $bet->id)->update(['status' => 'cancelled']);
        });
    }

    /**
     * Gera um ticket_code único para o espelho moderno.
     */
    private function uniqueTicketCode(Aposta $aposta): string
    {
        $base = $aposta->codigo_bilhete ?: ('A' . $aposta->id);
        $code = substr($base, 0, 20);

        $counter = 0;
        while (Bet::withoutGlobalScope('site')->where('ticket_code', $code)->exists()) {
            $suffix = '-' . $counter;
            $code = substr($base, 0, 20 - strlen($suffix)) . $suffix;
            $counter++;
        }

        return $code;
    }

    /**
     * Converte palpites legados no formato JSON usado pelo Bet moderno.
     */
    private function buildSelections($palpites): array
    {
        $selections = [];
        foreach ($palpites as $p) {
            $selections[] = [
                'match_id'        => $p->match_id ?? 0,
                'home_team'       => $p->home_team ?? 'Casa',
                'away_team'       => $p->away_team ?? 'Fora',
                'market_name'     => $p->market_name ?? '1x2',
                'selection_label' => $p->selection_label ?? '',
                'selection_odd'   => (float) ($p->selection_odd ?? 1.0),
            ];
        }
        return $selections;
    }

    /**
     * Mapeia status legado → status moderno.
     */
    private function mapStatus(?string $status): string
    {
        $status = trim(strtolower($status ?? ''));

        return match ($status) {
            'aberto', 'aguardando', 'open', 'pending' => 'open',
            'ganhou', 'venceu', 'won'                => 'won',
            'perdeu', 'lost'                         => 'lost',
            'cancelado', 'devolvido', 'cashout',
            'cancelled'                              => 'cancelled',
            default                                  => 'open',
        };
    }
}
