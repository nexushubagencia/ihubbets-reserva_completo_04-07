<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * BetValidator — Travas de Segurança IHUB V2
 *
 * Valida cada tentativa de aposta contra as regras configuradas pelo Admin.
 * Corrigido para usar os nomes de colunas corretos da tabela site_settings
 * e fallback para configuracaos (legado) quando necessário.
 *
 * Mapeamento de colunas (site_settings):
 *   min_bet_amount       → valor_mini_aposta
 *   max_bet_amount       → valor_max_aposta
 *   max_payout           → premio_max
 *
 * Uso:
 *   $validator = new BetValidator($siteId);
 *   $result    = $validator->validate($betPayload);
 *   if (!$result['ok']) { return response()->json(['error' => $result['message']], 422); }
 */
class BetValidator
{
    private object $settings;
    private int    $siteId;

    public function __construct(int $siteId)
    {
        $this->siteId   = $siteId;
        $this->settings = $this->loadSettings();
    }

    /* ------------------------------------------------------------------ */
    /*  PONTO DE ENTRADA                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Valida a aposta.
     *
     * @param array $bet
     * @param object|null $user Pre-locked user object
     */
    public function validate(array $bet, $user = null): array
    {
        if (!$user) {
            $user = DB::table('master_users')->where('id', $bet['user_id'])->first();
        }
        if (!$user) return $this->fail('Usuário não encontrado.');

        // 0. Usuário está ativo?
        if (($user->status ?? 1) == 0) {
            return $this->fail('Sua conta está bloqueada ou inativa. Entre em contato com o suporte.');
        }

        // 1. Sistema de apostas está ligado?
        if (!$this->settings->aposta_ativa) {
            return $this->fail('As apostas estão suspensas no momento. Tente novamente mais tarde.');
        }

        // 1.1 Toggle por modalidade - verifica se a modalidade está ativa
        $sportToggles = [
            'Futebol'     => 'op_futebol',
            'Basquete'    => 'op_basquete',
            'Volei'       => 'op_volei',
            'Tenis'       => 'op_tenis',
            'UFC'         => 'op_ufcbox',
            'Boxe'        => 'op_ufcbox',
            'E-Sports'    => 'op_e_sports',
            'Loto'        => null, // Loto sempre habilitado se op_quininha/seninha ativo
        ];

        // Detecta modalidade a partir dos selections
        $sportType = $bet['sport_type'] ?? null;
        if (!$sportType && isset($bet['selections'][0])) {
            $first = $bet['selections'][0];
            $sportType = $first['sport'] ?? $first['modalidade'] ?? null;
        }

        if ($sportType && isset($sportToggles[$sportType]) && $sportToggles[$sportType]) {
            $toggleField = $sportToggles[$sportType];
            if (property_exists($this->settings, $toggleField) && !$this->settings->$toggleField) {
                return $this->fail("A modalidade {$sportType} está desabilitada para apostas no momento.");
            }
        }

        // 2. Restrição de madrugada
        if ($this->settings->bloq_aposta_madrugada) {
            $hour = (int) now()->format('H');
            if ($hour >= 0 && $hour < 6) {
                return $this->fail('Apostas bloqueadas no período de madrugada (00h-06h).');
            }
        }

        // 3. Data limite dos jogos — verifica matchs E manual_events
        foreach ($bet['selections'] as $sel) {
            $matchId = $sel['match_id'] ?? $sel['partida'] ?? null;
            
            // Se for Loto (sem ID de partida real), pula validação de data de jogo
            if (!$matchId) continue;

            // Tenta na tabela matchs primeiro
            $matchDate = DB::table('matchs')->where('id', $matchId)->value('date');
            // Fallback para manual_events
            if (!$matchDate) {
                $matchDate = DB::table('manual_events')->where('id', $matchId)->value('start_time');
            }
            if ($matchDate) {
                $limitDatetime = $this->settings->data_limite_jogos . ' ' . $this->settings->hours_limit_date;
                if ($matchDate > $limitDatetime) {
                    return $this->fail('Um ou mais jogos estão além da data limite configurada.');
                }
            }
        }

        // 4. Usuário bloqueado?
        if ((int)$user->status === 0) {
            return $this->fail('Sua conta está suspensa ou inativa. Contate o suporte.');
        }

        // 4.1 Trava de Saldo (O Coração da Blindagem)
        $walletField = 'balance'; // Simples
        if (($bet['wallet_type'] ?? '') === 'casadinha') $walletField = 'balance_bonus';
        
        $currentBalance = (float)($user->$walletField ?? 0);
        
        // 🎰 Loto usa saldo_loto - entrada_loto
        if (($bet['wallet_type'] ?? '') === 'loto') {
            $currentBalance = (float)($user->saldo_loto ?? 0) - (float)($user->entrada_loto ?? 0);
        }

        if ($currentBalance < (float)$bet['amount']) {
            return $this->fail("Saldo insuficiente na carteira {$bet['wallet_type']}. Saldo disponível: R$ " . number_format($currentBalance, 2, ',', '.'));
        }

        // 5. Valor mínimo e máximo da aposta
        $minBet = $bet['is_live'] ? $this->settings->live_valor_mini_aposta : $this->settings->valor_mini_aposta;
        $maxBet = $bet['is_live'] ? $this->settings->live_valor_max_aposta  : $this->settings->valor_max_aposta;
        if ($bet['amount'] < (float)$minBet) {
            return $this->fail("Valor mínimo de aposta: R$ {$minBet}");
        }
        if ($bet['amount'] > (float)$maxBet) {
            return $this->fail("Valor máximo de aposta: R$ {$maxBet}");
        }

        // 6. Prêmio máximo
        $maxPrize = $bet['is_live'] ? $this->settings->live_premio_max : $this->settings->premio_max;
        if ($bet['potential_payout'] > (float)$maxPrize) {
            return $this->fail("O retorno potencial excede o prêmio máximo de R$ {$maxPrize}");
        }

        // 7. Verificação de Integridade de Odds (Anti-Tampering)
        if (($bet['wallet_type'] ?? '') !== 'loto') {
            $calculatedTotalOdd = 1.0;
            foreach ($bet['selections'] as $sel) {
                $oddSent = (float)($sel['odd'] ?? $sel['cotacao'] ?? 1.0);
                $matchId = $sel['match_id'] ?? $sel['partida'] ?? 0;
                $market  = $sel['market'] ?? $sel['group_opp'] ?? '';
                $label   = $sel['label'] ?? $sel['palpite'] ?? $sel['odd'] ?? '';

                // Verifica contra o banco de dados
                $dbOdd = DB::table('odds')
                    ->where('event_id', $matchId)
                    ->where('market_name', $market)
                    ->where('label', $label)
                    ->value('value');

                // Se for evento manual, checa na tabela manual_events
                if (!$dbOdd && strpos($matchId, 'm') === 0) {
                    $mId = str_replace('m', '', $matchId);
                    $manual = DB::table('manual_events')->where('id', $mId)->first();
                    if ($manual) {
                        if ($label == 'Casa' || $label == '1') $dbOdd = $manual->odd_home;
                        elseif ($label == 'Empate' || $label == 'X') $dbOdd = $manual->odd_draw;
                        elseif ($label == 'Fora' || $label == '2') $dbOdd = $manual->odd_away;
                    }
                }

                if ($dbOdd && abs((float)$dbOdd - $oddSent) > 0.05) { // Tolerância de 0.05 para variações rápidas
                    return $this->fail("A cotação de '{$label}' mudou. Atualize a página.");
                }

                $calculatedTotalOdd *= $oddSent;

                if ($oddSent < (float)$this->settings->bloquear_odd_abaixo) {
                    return $this->fail("Odd {$oddSent} está abaixo do mínimo permitido ({$this->settings->bloquear_odd_abaixo}).");
                }
                if ($oddSent > (float)$this->settings->travar_odd_acima) {
                    return $this->fail("Odd {$oddSent} excede o máximo permitido ({$this->settings->travar_odd_acima}).");
                }
            }

            // Verifica se o payout bate com as odds calculadas
            $expectedPayout = (float)$bet['amount'] * $calculatedTotalOdd;
            if (abs($expectedPayout - (float)$bet['potential_payout']) > 1.0) { // Tolerância de R$ 1,00 para arredondamentos
                 return $this->fail("Erro de cálculo no bilhete. O retorno potencial enviado não condiz com as cotações.");
            }
        }

        // 8. Odd mínima do bilhete (total acumulado)
        $minTotalOdd = $bet['is_live']
            ? $this->settings->live_cotacao_mini_bilhete
            : ($bet['is_multiple'] ? $this->settings->cotacao_mini_bilhete_mult : $this->settings->cotacao_mini_bilhete);
        if ($bet['total_odd'] < (float)$minTotalOdd) {
            return $this->fail("A cotação total do bilhete ({$bet['total_odd']}) está abaixo do mínimo ({$minTotalOdd}).");
        }

        // 9. Quantidade de jogos no bilhete
        $minGames = $bet['is_live'] ? $this->settings->live_quantidade_jogos_mini_bilhete : $this->settings->quantidade_jogos_mini_bilhete;
        $maxGames = $bet['is_live'] ? $this->settings->live_quantidade_jogos_max_bilhete  : $this->settings->quantidade_jogos_max_bilhete;
        $count = count($bet['selections']);
        if ($count < (int)$minGames) {
            return $this->fail("O bilhete precisa ter no mínimo {$minGames} jogo(s).");
        }
        if ($count > (int)$maxGames) {
            return $this->fail("O bilhete pode ter no máximo {$maxGames} jogo(s).");
        }

        // 10. Limite de apostas iguais (evitar abuso com o mesmo bilhete)
        if ((int)$this->settings->limite_apostas_iguais > 0) {
            $dupes = $this->countDuplicateBets($bet);
            if ($dupes >= (int)$this->settings->limite_apostas_iguais) {
                return $this->fail('Limite de apostas idênticas atingido neste jogo.');
            }
        }

        // 11. Alerta de aposta alta (não bloqueia, apenas registra)
        $alert = (float)$this->settings->alerta_aposta_acima;
        if ($alert > 0 && $bet['amount'] >= $alert) {
            $this->fireHighBetAlert($bet);
        }

        return $this->ok();
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS INTERNOS                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Carrega configurações do site_settings com mapeamento correto de colunas.
     * Fallback para configuracaos (legado) se site_settings estiver incompleto.
     *
     * CORREÇÃO CRÍTICA: site_settings usa nomes distintos:
     *   min_bet_amount = valor_mini_aposta
     *   max_bet_amount = valor_max_aposta
     *   max_payout     = premio_max
     */
    private function loadSettings(): object
    {
        // Valores padrão seguros
        $defaults = [
            'aposta_ativa'                       => true,
            'bloq_aposta_madrugada'              => true,
            'data_limite_jogos'                  => '2050-12-31',
            'hours_limit_date'                   => '23:59:59',
            'valor_mini_aposta'                  => 2.00,
            'valor_max_aposta'                   => 1000.00,
            'premio_max'                         => 50000.00,
            'live_valor_mini_aposta'             => 2.00,
            'live_valor_max_aposta'              => 500.00,
            'live_premio_max'                    => 10000.00,
            'bloquear_odd_abaixo'                => 1.00,
            'travar_odd_acima'                   => 1000.00,
            'cotacao_mini_bilhete'               => 1.01,
            'cotacao_mini_bilhete_mult'          => 1.40,
            'live_cotacao_mini_bilhete'          => 2.00,
            'quantidade_jogos_mini_bilhete'      => 1,
            'quantidade_jogos_max_bilhete'       => 25,
            'live_quantidade_jogos_mini_bilhete' => 1,
            'live_quantidade_jogos_max_bilhete'  => 15,
            'limite_apostas_iguais'              => 0,
            'alerta_aposta_acima'                => 500.00,
            // Sport toggles
            'op_futebol'      => true,
            'op_basquete'     => true,
            'op_volei'        => true,
            'op_tenis'        => true,
            'op_ufcbox'       => true,
            'op_e_sports'     => true,
        ];

        // 1. Tentar carregar de site_settings (tabela nova)
        $ss = DB::table('site_settings')->where('site_id', $this->siteId)->first();

        if ($ss) {
            // Mapeamento de colunas: nome_no_banco => chave_interna
            $columnMap = [
                'min_bet_amount'                     => 'valor_mini_aposta',
                'max_bet_amount'                     => 'valor_max_aposta',
                'max_payout'                         => 'premio_max',
                'aposta_ativa'                       => 'aposta_ativa',
                'bloq_aposta_madrugada'              => 'bloq_aposta_madrugada',
                'bloquear_odd_abaixo'                => 'bloquear_odd_abaixo',
                'travar_odd_acima'                   => 'travar_odd_acima',
                'data_limite_jogos'                  => 'data_limite_jogos',
                'hours_limit_date'                   => 'hours_limit_date',
                'cotacao_mini_bilhete_mult'          => 'cotacao_mini_bilhete_mult',
                'live_valor_mini_aposta'             => 'live_valor_mini_aposta',
                'live_valor_max_aposta'              => 'live_valor_max_aposta',
                'live_premio_max'                    => 'live_premio_max',
                'live_cotacao_mini_bilhete'          => 'live_cotacao_mini_bilhete',
                'quantidade_jogos_mini_bilhete'      => 'quantidade_jogos_mini_bilhete',
                'quantidade_jogos_max_bilhete'       => 'quantidade_jogos_max_bilhete',
                'live_quantidade_jogos_mini_bilhete' => 'live_quantidade_jogos_mini_bilhete',
                'live_quantidade_jogos_max_bilhete'  => 'live_quantidade_jogos_max_bilhete',
                'limite_apostas_iguais'              => 'limite_apostas_iguais',
                'alerta_aposta_acima'                => 'alerta_aposta_acima',
            ];

            foreach ($columnMap as $dbCol => $internalKey) {
                if (property_exists($ss, $dbCol) && !is_null($ss->$dbCol)) {
                    $defaults[$internalKey] = $ss->$dbCol;
                }
            }
        }

        // 2. Fallback para configuracaos (legado) — preenche campos não mapeados
        $cfg = DB::table('configuracaos')
            ->whereIn('site_id', [(string)$this->siteId, 'ihub', 'ihub_v2'])
            ->orderBy('id')
            ->first();

        if ($cfg) {
            // cotacao_mini_bilhete só existe no legado
            if (!empty($cfg->cotacao_mini_bilhete)) {
                $defaults['cotacao_mini_bilhete'] = (float)$cfg->cotacao_mini_bilhete;
            }
            // Sobrescreve min/max somente se site_settings não tinha dados válidos
            if (!$ss && !empty($cfg->valor_mini_aposta)) {
                $defaults['valor_mini_aposta'] = (float)$cfg->valor_mini_aposta;
                $defaults['valor_max_aposta']  = (float)$cfg->valor_max_aposta;
                $defaults['premio_max']        = (float)$cfg->premio_max;
            }
            // Sport toggles do configuracaos
            $sportFields = ['op_futebol', 'op_basquete', 'op_volei', 'op_tenis', 'op_ufcbox', 'op_e_sports'];
            foreach ($sportFields as $field) {
                if (property_exists($cfg, $field)) {
                    $defaults[$field] = (bool)$cfg->$field;
                }
            }
        }

        return (object) $defaults;
    }

    private function countDuplicateBets(array $bet): int
    {
        // Conta quantas apostas com os mesmos match_ids existem hoje
        $matchIds = array_column($bet['selections'], 'match_id');
        if (empty($matchIds)) $matchIds = array_column($bet['selections'], 'partida'); // Fallback legado
        
        sort($matchIds);
        $signature = md5(implode(',', $matchIds));

        // Checa em ambas as tabelas (Legado e Novo)
        $countBets = DB::table('bets')
            ->where('site_id', $this->siteId)
            ->whereDate('created_at', today())
            ->where('ticket_signature', $signature)
            ->count();

        $countApostas = DB::table('apostas')
            ->where('site_id', $this->siteId)
            ->whereDate('created_at', today())
            ->where('codigo_bilhete', 'like', $signature . '%') // Fallback se não tiver signature column
            ->count();

        return $countBets + $countApostas;
    }

    private function fireHighBetAlert(array $bet): void
    {
        try {
            DB::table('notifications')->insert([
                'site_id'    => $this->siteId,
                'user_id'    => null,
                'title'      => 'Aposta Alta Detectada',
                'message'    => "Aposta de R$ {$bet['amount']} registrada. Revisar risco.",
                'type'       => 'warning',
                'is_read'    => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Enviar email de alerta
            $emailAlerta = DB::table('configuracaos')
                ->where('site_id', $this->siteId)
                ->value('email_alerta');

            if ($emailAlerta && isset($bet['id'])) {
                $aposta = \App\Models\Bet::find($bet['id']);
                if ($aposta) {
                    \App\Jobs\sendAlertaBet::dispatch($aposta, $emailAlerta);
                }
            }
        } catch (\Throwable $e) {
            // Não deixar falha no alerta quebrar a aposta
        }
    }

    private function ok(): array
    {
        return ['ok' => true];
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }
}
