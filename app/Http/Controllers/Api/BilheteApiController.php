<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\User;
use App\Models\Configuracao;
use App\Models\PreBet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\BetValidator;
use App\Models\Aposta;
use App\Services\UnifiedBetService;


/**
 * 🎰 BilheteApiController — Motor de Apostas Completo
 * 
 * Restaurado do ORIGINAL_SOURCE (6085 linhas) adaptado para v2.1
 * Funções: sendAposta, sendApostaLive, validaCod, printBilhete,
 *          cancelaBilhete, bilhetes, searchBilhete, relatorio
 */
class BilheteApiController extends Controller
{
    private $arr = [];
    private UnifiedBetService $unifiedBet;

    public function __construct(UnifiedBetService $unifiedBet)
    {
        $this->unifiedBet = $unifiedBet;
    }

    /**
     * 👤 Dados do cambista logado (saldos e totais)
     */
    public function dadosLogado()
    {
        $user = User::find(auth()->user()->id);
        $siteId = config('tenant.site_id', 1);

        $betsAberto = \App\Models\Aposta::where('status', 'Aberto')
                        ->where('user_id', $user->id)
                        ->where('site_id', $siteId)
                        ->sum('valor_apostado');

        return response()->json([
            'saldo_casadinha' => ($user->saldo_casadinha ?? 0) - ($user->entrada_casadinha ?? 0),
            'saldo_simples'   => ($user->saldo_simples ?? 0) - ($user->entrada_simples ?? 0),
            'saldo_loto'      => ($user->saldo_loto ?? 0) - ($user->entrada_loto ?? 0),
            'quantidade'      => $user->quantidade_aposta ?? 0,
            'entradas'        => ($user->entradas ?? 0) + ($user->entrada_loto ?? 0),
            'entradas_abertas'=> $betsAberto,
            'saidas'          => $user->saidas ?? 0,
            'comissoes'       => $user->comissoes ?? 0,
            'lancamentos'     => $user->lancamentos ?? 0,
            'total'           => ($user->entradas ?? 0) + ($user->entrada_loto ?? 0) + ($user->lancamentos ?? 0) - (($user->saidas ?? 0) + ($user->comissoes ?? 0)),
        ]);
    }

    /**
     * 📊 Relatório do cambista por período
     */
    public function relatorio(Request $request)
    {
        $userId = auth()->user()->id;
        $date1 = ($request->date1 ?? now()->format('Y-m-d')) . ' 00:00:00';
        $date2 = ($request->date2 ?? now()->format('Y-m-d')) . ' 23:59:59';

        // 📊 1. Query na tabela MODERN (bets)
        $betsModern = DB::table('bets')
            ->select(
                DB::raw('sum(amount) as total_apostado'),
                DB::raw('sum(commission_amount) as comissoes'),
                DB::raw('count(id) as quantidade'),
                DB::raw('sum(case when status = "won" then potential_payout else 0 end) as total_premios')
            )
            ->where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('cambista_id', $userId);
            })
            ->where('created_at', '>=', $date1)
            ->where('created_at', '<=', $date2)
            ->where('status', '!=', 'cancelled')
            ->first();

        // 📊 2. Query na tabela LEGACY (apostas)
        $betsLegacy = DB::table('apostas')
            ->select(
                DB::raw('sum(valor_apostado) as total_apostado'),
                DB::raw('sum(comicao) as comissoes'),
                DB::raw('count(id) as quantidade'),
                DB::raw('sum(case when status = "won" or status = "Ganhou" then retorno_possivel else 0 end) as total_premios')
            )
            ->where('user_id', $userId)
            ->where('created_at', '>=', $date1)
            ->where('created_at', '<=', $date2)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'Cancelado')
            ->first();

        // 🧮 3. Consolidação (Unificação)
        $totalEntradas  = ($betsModern->total_apostado ?? 0) + ($betsLegacy->total_apostado ?? 0);
        $totalComissoes = ($betsModern->comissoes ?? 0) + ($betsLegacy->comissoes ?? 0);
        $totalPremios   = ($betsModern->total_premios ?? 0) + ($betsLegacy->total_premios ?? 0);
        $totalQuantidade = ($betsModern->quantidade ?? 0) + ($betsLegacy->quantidade ?? 0);
        
        $saldoFinal = $totalEntradas - ($totalComissoes + $totalPremios);

        $resultado = [
            [
                'saidas'           => round($totalPremios, 2),
                'entradas'         => round($totalEntradas, 2),
                'quantidade'       => $totalQuantidade,
                'comissaocambista' => round($totalComissoes, 2),
                'saldo'            => round($saldoFinal, 2),
            ]
        ];

        return response()->json($resultado);
    }

    /**
     * 🖨️ Print bilhete por ID
     */
    public function printBilheteId($id)
    {
        $siteId = config('tenant.site_id', 1);
        $bet = \App\Models\Aposta::with('palpites')->where('id', $id)->where('site_id', $siteId)->first();

        if (!$bet) {
            // 🚀 FALLBACK: Busca em PreBet (PIN) se não achar em Aposta
            $preBet = \App\Models\PreBet::where('id', $id)->where('site_id', $siteId)->first();
            if ($preBet) {
                return response()->json([$this->formatPreBet($preBet)]);
            }
            return response()->json(['message' => 'Bilhete não encontrado'], 404);
        }

        return response()->json([$this->formatBilhete($bet)]);
    }

    /**
     * 🖨️ Print bilhete por cupom (POST)
     */
    public function printBilheteCod(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $bet = \App\Models\Aposta::with('palpites')
                  ->where('codigo_bilhete', $request->cupom)
                  ->where('site_id', $siteId)
                  ->first();

        if (!$bet) {
            // 🚀 FALLBACK: Busca em PreBet (PIN)
            $preBet = \App\Models\PreBet::where('code', strtoupper($request->cupom))->where('site_id', $siteId)->first();
            if ($preBet) {
                return response()->json([$this->formatPreBet($preBet)]);
            }
            return response()->json(['message' => 'Bilhete não encontrado'], 404);
        }

        return response()->json([$this->formatBilhete($bet)]);
    }

    /**
     * 🖨️ Print bilhete GET cod (cambista valida antes de imprimir)
     */
    public function printBilheteGetCod(Request $request)
    {
        return $this->printBilheteCod($request);
    }

    /**
     * 🖨️ Print bilhete GET cod site (cliente consulta)
     */
    public function printBilheteGetCodSite(Request $request)
    {
        return $this->printBilheteCod($request);
    }

    /**
     * ✅ Valida código do pré-bilhete e converte em aposta real
     */
    public function validaCod(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $user = auth()->user();

        // 🔍 Busca pelo Código (PIN) em vez de ID, ou ID se fornecido
        $query = \App\Models\PreBet::where('site_id', $siteId);
        
        if ($request->codigo) {
            $query->where('code', strtoupper($request->codigo));
        } else {
            $query->where('id', $request->aposta_id);
        }

        $preBet = $query->first();

        if (!$preBet) {
            return response()->json(['success' => false, 'message' => 'PIN não encontrado ou já validado.'], 404);
        }

        // ⏳ EXPIRAÇÃO: PINs expiram em 300 minutos (5 horas)
        if ($preBet->created_at->diffInMinutes(now()) > 300) {
            $preBet->delete(); // Remove o PIN expirado
            return response()->json(['success' => false, 'message' => 'Este PIN expirou (limite de 300 minutos).'], 422);
        }

        // Formata os palpites para exibição/processamento
        $selections = is_array($preBet->selections) ? $preBet->selections : json_decode($preBet->selections, true);
        $totalPalpites = count($selections);
        $valorApostado = $preBet->total_stake;

        // 👁️ MODO APENAS VISUALIZAÇÃO (Preview antes de validar)
        if ($request->check_only) {
            return response()->json([
                'success' => true,
                'bilhete' => [
                    'id'               => $preBet->id,
                    'codigo_bilhete'   => $preBet->code,
                    'cliente'          => $preBet->client_name ?? 'Cliente',
                    'valor_apostado'   => $valorApostado,
                    'retorno_possivel' => $preBet->possible_return,
                    'cotacao'          => $preBet->total_stake > 0 ? ($preBet->possible_return / $preBet->total_stake) : 1,
                    'created_at'       => $preBet->created_at,
                    'palpites'         => array_map(function($p) {
                        return [
                            'home_team'      => $p['home'] ?? 'Casa',
                            'away_team'      => $p['away'] ?? 'Fora',
                            'market_name'    => $p['group_opp'] ?? 'Mercado',
                            'selection_name' => $p['palpite'] ?? 'Seleção',
                            'odd'            => $p['cotacao'] ?? 1.0,
                        ];
                    }, $selections)
                ]
            ]);
        }

        // Detecta se é Loto (palpites são números/strings simples)
        $isLoto = false;
        if (count($selections) > 0) {
            $first = $selections[0];
            if (!is_array($first) || (!isset($first['home']) && !isset($first['idEvent']))) {
                $isLoto = true;
            }
        }

        // 🛡️ BLINDAGEM: Valida regras e saldo antes de prosseguir
        $validator = new \App\Services\BetValidator($siteId);
        $check = $validator->validate([
            'amount'           => $valorApostado,
            'potential_payout' => $preBet->possible_return,
            'total_odd'        => $preBet->total_stake > 0 ? ($preBet->possible_return / $preBet->total_stake) : 1,
            'selections'       => $selections,
            'user_id'          => $user->id,
            'is_live'          => false,
            'is_multiple'      => $totalPalpites > 1,
            'wallet_type'      => $isLoto ? 'loto' : ($totalPalpites > 1 ? 'casadinha' : 'simples')
        ]);

        if (!$check['ok']) {
            return response()->json(['success' => false, 'message' => $check['message']], 422);
        }

        // Calcula comissão baseado no total de palpites
        $comissao = $this->calcularComissao($valorApostado, $totalPalpites, $user);


        // Gera cupom definitivo (Bilhete Real)
        $cupom = strtoupper(\Illuminate\Support\Str::random(8));

        DB::beginTransaction();
        try {
            $bet = \App\Models\Aposta::create([
                'site_id'          => $siteId,
                'user_id'          => $user->id,
                'gerente_id'       => $user->gerente_id ?? 0,
                'valor_apostado'   => $valorApostado,
                'retorno_possivel' => $preBet->possible_return,
                'comicao'          => $comissao,
                'status'           => 'Aberto',
                'codigo_bilhete'   => $cupom,
                'modalidade'       => $isLoto ? 'Loto' : 'Esporte',
                'tipo'             => $isLoto ? ($preBet->tipo ?? 'Quininha') : ($totalPalpites > 1 ? 'Multipla' : 'Simples'),
                'total_palpites'   => $totalPalpites,
                'vendedor'         => $user->name,
                'cliente'          => $preBet->client_name ?? 'Cliente',
                'cotacao'          => $preBet->total_stake > 0 ? ($preBet->possible_return / $preBet->total_stake) : 1,
                'andamento_palpites' => $totalPalpites,
                'concurso'         => $preBet->concurso,
            ]);

            // Atualiza contadores e DESCONTA SALDO do cambista
            $cambista = User::find($user->id);
            $cambista->quantidade_aposta = ($cambista->quantidade_aposta ?? 0) + 1;
            $cambista->entradas = ($cambista->entradas ?? 0) + $valorApostado;
            $cambista->comissoes = ($cambista->comissoes ?? 0) + $comissao;

            if ($isLoto) {
                $cambista->entrada_loto = ($cambista->entrada_loto ?? 0) + $valorApostado;
            } else if ($totalPalpites == 1) {
                $cambista->entrada_simples = ($cambista->entrada_simples ?? 0) + $valorApostado;
            } else {
                $cambista->entrada_casadinha = ($cambista->entrada_casadinha ?? 0) + $valorApostado;
            }
            $cambista->save();

            // Atualiza contadores do gerente
            $gerente = User::find($user->gerente_id ?? $user->manager_id);
            if ($gerente) {
                $gerente->quantidade_aposta = ($gerente->quantidade_aposta ?? 0) + 1;
                $gerente->entradas = ($gerente->entradas ?? 0) + $valorApostado;
                $gerente->save();
            }

            // Itens da aposta
            foreach ($selections as $palpite) {
                \App\Models\Palpite::create([
                    'aposta_id'       => $bet->id,
                    'match_id'        => (int) str_replace('m', '', ($palpite['idEvent'] ?? $palpite['partida'] ?? 0)),
                    'home_team'       => $palpite['home'] ?? 'Casa',
                    'away_team'       => $palpite['away'] ?? 'Fora',
                    'market_name'     => $palpite['group_opp'] ?? '1x2',
                    'selection_label' => $palpite['palpite'] ?? '',
                    'selection_odd'   => $palpite['cotacao'] ?? 1.0,
                    'status'          => 'Aberto'
                ]);
            }

            // Deleta o PreBet
            $preBet->delete();

            // Log
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => 'bet_placed',
                'amount'     => $valorApostado,
                'gateway_ref'=> "bet_{$bet->id}",
                'status'     => 'completed',
                'description'=> "Aposta Validada (PIN: {$preBet->code} -> Bilhete: {$cupom})",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // 🔄 Espelha para tabela moderna (bets/bet_items) para liquidação automática
            $this->unifiedBet->createFromAposta($bet);

            return response()->json([
                'success' => true,
                'message' => 'Bilhete validado com sucesso!',
                'bilhete' => [
                    'id' => $bet->id,
                    'codigo_bilhete' => $bet->codigo_bilhete,
                    'cupom' => $bet->codigo_bilhete
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('validaCod error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao validar PIN.'], 500);
        }
    }

    /**
     * 🎯 Enviar aposta (cambista autenticado - pré-jogo)
     */
    public function sendAposta(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        // Normalização de dados (Suporta múltiplos formatos de entrada)
        $valorApostado   = $request->valor_apostado ?? $request->amount;
        $retornoPossivel = $request->retorno_possivel ?? $request->potential_return ?? $request->potential_payout;
        $palpites        = $request->palpites ?? $request->selections;

        if (!$valorApostado || !$palpites) {
            return response()->json(['message' => 'Dados de aposta incompletos.'], 422);
        }

        // Gera cupom
        $totalPalpites = $request->total_palpites ?? count($palpites);
        $cupom = strtoupper(Str::random(7));

        DB::beginTransaction();
        try {
            // 🛡️ LOCK FOR UPDATE: Evita Race Conditions (Double Spend)
            $user = User::where('id', auth()->user()->id)->lockForUpdate()->first();

            if (in_array($user->nivel ?? '', ['admin', 'adm']) || in_array($user->role ?? '', ['admin', 'adm'])) {
                DB::rollBack();
                return response()->json(['message' => 'Administradores não podem realizar apostas.'], 403);
            }

            if (!$user || ($user->situacao ?? '') !== 'ativo') {
                DB::rollBack();
                return response()->json(['message' => 'Usuário inativo ou não encontrado'], 403);
            }

            // 🛡️ BLINDAGEM: Valida regras, saldo e INTEGRIDADE de ODDS
            $validator = new BetValidator($siteId);
            $check = $validator->validate([
                'amount'           => $valorApostado,
                'potential_payout' => $retornoPossivel,
                'total_odd'        => $valorApostado > 0 ? ($retornoPossivel / $valorApostado) : 1,
                'selections'       => $palpites,
                'user_id'          => $user->id,
                'is_live'          => false,
                'is_multiple'      => $totalPalpites > 1,
                'wallet_type'      => $totalPalpites > 1 ? 'casadinha' : 'simples'
            ], $user); // Passa o usuário já travado no banco

            if (!$check['ok']) {
                DB::rollBack();
                return response()->json(['message' => $check['message']], 422);
            }

            // Calcula comissão
            $comissao = $this->calcularComissao($valorApostado, $totalPalpites, $user);

            $bet = \App\Models\Aposta::create([
                'site_id'          => $siteId,
                'user_id'          => $user->id,
                'gerente_id'       => $user->gerente_id ?? 0,
                'valor_apostado'   => $valorApostado,
                'retorno_possivel' => $retornoPossivel,
                'comicao'          => $comissao,
                'status'           => 'Aberto',
                'codigo_bilhete'   => $cupom,
                'modalidade'       => 'Esporte',
                'tipo'             => $totalPalpites > 1 ? 'Multipla' : 'Simples',
                'total_palpites'   => $totalPalpites,
                'vendedor'         => $user->name,
                'cliente'          => $request->cliente ?? 'Cliente',
                'cotacao'          => $valorApostado > 0 ? ($retornoPossivel / $valorApostado) : 1,
                'andamento_palpites' => $totalPalpites,
            ]);

            // Atualiza cambista e DESCONTA SALDO (Atômico)
            $user->quantidade_aposta += 1;
            $user->entradas += $valorApostado;
            $user->comissoes += $comissao;

            if ($totalPalpites == 1) {
                $user->entrada_simples += $valorApostado;
                $user->balance -= $valorApostado;
            } else {
                $user->entrada_casadinha += $valorApostado;
                $user->balance_bonus -= $valorApostado;
            }
            $user->save();

            // 🎁 ABATIMENTO DE ROLLOVER
            if ($user->nivel == 'cliente' && $totalPalpites > 1) {
                $this->updateRollover($user->id, $valorApostado, $valorApostado > 0 ? ($retornoPossivel / $valorApostado) : 1);
            }

            // Atualiza gerente (Lock para evitar race condition nas comissões)
            if ($user->gerente_id) {
                $gerente = User::where('id', $user->gerente_id)->lockForUpdate()->first();
                if ($gerente) {
                    $gerente->quantidade_aposta += 1;
                    $gerente->entradas += $valorApostado;
                    $gerente->comissoes += $comissao;
                    $gerente->save();
                }
            }

            // Insere os itens da aposta para o ResultProcessor
            foreach ($palpites as $palpite) {
                \App\Models\Palpite::create([
                    'aposta_id'       => $bet->id,
                    'match_id'        => (int) str_replace('m', '', ($palpite['idEvent'] ?? $palpite['partida'] ?? 0)),
                    'home_team'       => $palpite['home'] ?? 'Casa',
                    'away_team'       => $palpite['away'] ?? 'Fora',
                    'market_name'     => $palpite['group_opp'] ?? '1x2',
                    'selection_label' => $palpite['palpite'] ?? $palpite['odd'] ?? '',
                    'selection_odd'   => $palpite['cotacao'] ?? 1.0,
                    'status'          => 'Aberto'
                ]);

                // MapaBet tracking para analytics
                \App\Models\MapaBet::create([
                    'event_id'    => $palpite['idEvent'] ?? $palpite['partida'] ?? 0,
                    'confronto'   => ($palpite['home'] ?? '') . ' vs ' . ($palpite['away'] ?? ''),
                    'date_event'  => now(),
                    'sport'       => 'Futebol',
                    'group_opp'   => $palpite['group_opp'] ?? '1x2',
                    'apostado'    => $valorApostado / max(1, $totalPalpites),
                    'opcao'       => $palpite['palpite'] ?? $palpite['odd'] ?? '',
                    'tipo_aposta' => 'Simples',
                    'site_id'     => $siteId,
                ]);
            }

            // LOG DE TRANSAÇÃO: APOSTA DIRETA
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => 'bet_placed',
                'amount'     => $request->valor_apostado,
                'gateway_ref'=> "bet_{$bet->id}",
                'status'     => 'completed',
                'description'=> "Aposta Direta (Bilhete: {$cupom})",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // 🔄 Espelha para tabela moderna (bets/bet_items) para liquidação automática
            $this->unifiedBet->createFromAposta($bet);

            return response()->json($this->formatBilhete($bet));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('sendAposta error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao salvar aposta.'], 500);
        }
    }

    /**
     * 🎯 Enviar aposta ao vivo
     */
    public function sendApostaLive(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $valorApostado   = $request->valor_apostado ?? $request->amount;
        $retornoPossivel = $request->retorno_possivel ?? $request->potential_return ?? $request->potential_payout;
        $palpites        = $request->palpites ?? $request->selections;

        if (!$valorApostado || !$palpites) {
            return response()->json(['message' => 'Dados de aposta incompletos.'], 422);
        }

        $totalPalpites = $request->total_palpites ?? count($palpites);
        $cupom = strtoupper(Str::random(7));

        DB::beginTransaction();
        try {
            $user = User::where('id', auth()->user()->id)->lockForUpdate()->first();

            if (in_array($user->nivel ?? '', ['admin', 'adm']) || in_array($user->role ?? '', ['admin', 'adm'])) {
                DB::rollBack();
                return response()->json(['message' => 'Administradores não podem realizar apostas.'], 403);
            }

            if (!$user || ($user->situacao ?? '') !== 'ativo') {
                DB::rollBack();
                return response()->json(['message' => 'Usuário inativo ou não encontrado'], 403);
            }

            // Validação live - odds podem mudar
            $validator = new BetValidator($siteId);
            $check = $validator->validate([
                'amount'           => $valorApostado,
                'potential_payout' => $retornoPossivel,
                'total_odd'        => $valorApostado > 0 ? ($retornoPossivel / $valorApostado) : 1,
                'selections'       => $palpites,
                'user_id'          => $user->id,
                'is_live'          => true,
                'is_multiple'      => $totalPalpites > 1,
                'wallet_type'      => $totalPalpites > 1 ? 'casadinha' : 'simples'
            ], $user);

            if (!$check['ok']) {
                DB::rollBack();
                return response()->json(['message' => $check['message']], 422);
            }

            $comissao = $this->calcularComissao($valorApostado, $totalPalpites, $user);

            $bet = Aposta::create([
                'site_id'          => $siteId,
                'user_id'          => $user->id,
                'gerente_id'       => $user->gerente_id ?? 0,
                'valor_apostado'   => $valorApostado,
                'retorno_possivel' => $retornoPossivel,
                'comicao'          => $comissao,
                'status'           => 'Aberto',
                'codigo_bilhete'   => $cupom,
                'modalidade'       => 'Esporte',
                'tipo'             => $totalPalpites > 1 ? 'Multipla' : 'Simples',
                'total_palpites'   => $totalPalpites,
                'vendedor'         => $user->name,
                'cliente'          => $request->cliente ?? 'Cliente',
                'cotacao'          => $valorApostado > 0 ? ($retornoPossivel / $valorApostado) : 1,
                'andamento_palpites' => $totalPalpites,
            ]);

            $user->quantidade_aposta += 1;
            $user->entradas += $valorApostado;
            $user->comissoes += $comissao;

            if ($totalPalpites == 1) {
                $user->entrada_simples += $valorApostado;
                $user->balance -= $valorApostado;
            } else {
                $user->entrada_casadinha += $valorApostado;
                $user->balance_bonus -= $valorApostado;
            }
            $user->save();

            if ($user->nivel == 'cliente' && $totalPalpites > 1) {
                $this->updateRollover($user->id, $valorApostado, $valorApostado > 0 ? ($retornoPossivel / $valorApostado) : 1);
            }

            if ($user->gerente_id) {
                $gerente = User::where('id', $user->gerente_id)->lockForUpdate()->first();
                if ($gerente) {
                    $gerente->quantidade_aposta += 1;
                    $gerente->entradas += $valorApostado;
                    $gerente->comissoes += $comissao;
                    $gerente->save();
                }
            }

            foreach ($palpites as $palpite) {
                \App\Models\Palpite::create([
                    'aposta_id'       => $bet->id,
                    'match_id'        => (int) str_replace('m', '', ($palpite['idEvent'] ?? $palpite['partida'] ?? 0)),
                    'home_team'       => $palpite['home'] ?? 'Casa',
                    'away_team'       => $palpite['away'] ?? 'Fora',
                    'market_name'     => $palpite['group_opp'] ?? '1x2',
                    'selection_label' => $palpite['palpite'] ?? $palpite['odd'] ?? '',
                    'selection_odd'   => $palpite['cotacao'] ?? 1.0,
                    'status'          => 'Aberto'
                ]);

                // MapaBet tracking para apostas live
                \App\Models\MapaBet::create([
                    'event_id'    => $palpite['idEvent'] ?? $palpite['partida'] ?? 0,
                    'confronto'   => ($palpite['home'] ?? '') . ' vs ' . ($palpite['away'] ?? ''),
                    'date_event'  => now(),
                    'sport'       => 'Futebol',
                    'group_opp'   => $palpite['group_opp'] ?? '1x2',
                    'apostado'    => $valorApostado / max(1, $totalPalpites),
                    'opcao'       => $palpite['palpite'] ?? $palpite['odd'] ?? '',
                    'tipo_aposta' => 'Live',
                    'site_id'     => $siteId,
                ]);
            }

            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => 'bet_placed_live',
                'amount'     => $valorApostado,
                'gateway_ref'=> "bet_{$bet->id}_live",
                'status'     => 'completed',
                'description'=> "Aposta Ao Vivo (Bilhete: {$cupom})",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // 🔄 Espelha para tabela moderna (bets/bet_items) para liquidação automática
            $this->unifiedBet->createFromAposta($bet);

            return response()->json($this->formatBilhete($bet));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('sendApostaLive error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao salvar aposta live.'], 500);
        }
    }

    /**
     * 🎯 Enviar aposta ao vivo (App)
     */
    public function sendApostaLiveApp(Request $request)
    {
        return $this->sendApostaLive($request);
    }

    /**
     * 🎯 Enviar aposta site (público sem auth)
     */
    public function sendApostaSite(Request $request)
    {
        return (new PublicBetController)->store($request);
    }

    /**
     * 🎯 Enviar pré-aposta (site)
     */
    public function sendPreAposta(Request $request)
    {
        return (new PublicBetController)->store($request);
    }

    /**
     * ✅ Validar live (retorna odds atualizadas e verifica se ainda estão válidas)
     */
    public function validLive(Request $request)
    {
        $palpites = $request->palpites ?? [];
        if (empty($palpites)) {
            return response()->json(['valid' => false, 'message' => 'Nenhum palpite fornecido'], 422);
        }

        $siteId = config('tenant.site_id', 1);
        $validated = [];
        $changes = [];

        foreach ($palpites as $palpite) {
            $matchId = (int) str_replace('m', '', $palpite['idEvent'] ?? $palpite['partida'] ?? 0);
            $oddValue = (float) ($palpite['cotacao'] ?? $palpite['odd'] ?? 0);
            $marketName = $palpite['group_opp'] ?? '1x2';
            $selectionLabel = $palpite['palpite'] ?? '';

            // Busca odd atualizada no banco
            $currentOdd = \App\Models\Odd::where('event_id', $matchId)
                ->where('market_name', $marketName)
                ->where('label', $selectionLabel)
                ->where('status', 'active')
                ->first();

            if ($currentOdd) {
                $newOdd = (float) $currentOdd->value;
                $palpite['cotacao'] = $newOdd;
                $palpite['odd'] = $newOdd;

                // Detectou mudança de odd
                if ($oddValue > 0 && abs($newOdd - $oddValue) > 0.01) {
                    $changes[] = [
                        'match_id' => $matchId,
                        'market' => $marketName,
                        'selection' => $selectionLabel,
                        'old_odd' => $oddValue,
                        'new_odd' => $newOdd,
                    ];
                }
            }

            $validated[] = $palpite;
        }

        return response()->json([
            'valid' => true,
            'palpites' => $validated,
            'changes' => $changes,
            'has_changes' => count($changes) > 0,
        ]);
    }

    /**
     * ✅ Validar live App
     */
    public function validLiveApp(Request $request)
    {
        return $this->validLive($request);
    }

    /**
     * 🔍 Consulta pública de bilhete/pré-bilhete pelo código (cupom)
     */
    public function checkAposta($code)
    {
        $siteId = config('tenant.site_id', 1);

        // Tenta encontrar como aposta real
        $bet = Aposta::with('palpites')
            ->where('site_id', $siteId)
            ->where('codigo_bilhete', $code)
            ->first();

        if ($bet) {
            return response()->json([
                'status' => 'ok',
                'tipo'   => 'aposta',
                'data'   => $this->formatBilhete($bet),
            ]);
        }

        // Tenta encontrar como pré-bilhete (PIN)
        $preBet = PreBet::where('site_id', $siteId)->where('code', $code)->first();
        if ($preBet) {
            return response()->json([
                'status' => 'ok',
                'tipo'   => 'pre_bet',
                'data'   => $this->formatPreBet($preBet),
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Bilhete não encontrado',
        ], 404);
    }

    /**
     * 📋 Lista bilhetes do cambista
     */
    public function bilhetes()
    {
        $userId = auth()->user()->id;
        $siteId = config('tenant.site_id', 1);

        return \App\Models\Aposta::with('palpites')
                  ->where('user_id', $userId)
                  ->where('site_id', $siteId)
                  ->orderBy('id', 'desc')
                  ->limit(100)
                  ->get()
                  ->map(fn($b) => $this->formatBilhete($b));
    }

    /**
     * 🔍 Busca bilhete
     */
    public function searchBilhete(Request $request)
    {
        $userId = auth()->user()->id;

        return \App\Models\Aposta::with('palpites')
                  ->where('user_id', $userId)
                  ->where(function($q) use ($request) {
                      $q->where('codigo_bilhete', 'LIKE', "%{$request->search}%")
                        ->orWhere('id', $request->search);
                  })
                  ->orderBy('id', 'desc')
                  ->get()
                  ->map(fn($b) => $this->formatBilhete($b));
    }

    /**
     * ❌ Cancela bilhete
     */
    public function cancelaBilhete($id)
    {
        $siteId = config('tenant.site_id', 1);
        $userId = auth()->user()->id;

        $bet = \App\Models\Aposta::where('id', $id)
                  ->where('site_id', $siteId)
                  ->where('status', 'Aberto')
                  ->first();

        if (!$bet) {
            return response()->json(['message' => 'Bilhete não pode ser cancelado'], 404);
        }

        // Verifica permissão
        $conf = Configuracao::where('site_id', $siteId)->first();
        $user = auth()->user();
        $nivel = $user->nivel ?? $user->role ?? 'cambista';

        $canCancel = false;
        if ($nivel === 'adm' || $nivel === 'admin') $canCancel = true;
        if (($nivel === 'gerente' || $nivel === 'manager') && ($conf->manager_can_cancel ?? false)) $canCancel = true;
        if (($nivel === 'cambista' || $nivel === 'seller') && ($conf->sellers_can_cancel ?? false)) $canCancel = true;

        if (!$canCancel) {
            return response()->json(['message' => 'Sem permissão para cancelar'], 403);
        }

        DB::beginTransaction();
        try {
            $bet->status = 'Cancelado';
            $bet->save();

            // Reverte contadores do cambista
            $cambista = User::find($bet->user_id);
            if ($cambista) {
                $cambista->entradas = max(0, ($cambista->entradas ?? 0) - $bet->valor_apostado);
                $cambista->quantidade_aposta = max(0, ($cambista->quantidade_aposta ?? 0) - 1);
                $cambista->comissoes = max(0, ($cambista->comissoes ?? 0) - ($bet->comicao ?? 0));
                
                if ($bet->modalidade == 'Loto') {
                    $cambista->entrada_loto = max(0, ($cambista->entrada_loto ?? 0) - $bet->valor_apostado);
                    $cambista->saldo_loto += $bet->valor_apostado;
                } else {
                    if ($bet->total_palpites == 1) {
                        $cambista->entrada_simples = max(0, ($cambista->entrada_simples ?? 0) - $bet->valor_apostado);
                        $cambista->balance += $bet->valor_apostado;
                    } else {
                        $cambista->entrada_casadinha = max(0, ($cambista->entrada_casadinha ?? 0) - $bet->valor_apostado);
                        $cambista->balance_bonus += $bet->valor_apostado;
                    }
                }
                $cambista->save();
            }

            // Reverte gerente
            $gerente = User::find($bet->gerente_id);
            if ($gerente) {
                $gerente->entradas = max(0, ($gerente->entradas ?? 0) - $bet->valor_apostado);
                $gerente->quantidade_aposta = max(0, ($gerente->quantidade_aposta ?? 0) - 1);
                $gerente->comissoes = max(0, ($gerente->comissoes ?? 0) - ($bet->comicao ?? 0));
                $gerente->save();
            }

            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => 'bet_cancelled',
                'amount'     => $bet->valor_apostado,
                'gateway_ref'=> "bet_{$bet->id}",
                'status'     => 'completed',
                'description'=> "Bilhete #{$bet->codigo_bilhete} Cancelado",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // 🔄 Sincroniza cancelamento com tabela moderna
            $this->unifiedBet->cancel($bet);

            return response()->json(['message' => 'Bilhete cancelado com sucesso']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('cancelaBilhete error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao cancelar bilhete.'], 500);
        }
    }

    // ═══════ HELPERS ═══════

    /**
     * Calcula comissão baseado no total de palpites (1-10+)
     */
    private function calcularComissao($valorApostado, $totalPalpites, $user)
    {
        $comissaoField = 'comissao' . min($totalPalpites, 10);
        $percentual = $user->$comissaoField ?? 0;
        return ($valorApostado * $percentual) / 100;
    }

    /**
     * Formata PreBet para o padrão do Bilhete
     */
    private function formatPreBet($preBet)
    {
        $selections = is_array($preBet->selections) ? $preBet->selections : json_decode($preBet->selections, true);
        $isLoto = $preBet->modalidade == 'Loto';
        
        $formattedPalpites = [];
        $lotoPalpites = [];

        if ($isLoto) {
            foreach ($selections as $p) {
                $lotoPalpites[] = [
                    'dezena' => is_array($p) ? ($p['dezena'] ?? $p['num'] ?? $p[0] ?? '') : $p,
                    'status' => 'Aberto'
                ];
            }
        } else {
            foreach ($selections as $p) {
                $sel = (array) $p;
                $formattedPalpites[] = [
                    'home'       => $sel['home'] ?? 'Casa',
                    'away'       => $sel['away'] ?? 'Fora',
                    'league'     => $sel['league'] ?? 'Campeonato',
                    'group_opp'  => $sel['group_opp'] ?? 'Mercado',
                    'palpite'    => $this->mapOutcome($sel['palpite'] ?? $sel['odd'] ?? ''),
                    'cotacao'    => $sel['cotacao'] ?? 1.0,
                    'status'     => 'Aberto',
                    'match_temp' => $sel['match_temp'] ?? $sel['date'] ?? null,
                    'sport'      => $sel['sport'] ?? 'Futebol',
                    'score'      => 'vs'
                ];
            }
        }

        return [
            'id'               => $preBet->id,
            'cupom'            => $preBet->code,
            'status'           => 'Aberto',
            'cliente'          => $preBet->client_name ?? 'Bilhete Site',
            'vendedor'         => 'Site',
            'valor_apostado'   => (float)$preBet->total_stake,
            'retorno_possivel' => (float)$preBet->possible_return,
            'retorno_cambista' => (float)$preBet->possible_return,
            'total_palpites'   => $isLoto ? count($lotoPalpites) : count($formattedPalpites),
            'acertos_palpites' => 0,
            'modalidade'       => $preBet->modalidade ?? 'Esporte',
            'cotacao'          => $preBet->total_stake > 0 ? ($preBet->possible_return / $preBet->total_stake) : 1,
            'palpites'         => $formattedPalpites,
            'palpites_loto'    => $lotoPalpites,
            'created_at'       => $preBet->created_at,
            'tipo'             => $preBet->tipo ?? ($isLoto ? 'Quininha' : (count($formattedPalpites) > 1 ? 'Múltipla' : 'Simples'))
        ];
    }

    /**
     * Formata Bet para o padrão legado esperado pelo frontend
     */
    private function formatBilhete($bet)
    {
        $isLoto = ($bet->modalidade == 'Loto');
        $mappedPalpites = [];
        $lotoPalpites = [];

        if ($isLoto) {
            $palpites = $bet->palpitesLoto()->get();
            foreach ($palpites as $p) {
                $lotoPalpites[] = [
                    'id'     => $p->id,
                    'dezena' => $p->dezena,
                    'status' => $this->mapStatus($p->status)
                ];
            }
        } else {
            $palpites = $bet->palpites()->get();
            foreach ($palpites as $p) {
                $eventId = $p->match_id ?? 0;
                $live = \App\Models\MatchModel::where('event_id', $eventId)->first();
                
                $pData = [
                    'id'              => $p->id,
                    'home'            => $p->home_team ?? 'Casa',
                    'away'            => $p->away_team ?? 'Fora',
                    'home_team'       => $p->home_team ?? 'Casa',
                    'away_team'       => $p->away_team ?? 'Fora',
                    'group_opp'       => $p->market_name ?? 'Mercado',
                    'market_name'     => $p->market_name ?? 'Mercado',
                    'palpite'         => $this->mapOutcome($p->selection_label),
                    'selection_label' => $this->mapOutcome($p->selection_label),
                    'cotacao'         => (float)($p->selection_odd ?? 1.0),
                    'selection_odd'   => (float)($p->selection_odd ?? 1.0),
                    'status'          => $this->mapStatus($p->status),
                    'match_temp'      => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
                    'score'           => $p->score ?? '0x0',
                ];

                if ($live) {
                    $pData['live'] = $live;
                    $pData['score'] = $live->score;
                    $pData['match_temp'] = $live->date;
                }

                $mappedPalpites[] = $pData;
            }
        }

        return [
            'id'               => $bet->id,
            'user_id'          => $bet->user_id,
            'cupom'            => $bet->codigo_bilhete,
            'status'           => $this->mapStatus($bet->status),
            'valor_apostado'   => (float)$bet->valor_apostado,
            'retorno_possivel' => (float)$bet->retorno_possivel,
            'vendedor'         => $bet->user ? $bet->user->name : ($bet->vendedor ?? 'Sistema'),
            'cliente'          => $bet->cliente ?? 'Cliente',
            'tipo'             => $bet->tipo ?? ($isLoto ? 'Quininha' : (count($mappedPalpites) > 1 ? 'Multipla' : 'Simples')),
            'comicao'          => (float)($bet->comicao ?? 0),
            'total_palpites'   => $isLoto ? count($lotoPalpites) : count($mappedPalpites),
            'palpites'         => $mappedPalpites,
            'palpites_loto'    => $lotoPalpites,
            'created_at'       => $bet->created_at,
            'modalidade'       => $bet->modalidade ?? 'Esporte',
        ];
    }

    /**
     * Mapeia status v2.1 → legado
     */
    private function mapStatus($status)
    {
        return match($status) {
            'open', 'pending' => 'Aberto',
            'won'             => 'Ganhou',
            'lost'            => 'Perdeu',
            'cancelled'       => 'Cancelado',
            default           => $status,
        };
    }

    /**
     * Mapeia labels 1, X, 2 → Casa, Empate, Fora
     */
    private function mapOutcome($label)
    {
        $l = strtoupper(trim($label));
        return match($l) {
            '1', 'CASA', 'VITORIA CASA', 'HOME' => 'Casa',
            'X', 'EMPATE', 'DRAW' => 'Empate',
            '2', 'FORA', 'VITORIA FORA', 'AWAY' => 'Fora',
            default => $label
        };
    }

    /**
     * 🎁 Atualiza o progresso de rollover do usuário
     */
    private function updateRollover($userId, $amount, $odd)
    {
        $bonusUser = DB::table('bonus_user')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if ($bonusUser) {
            $promocode = DB::table('promocodes')->where('id', $bonusUser->bonus_id)->first();
            
            // Verifica se a odd da aposta é maior ou igual à odd mínima do cupom
            $minOdd = $promocode->min_odd ?? 1.0;
            
            if ($odd >= $minOdd) {
                DB::table('bonus_user')
                    ->where('id', $bonusUser->id)
                    ->increment('current_rollover', $amount, [
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * 💰 Cash-Out: Resgate antecipado de aposta
     */
    public function cashOutAposta($id)
    {
        $bilhete = Aposta::find($id);

        if (!$bilhete) {
            return response()->json(['message' => 'Aposta não encontrada'], 404);
        }

        if ($bilhete->user_id !== auth()->user()->id) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $confs = Configuracao::where('site_id', config('tenant.site_id', 1))->first();
        if (!$confs || !$confs->cash_out_ativo) {
            return response()->json(['message' => 'Cash-out desativado'], 400);
        }

        if ($bilhete->status !== 'Aberto' && $bilhete->status !== 'Aguardando') {
            return response()->json(['message' => 'Aposta não está pendente'], 400);
        }

        $taxa = $confs->cash_out_taxa ?? 80;
        $returnAmount = ($bilhete->valor_apostado * $taxa) / 100;

        DB::beginTransaction();
        try {
            $bilhete->status = 'Cashout';
            $bilhete->save();

            $palpites = \App\Models\Palpite::where('aposta_id', $id)->get();
            foreach ($palpites as $palp) {
                $palp->status = 'Cancelado';
                $palp->save();
            }

            $user = User::find($bilhete->user_id);
            if ($user) {
                // Subtrai do aporte/entrada (efeito financeiro de resgate antecipado)
                $user->entradas = max(0, ($user->entradas ?? 0) - $returnAmount);

                // Credita o valor do cash-out na carteira correta do usuário
                if ($bilhete->total_palpites > 1) {
                    $user->entrada_casadinha = max(0, ($user->entrada_casadinha ?? 0) - $returnAmount);
                    $user->balance_bonus = ($user->balance_bonus ?? 0) + $returnAmount;
                } else {
                    $user->entrada_simples = max(0, ($user->entrada_simples ?? 0) - $returnAmount);
                    $user->balance = ($user->balance ?? 0) + $returnAmount;
                }

                // Estorna comissão proporcional ao cash-out (não ao valor total)
                $comissaoCashout = ($bilhete->comicao ?? 0) * ($taxa / 100);
                $user->comissoes = max(0, ($user->comissoes ?? 0) - $comissaoCashout);
                $user->save();
            }

            $gerente = User::find($bilhete->gerente_id ?? null);
            if ($gerente) {
                $gerente->entradas = max(0, ($gerente->entradas ?? 0) - $returnAmount);
                $gerente->comissoes = max(0, ($gerente->comissoes ?? 0) - $comissaoCashout);
                $gerente->save();
            }

            // Log de Transação: CASH-OUT
            DB::table('transactions')->insert([
                'site_id'    => config('tenant.site_id', 1),
                'user_id'    => $bilhete->user_id,
                'type'       => 'cashout',
                'amount'     => $returnAmount,
                'gateway_ref'=> "cashout_{$bilhete->id}",
                'status'     => 'completed',
                'description'=> "Cash-Out Aposta #{$bilhete->codigo_bilhete} - Retorno R$ " . number_format($returnAmount, 2, ',', '.'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // 🔄 Sincroniza cash-out com tabela moderna
            $this->unifiedBet->cashOut($bilhete, $returnAmount);

            return response()->json([
                'message' => 'Cash-out realizado com sucesso!',
                'return_amount' => $returnAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('CashOut error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao processar cash-out'], 500);
        }
    }

    // Apostas Loto (Migrado do REI BET)
    public function sendApostaLoto(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $user = \App\Models\User::where('id', auth()->user()->id)->lockForUpdate()->first();

        if ($user && ($user->situacao ?? '') == 'ativo') {
            $hora = date('Hi');

            $conf = \App\Models\Configuracao::where('site_id', $siteId)->first();
            $bloq_aposta_madrugada = $conf->bloq_aposta_madrugada ?? 'Nao';

            if ($bloq_aposta_madrugada == 'Sim' && $hora >= '0100' &&  $hora <= '0559') {
                return response()->json(['message' => 'error'], 404);
            }

            // Gera cupom
            $valorMaximo = 7;
            $varcaracteres = 'ABCDEFGHIJLMNOPRSTUVXZYW0123456789ABCDGHSMHHHK';
            $cupom = strtoupper(Str::random($valorMaximo));

            // Comissão
            $comissao = $request->valor_apostado * ($user->comissao_loto ?? 0) / 100;

            DB::beginTransaction();
            try {
                $aposta = \App\Models\Aposta::create([
                    'user_id'               => $user->id,
                    'adm_id'                => $user->adm_id ?? 1,
                    'gerente_id'            => $user->gerente_id,
                    'site_id'               => $siteId,
                    'modalidade'            => 'Loto',
                    'cupom'                 => $cupom,
                    'status'                => 'Aberto',
                    'concurso'              => $request->concurso,
                    'valor_apostado'        => $request->valor_apostado,
                    'retorno_possivel'      => $request->retorno_possivel,
                    'vendedor'              => $user->name,
                    'cliente'               => $request->cliente,
                    'tipo'                  => $request->tipo,
                    'comicao'               => $comissao,
                    'cotacao'               => $request->cotacao,
                    'total_palpites'        => count($request->palpites ?? []),
                    'andamento_palpites'    => count($request->palpites ?? []),
                    'acertos_palpites'      => 0,
                    'erros_palpites'        => 0,
                    'devolvidos_palpites'   => 0,
                ]);

                foreach ($request->palpites as $palpite) {
                    \App\Models\PalpiteLoto::create([
                        'aposta_id'     => $aposta->id,
                        'tipo'          => $request->tipo,
                        'dezena'        => $palpite['num'] ?? $palpite,
                        'status'        => 'Aberto',
                        'concurso'      => $request->concurso,
                    ]);
                }

                // Atualizações Usuário (desconta saldo)
                if (in_array($user->role ?? '', ['cliente', 'client'])) {
                    if ($user->saldo >= $request->valor_apostado) {
                        $user->saldo -= $request->valor_apostado;
                    } else {
                        DB::rollBack();
                        return response()->json(['message' => 'Saldo insuficiente'], 422);
                    }
                }

                $user->quantidade_aposta = ($user->quantidade_aposta ?? 0) + 1;
                $user->entrada_loto = ($user->entrada_loto ?? 0) + $request->valor_apostado;
                $user->comissoes = ($user->comissoes ?? 0) + $comissao;
                $user->save();

                if ($user->gerente_id) {
                    $gerente = \App\Models\User::find($user->gerente_id);
                    if ($gerente) {
                        $gerente->quantidade_aposta = ($gerente->quantidade_aposta ?? 0) + 1;
                        $gerente->entradas = ($gerente->entradas ?? 0) + $request->valor_apostado;
                        $gerente->comissoes = ($gerente->comissoes ?? 0) + $comissao;
                        $gerente->save();
                    }
                }

                // Transação unificada
                DB::table('transactions')->insert([
                    'site_id'    => $siteId,
                    'user_id'    => $user->id,
                    'type'       => 'bet',
                    'amount'     => -$request->valor_apostado,
                    'gateway_ref'=> "bet_{$aposta->id}",
                    'status'     => 'completed',
                    'description'=> "Aposta Loto (Cupom: {$cupom})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();
                return response()->json(['id' => $aposta->id, 'cupom' => $cupom]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Loto Bet Error: ' . $e->getMessage());
                return response()->json(['message' => 'Erro interno ao processar aposta'], 500);
            }
        }

        return response()->json(['message' => 'Usuário inativo ou não autorizado'], 403);
    }
}
