<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ManualEvent;
use Carbon\Carbon;

/**
 * ResultProcessor — Motor de Resultados IHUB V2
 *
 * Equivalente ao sendResult() do sistema original (ORIGINAL_SOURCE/gerenciador)
 * mas modernizado para Laravel 13, usando transações e suportando:
 *  - Mercados padrão de futebol (Casa/Fora/Empate, Over/Under, Par/Ímpar, etc.)
 *  - Regra "Empate Anula Aposta" (devolução)
 *  - Liquidação automática de bilhetes (Ganho / Perdido)
 *  - Crédito de comissão ao Cambista e Gerente
 */
class ResultProcessor
{
    /* ------------------------------------------------------------------ */
    /*  PONTO DE ENTRADA                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Processa o resultado de um evento manual.
     *
     * @param  int    $eventId       ID do manual_event
     * @param  int    $homeFullTime  Gols casa (tempo completo)
     * @param  int    $awayFullTime  Gols fora (tempo completo)
     * @param  int    $homeHalfTime  Gols casa (1º tempo)
     * @param  int    $awayHalfTime  Gols fora (1º tempo)
     * @return array  Resumo do processamento
     */
    public function process(int $eventId, int $homeFullTime, int $awayFullTime, int $homeHalfTime, int $awayHalfTime): array
    {
        // Evitar reprocessamento
        if ($this->alreadyProcessed($eventId)) {
            return ['status' => 'skipped', 'message' => 'Resultado já processado para este evento.'];
        }

        DB::beginTransaction();
        try {
            // 1. Derivar todos os resultados verdadeiros do placar
            $winners = $this->deriveWinners($homeFullTime, $awayFullTime, $homeHalfTime, $awayHalfTime);

            // 2. Salvar o score no evento
            $scoreStr = "{$homeFullTime}-{$awayFullTime} ({$homeHalfTime}-{$awayHalfTime})";
            DB::table('manual_events')->where('id', $eventId)->update([
                'status'     => 'finished',
                'score'      => $scoreStr,
                'updated_at' => now(),
            ]);

            // 3. Buscar todos os palpites abertos para este evento
            $palpites = DB::table('bet_items')
                ->where('match_id', $eventId)
                ->where('status', 'pending')
                ->get();

            $stats = ['won' => 0, 'lost' => 0, 'returned' => 0, 'paid_out' => 0.0];

            foreach ($palpites as $palpite) {
                $isWinner   = in_array($palpite->selection_label, $winners['winners']);
                $isReturned = !$isWinner && in_array($palpite->selection_label, $winners['returned']);

                if ($isWinner) {
                    DB::table('bet_items')->where('id', $palpite->id)->update(['status' => 'won']);
                    $stats['won']++;
                } elseif ($isReturned) {
                    DB::table('bet_items')->where('id', $palpite->id)->update(['status' => 'returned']);
                    $stats['returned']++;
                } else {
                    DB::table('bet_items')->where('id', $palpite->id)->update(['status' => 'lost']);
                    $stats['lost']++;
                }
            }

            // 4. Liquidar os bilhetes (apostas) completos
            $paidOut = $this->settleBets($eventId);
            $stats['paid_out'] = $paidOut;

            // 5. Registrar o resultado para auditoria
            DB::table('audit_logs')->insert([
                'site_id'     => config('tenant.site_id', 1),
                'user_id'     => auth()->id(),
                'action'      => 'PROCESS_RESULT',
                'target_type' => 'manual_events',
                'target_id'   => $eventId,
                'new_values'  => json_encode(['score' => $scoreStr, 'winners' => $winners['winners']]),
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
            ]);

            DB::commit();

            return [
                'status'   => 'success',
                'score'    => $scoreStr,
                'markets'  => $winners['winners'],
                'stats'    => $stats,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ResultProcessor error: ' . $e->getMessage(), ['event_id' => $eventId]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  DERIVAÇÃO DE MERCADOS VENCEDORES                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Helper para extrair vencedores a partir de dados brutos da BetsAPI.
     * 
     * @param array $eventData Dados retornados pela v1/bet365/result
     * @return array
     */
    public function getWinnersFromApiData(array $eventData): array
    {
        $ss = $eventData['ss'] ?? '0-0';
        $scores = $eventData['scores'] ?? [];
        
        $parts = explode('-', $ss);
        $hFull = (int) ($parts[0] ?? 0);
        $aFull = (int) ($parts[1] ?? 0);

        // Tentar extrair o 1º Tempo (period 1)
        $hHalf = (int) ($scores['1']['home'] ?? $hFull); // Fallback se não tiver scores detalhados
        $aHalf = (int) ($scores['1']['away'] ?? $aFull);

        return $this->deriveWinners($hFull, $aFull, $hHalf, $aHalf);
    }

    /**
     * Para um dado placar, retorna a lista de todos os mercados que são verdadeiros.

     * Equivalente completo ao sistema original (ORIGINAL_SOURCE/gerenciador/HomeController.php)
     */
    public function deriveWinners(int $hFull, int $aFull, int $hHalf, int $aHalf): array
    {
        $winners  = [];
        $returned = []; // Empate Anula Aposta

        $h2 = $hFull - $hHalf; // Gols casa 2º tempo
        $a2 = $aFull - $aHalf; // Gols fora 2º tempo
        $total = $hFull + $aFull;
        $totalHalf = $hHalf + $aHalf;

        // ── 1º TEMPO ──────────────────────────────────────────────────────
        if ($hHalf > $aHalf) {
            $winners = array_merge($winners, ['Casa (1T)', 'Casa ou Empate (1T)', 'Casa ou Fora (1T)']);
        } elseif ($hHalf == $aHalf) {
            $winners = array_merge($winners, ['Empate (1T)', 'Casa ou Empate (1T)', 'Empate ou Fora (1T)']);
        } else {
            $winners = array_merge($winners, ['Fora (1T)', 'Empate ou Fora (1T)', 'Casa ou Fora (1T)']);
        }

        // Placar exato 1T
        $winners[] = "{$hHalf}-{$aHalf} (1T)";

        // ── 2º TEMPO ──────────────────────────────────────────────────────
        if ($h2 > $a2) {
            $winners = array_merge($winners, ['Casa (2T)', 'Casa ou Empate (2T)', 'Casa ou Fora (2T)']);
        } elseif ($h2 == $a2) {
            $winners = array_merge($winners, ['Empate (2T)', 'Casa ou Empate (2T)', 'Empate ou Fora (2T)']);
        } else {
            $winners = array_merge($winners, ['Fora (2T)', 'Empate ou Fora (2T)', 'Casa ou Fora (2T)']);
        }

        // ── TEMPO COMPLETO — VENCEDOR ─────────────────────────────────────
        if ($hFull > $aFull) {
            $winners = array_merge($winners, ['Casa', 'Casa ou Empate', 'Casa ou Fora', 'Casa - (Empate Anula Aposta)']);
            $returned[] = 'Empate Anula Aposta'; // devolve quem apostou em "Empate Anula Aposta" quando casa vence
            // Margem vitória Casa
            $margin = $hFull - $aFull;
            if ($margin == 1) $winners[] = 'Casa : 1';
            elseif ($margin == 2) $winners[] = 'Casa : 2';
            elseif ($margin == 3) $winners[] = 'Casa : 3';
            elseif ($margin >= 4) $winners[] = 'Casa : 4+';
        } elseif ($hFull == $aFull) {
            $winners = array_merge($winners, ['Empate', 'Casa ou Empate', 'Empate ou Fora']);
            $returned[] = 'Empate Anula Aposta';
            if ($total > 0) $winners[] = 'Empate com Gols';
            if ($total == 0) { $winners[] = 'Sem Gols'; $winners[] = 'Empate Sem Gols'; }
        } else {
            $winners = array_merge($winners, ['Fora', 'Empate ou Fora', 'Casa ou Fora', 'Fora - (Empate Anula Aposta)']);
            $returned[] = 'Empate Anula Aposta';
            $margin = $aFull - $hFull;
            if ($margin == 1) $winners[] = 'Fora : 1';
            elseif ($margin == 2) $winners[] = 'Fora : 2';
            elseif ($margin == 3) $winners[] = 'Fora : 3';
            elseif ($margin >= 4) $winners[] = 'Fora : 4+';
        }

        // ── INTERVALO / FINAL (Dupla Chance de Tempo) ─────────────────────
        $htResult = $hHalf <=> $aHalf; // -1=Fora vence 1T, 0=Empate 1T, 1=Casa vence 1T
        $ftResult = $hFull <=> $aFull; // -1=Fora vence FT, 0=Empate FT, 1=Casa vence FT
        $halfFinalKey = "{$htResult}:{$ftResult}";
        $halfFinalMap = [
            '1:1'   => 'Casa - Casa',
            '0:0'   => 'Empate - Empate',
            '-1:-1' => 'Fora - Fora',
            '1:0'   => 'Casa - Empate',
            '1:-1'  => 'Casa - Fora',
            '-1:1'  => 'Fora - Casa',
            '-1:0'  => 'Fora - Empate',
            '0:1'   => 'Empate - Casa',
            '0:-1'  => 'Empate - Fora',
        ];
        if (isset($halfFinalMap[$halfFinalKey])) {
            $winners[] = $halfFinalMap[$halfFinalKey];
        }

        // ── PLACAR EXATO TEMPO COMPLETO ───────────────────────────────────
        $winners[] = "{$hFull}-{$aFull}";

        // ── OVER / UNDER ───────────────────────────────────────────────────
        $overUnder = [0.5, 1.5, 2.5, 3.5, 4.5, 5.5, 6.5, 7.5, 8.5, 9.5];
        foreach ($overUnder as $line) {
            if ($total > $line)  $winners[] = "Mais de {$line}";
            if ($total < $line)  $winners[] = "Menos de {$line}";
        }

        // ── TOTAL DE GOLS (X Gols) ────────────────────────────────────────
        $goalLabel = $total >= 7 ? '7+' : (string)$total;
        $winners[] = "{$goalLabel} Gols";

        // ── PAR / ÍMPAR ────────────────────────────────────────────────────
        $winners[] = $total % 2 === 0 ? 'Par' : 'Ímpar';

        // ── AMBAS MARCAM ──────────────────────────────────────────────────
        if ($hFull > 0 && $aFull > 0) $winners[] = 'Ambos Marcam';
        else                            $winners[] = 'Ambos Não Marcam';

        return [
            'winners'  => array_unique($winners),
            'returned' => array_unique($returned),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  LIQUIDAÇÃO DE BILHETES                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Percorre todos os bilhetes que contêm palpites do evento processado,
     * verifica se todos os itens do bilhete foram resolvidos e define o status final.
     * Credita o prêmio e as comissões.
     */
    public function settleBets(int $eventId): float
    {
        $totalPaid = 0.0;

        // Bilhetes que contêm este evento
        $betIds = DB::table('bet_items')
            ->where('match_id', $eventId)
            ->pluck('bet_id')
            ->unique();

        foreach ($betIds as $betId) {
            $totalPaid += $this->settleSingleBet($betId);
        }

        return $totalPaid;
    }

    /**
     * Verifica e liquida um único bilhete se todos os itens estiverem resolvidos.
     */
    public function settleSingleBet(int $betId): float
    {
        $items = DB::table('bet_items')->where('bet_id', $betId)->get();

        // Ainda há palpites pendentes? Aguarda
        $pending = $items->where('status', 'pending')->count();
        if ($pending > 0) return 0.0;

        $bet = DB::table('bets')->where('id', $betId)->where('status', 'pending')->first();
        if (!$bet) return 0.0;

        $hasLost = $items->where('status', 'lost')->count() > 0;

        if ($hasLost) {
            // Bilhete PERDEU
            DB::table('bets')->where('id', $betId)->update([
                'status'     => 'lost',
                'updated_at' => now(),
            ]);
            return 0.0;
        } else {
            // Bilhete GANHOU (ou foi devolvido)
            // Recalcular o retorno real excluindo itens devolvidos
            $validItems = $items->whereNotIn('status', ['returned']);
            $multiOdd   = $validItems->reduce(fn($carry, $item) => $carry * $item->selection_odd, 1.0);
            $fullPayout = round($bet->amount * $multiOdd, 2);

            // 🏆 COMISSÃO SOBRE PRÊMIO (TAXA DE PAGAMENTO)
            $config = DB::table('configuracaos')->where('site_id', config('tenant.site_id', 1))->first();
            $prizeCommissionRate = $config->comissao_premio ?? 0;
            $prizeCommissionAmount = 0;

            if ($prizeCommissionRate > 0) {
                $prizeCommissionAmount = round(($fullPayout * $prizeCommissionRate) / 100, 2);
            }

            DB::table('bets')->where('id', $betId)->update([
                'status'                  => 'won',
                'potential_payout'        => $fullPayout,
                'prize_commission_amount' => $prizeCommissionAmount,
                'updated_at'              => now(),
            ]);

            // Creditar saldo do apostador
            $this->creditPayout($bet->user_id, $fullPayout, $betId, $bet->is_bonus_bet ?? 0);
            
            // 💰 PAGAMENTO DE COMISSÃO DE VENDA
            if ($bet->commission_amount > 0) {
                $this->creditCommission($bet->user_id, $bet->commission_amount, $betId);
            }

            return $fullPayout;
        }
    }


    /**
     * Credita o prêmio para o usuário (carteira Real ou Bônus).
     */
    private function creditPayout(int $userId, float $amount, int $betId, int $isBonusBet = 0): void
    {
        $walletColumn = ($isBonusBet === 1) ? 'balance_bonus' : 'balance';

        // 💰 Busca os dados do bilhete liquidado (Pode ser 'bets' ou 'apostas')
        $bet = DB::table('bets')->where('id', $betId)->first();
        $tableName = 'bets';
        
        if (!$bet) {
            $bet = DB::table('apostas')->where('id', $betId)->first();
            $tableName = 'apostas';
        }

        if (!$bet) return;

        $prizeCommissionAmount = $bet->prize_commission_amount ?? 0;
        $userPayout = $amount - $prizeCommissionAmount;

        // 1. Credita o valor LÍQUIDO ao usuário (Descontado a comissão do cambista)
        DB::table('master_users')->where('id', $userId)
            ->increment($walletColumn, $userPayout);

        // Se for aposta de bônus, checa se o rollover foi concluído para converter
        if ($isBonusBet === 1) {
            $this->checkAndConvertBonus($userId);
        }

        // 💰 ATUALIZAÇÃO CRÍTICA PARA FECHAMENTO DE CAIXA:
        if ($tableName === 'bets') {
            $cambistaId = $bet->cambista_id;
            $managerId  = $bet->manager_id;
            $ticketCode = $bet->ticket_code ?? $bet->id;
        } else {
            // No legado 'apostas', user_id é o cambista
            $cambistaId = $bet->user_id;
            $managerId  = $bet->gerente_id;
            $ticketCode = $bet->codigo_bilhete ?? $bet->id;
        }

        // Se não tiver cambista_id gravado, tenta buscar pelo user_id (Dono)
        if (!$cambistaId) {
            $owner = DB::table('master_users')->where('id', $bet->user_id)->first();
            if ($owner && ($owner->nivel == 'cambista' || $owner->role == 'seller')) {
                $cambistaId = $owner->id;
                $managerId  = $owner->gerente_id ?? $owner->parent_id ?? $managerId;
            }
        }

        // Registra a Saída no Cambista (O valor TOTAL da saída da banca continua sendo o prêmio cheio)
        if ($cambistaId) {
            DB::table('master_users')->where('id', $cambistaId)->increment('saidas', $amount);

            // 💰 Credita a TAXA DE PAGAMENTO (Comissão de Prêmio) ao Cambista
            if ($prizeCommissionAmount > 0) {
                DB::table('master_users')->where('id', $cambistaId)->increment('balance', $prizeCommissionAmount);
                DB::table('master_users')->where('id', $cambistaId)->increment('comissoes', $prizeCommissionAmount); 
                $this->logCommission($cambistaId, $prizeCommissionAmount, $betId, "Taxa de Pagamento (Comis. Prêmio) - Ref: {$ticketCode}");
            }
        }

        // Registra a Saída no Gerente
        if ($managerId) {
            DB::table('master_users')->where('id', $managerId)->increment('saidas', $amount);
        }

        DB::table('transactions')->insert([
            'site_id'    => config('tenant.site_id', 1),
            'user_id'    => $userId,
            'type'       => 'bet_payout',
            'amount'     => $userPayout,
            'gateway_ref'=> "{$tableName}_{$betId}",
            'status'     => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 🧙‍♂️ A Mágica: Converte Bônus em Real se o Rollover acabou
     */
    private function checkAndConvertBonus(int $userId)
    {
        $bonusUser = DB::table('bonus_user')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if ($bonusUser && ($bonusUser->current_rollover >= $bonusUser->target_rollover)) {
            $user = DB::table('master_users')->where('id', $userId)->first();
            $bonusBalance = $user->balance_bonus ?? 0;

            if ($bonusBalance > 0) {
                // Busca o teto configurado no painel (fallback R$ 500)
                $config = DB::table('configuracaos')->where('site_id', config('tenant.site_id', 1))->first();
                $maxConversion = $config->max_bonus_conversion ?? 500.00;
                
                $conversionAmount = min($bonusBalance, $maxConversion);

                DB::table('master_users')->where('id', $userId)->update([
                    'balance' => DB::raw("balance + {$conversionAmount}"),
                    'balance_bonus' => 0
                ]);

                DB::table('bonus_user')->where('id', $bonusUser->id)->update([
                    'status' => 'completed',
                    'updated_at' => now()
                ]);

                DB::table('transactions')->insert([
                    'site_id'    => config('tenant.site_id', 1),
                    'user_id'    => $userId,
                    'type'       => 'manual_credit',
                    'amount'     => $conversionAmount,
                    'status'     => 'completed',
                    'description'=> "Conversão de Bônus (Rollover concluído! Teto aplicado: R$ {$maxConversion})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * 💰 Motor de Comissões Hierárquico (Afiliados e Rede)
     * Credita os valores pré-calculados no momento da aposta.
     */
    private function creditCommission(int $userId, float $commissionAmount, int $betId): void
    {
        $bet = DB::table('bets')->where('id', $betId)->first();
        $tableName = 'bets';
        
        if (!$bet) {
            $bet = DB::table('apostas')->where('id', $betId)->first();
            $tableName = 'apostas';
        }

        if (!$bet) return;

        if ($tableName === 'bets') {
            $cambistaId = $bet->cambista_id;
            $managerId  = $bet->manager_id;
            $cambistaComm = $bet->commission_amount ?? 0;
            $managerComm  = $bet->manager_commission_amount ?? 0;
            $ticketCode = $bet->ticket_code ?? $bet->id;
        } else {
            $cambistaId = $bet->user_id;
            $managerId  = $bet->gerente_id;
            $cambistaComm = $bet->comicao ?? 0;
            $managerComm  = 0; // Legado geralmente não tem comissão de gerente separada aqui
            $ticketCode = $bet->codigo_bilhete ?? $bet->id;
        }

        // 1. 💰 CRÉDITO AO CAMBISTA / AFILIADO
        if ($cambistaId && $cambistaComm > 0) {
            DB::table('master_users')->where('id', $cambistaId)->increment('balance', $cambistaComm);
            $this->logCommission($cambistaId, $cambistaComm, $betId, "Comissão Cambista/Afiliado (Ref: {$ticketCode})");
        }

        // 2. 🎩 CRÉDITO AO GERENTE (Gestão Online)
        if ($managerId && $managerComm > 0) {
            DB::table('master_users')->where('id', $bet->manager_id)->increment('balance', $bet->manager_commission_amount);
            $this->logCommission($bet->manager_id, $bet->manager_commission_amount, $betId, "Comissão Gerente/Gestão (Ref: {$bet->ticket_code})");
        }
    }

    /**
     * Auxiliar para registrar logs de comissão
     */
    private function logCommission(int $userId, float $amount, int $betId, string $desc): void
    {
        DB::table('transactions')->insert([
            'site_id'     => config('tenant.site_id', 1),
            'user_id'     => $userId,
            'type'        => 'commission',
            'amount'      => $amount,
            'gateway_ref' => "commission_bet_{$betId}",
            'status'      => 'completed',
            'description' => $desc,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Processa o resultado de um sorteio de Loto.
     */
    public function processLoto(string $type, string $concurso, array $drawnNumbers, bool $autoPay = true): array
    {
        Log::info("Processando Loto: {$type} - {$concurso}", ['drawn' => $drawnNumbers, 'autoPay' => $autoPay]);

        DB::beginTransaction();
        try {
            $apostas = DB::table('apostas')
                ->join('palpite_lotos', 'apostas.id', '=', 'palpite_lotos.aposta_id')
                ->where('apostas.modalidade', 'Loto')
                ->where('apostas.tipo', $type)
                ->where('palpite_lotos.concurso', $concurso)
                ->where('apostas.status', 'Aberto')
                ->select('apostas.*')
                ->distinct()
                ->get();

            $stats = ['won' => 0, 'lost' => 0, 'paid_out' => 0.0];

            foreach ($apostas as $aposta) {
                $dezenasApostadas = DB::table('palpite_lotos')
                    ->where('aposta_id', $aposta->id)
                    ->pluck('dezena')
                    ->toArray();

                $acertos = count(array_intersect($dezenasApostadas, $drawnNumbers));
                $totalDrawn = count($drawnNumbers);
                
                // Ganha se acertar TODOS os números sorteados (Quininha=5, Seninha=6)
                if ($acertos >= $totalDrawn) {
                    DB::table('apostas')->where('id', $aposta->id)->update([
                        'status' => 'won',
                        'acertos_palpites' => $acertos,
                        'updated_at' => now()
                    ]);
                    
                    if ($autoPay) {
                        $this->creditPayout($aposta->user_id, $aposta->retorno_possivel, $aposta->id);
                        $stats['paid_out'] += $aposta->retorno_possivel;
                    }
                    
                    $stats['won']++;
                } else {
                    DB::table('apostas')->where('id', $aposta->id)->update([
                        'status' => 'lost',
                        'acertos_palpites' => $acertos,
                        'updated_at' => now()
                    ]);
                    $stats['lost']++;
                }

                DB::table('palpite_lotos')->where('aposta_id', $aposta->id)->update(['status' => 'Processado']);
            }

            DB::commit();
            return ['status' => 'success', 'stats' => $stats];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error processing Loto: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function alreadyProcessed(int $eventId): bool
    {
        return DB::table('manual_events')
            ->where('id', $eventId)
            ->where('status', 'finished')
            ->exists();
    }
}
