<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\User;
use App\Models\BonusUser;
use App\Services\BetValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserBetController extends Controller
{
    /**
     * Usuário logado faz uma aposta usando seu saldo (real ou bônus)
     * 
     * IMPORTANTE: A resposta DEVE seguir o mesmo formato do PublicBetController
     * pois o modal-bilhete no Geral.vue espera campos específicos:
     * status, cupom, id, cliente, vendedor, valor_apostado, retorno_possivel,
     * total_palpites, palpites, cotacao, created_at, tipo
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $siteId = config('tenant.site_id', 1);
        
        // 🚀 ADICIONADO: Admins/Gerentes não apostam como clientes
        if (in_array($user->role, ['admin', 'manager', 'super_admin', 'adm']) || in_array($user->nivel, ['admin', 'manager', 'adm'])) {
            return response()->json(['status' => 'error', 'message' => 'Contas administrativas não podem realizar apostas diretas pelo site. Use o painel administrativo.'], 403);
        }

        // Suporta tanto o novo padrão quanto o padrão legado do frontend
        $amount = $request->amount ?? $request->valor_apostado;
        $selections = $request->selections ?? $request->palpites;
        $potentialReturn = $request->potential_return ?? $request->retorno_possivel;
        $cliente = $request->cliente ?? $request->client_name ?? $user->name;
        $cotacao = $request->cotacao ?? 0;
        $useBonus = filter_var($request->use_bonus, FILTER_VALIDATE_BOOLEAN) ?? false;
        $preBetId = $request->pre_bet_id ?? null;

        if (!$amount || !$selections) {
            return response()->json(['status' => 'error', 'message' => 'Dados da aposta incompletos.'], 400);
        }

        // Calcula cotação total a partir das seleções
        $totalOdd = 1.0;
        $isLive = false;
        foreach ($selections as $sel) {
            $sel = is_array($sel) ? $sel : (array) $sel;
            $totalOdd *= (float)($sel['cotacao'] ?? $sel['value'] ?? $sel['odd'] ?? 1.0);
            if (!empty($sel['is_live'])) $isLive = true;
        }

        // Determina tipo de carteira
        $walletType = $useBonus ? 'casadinha' : 'simples';

        // 1. Validação completa via BetValidator (11 regras do admin)
        $validator = new BetValidator((int)$siteId);
        $validation = $validator->validate([
            'amount'           => (float)$amount,
            'potential_payout'  => (float)($potentialReturn ?? 0),
            'total_odd'        => $totalOdd,
            'selections'       => $selections,
            'user_id'          => $user->id,
            'is_live'          => $isLive,
            'is_multiple'      => count($selections) > 1,
            'wallet_type'      => $walletType,
        ]);

        if (!$validation['ok']) {
            return response()->json(['status' => 'error', 'message' => $validation['message']], 422);
        }

        // 2. Verificação de Bônus (Restrição Live)
        if ($useBonus) {
            $activeBonus = BonusUser::where('user_id', $user->id)
                                    ->where('status', 'active')
                                    ->first();
            
            if (!$activeBonus) {
                return response()->json(['status' => 'error', 'message' => 'Você não possui bônus ativo.'], 400);
            }

            if ($activeBonus->current_balance < $amount) {
                return response()->json(['status' => 'error', 'message' => 'Saldo de bônus insuficiente.'], 400);
            }

            // Restrição: Bônus apenas para apostas AO VIVO
            foreach ($selections as $sel) {
                if (!isset($sel['is_live']) || !$sel['is_live']) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'O saldo de bônus só pode ser usado em apostas AO VIVO.'
                    ], 403);
                }
            }
        }

        DB::beginTransaction();
        try {
            // 2. Lógica de Comissão Flat (Online/Afiliados)
            $comissaoAmount = 0;
            $managerCommissionAmount = 0;
            
            // Busca o cambista vinculado (Afiliado) para cálculo de comissão online
            $cambista = User::find($user->cambista_id);
            if ($cambista) {
                // Afiliado ganha % fixa configurada no perfil dele
                $comissaoPercent = (float)($cambista->comissao_online ?? 0);
                $comissaoAmount = ($amount * $comissaoPercent) / 100;

                // Gerente ganha % fixa configurada no perfil dele (sobre o volume dos seus afiliados)
                $gerente = User::find($cambista->gerente_id ?? $cambista->parent_id ?? $cambista->manager_id);
                if ($gerente) {
                    $gerentePercent = (float)($gerente->comissao_gerente_online ?? 0);
                    $managerCommissionAmount = ($amount * $gerentePercent) / 100;
                }
            }

            // 3. Gera código do bilhete
            $ticketCode = strtoupper(substr(md5(uniqid(rand(), true)), 0, 7));

            // 4. Cria o Bilhete
            $bet = Bet::create([
                'site_id' => $siteId,
                'user_id' => $user->id,
                'manager_id' => $gerente->id ?? null,
                'cambista_id' => $user->cambista_id, // 🚀 Vínculo híbrido (Afiliado/Rua)
                'amount' => $amount,
                'potential_payout' => $potentialReturn,
                'commission_amount' => $comissaoAmount,
                'manager_commission_amount' => $managerCommissionAmount, // 📊 Nova coluna de auditoria
                'selections' => json_encode($selections),
                'status' => 'open',
                'is_bonus_bet' => $useBonus,
                'ticket_code' => $ticketCode,
                'client_name' => $cliente
            ]);

            // 5. Criação dos BetItems (Crucial para o ResultProcessor)
            foreach ($selections as $p) {
                $sel = is_array($p) ? $p : (array) $p;
                \App\Models\BetItem::create([
                    'bet_id'          => $bet->id,
                    'match_id'        => $sel['idEvent'] ?? $sel['match_id'] ?? $sel['partida'] ?? 0,
                    'league_name'     => $sel['league'] ?? 'Desconhecido',
                    'home_team'       => $sel['home'] ?? 'Casa',
                    'away_team'       => $sel['away'] ?? 'Fora',
                    'market_name'     => $sel['group_opp'] ?? $sel['market'] ?? '1x2',
                    'selection_label' => $sel['palpite'] ?? $sel['odd'] ?? '',
                    'selection_odd'   => $sel['cotacao'] ?? $sel['value'] ?? 1.0,
                    'status'          => 'pending',
                ]);
            }

            // 6. Atualização Financeira do Usuário
            if ($useBonus) {
                DB::table('bonus_user')
                  ->where('user_id', $user->id)
                  ->where('status', 'active')
                  ->decrement('current_balance', $amount);
                
                DB::table('bonus_user')
                  ->where('user_id', $user->id)
                  ->where('status', 'active')
                  ->increment('current_rollover', $amount);

                // Log transação bônus
                DB::table('transactions')->insert([
                    'site_id'    => $siteId,
                    'user_id'    => $user->id,
                    'type'       => 'bet_placed_bonus',
                    'amount'     => $amount,
                    'gateway_ref'=> "bet_{$bet->id}",
                    'status'     => 'completed',
                    'description'=> "Aposta realizada com bônus (Bilhete: {$ticketCode})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Sempre decrementa o balance para punters e cambistas (limite de crédito)
                DB::table('master_users')->where('id', $user->id)->decrement('balance', $amount);

                // Log transação real
                DB::table('transactions')->insert([
                    'site_id'    => $siteId,
                    'user_id'    => $user->id,
                    'type'       => 'bet_placed',
                    'amount'     => $amount,
                    'gateway_ref'=> "bet_{$bet->id}",
                    'status'     => 'completed',
                    'description'=> "Aposta realizada (Bilhete: {$ticketCode})",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Se for cambista (Físico) ou tiver um cambista vinculado (Online/Afiliado)
                if ($cambista) {
                    $cambista->quantidade_aposta = ($cambista->quantidade_aposta ?? 0) + 1;
                    $cambista->entradas = ($cambista->entradas ?? 0) + $amount;
                    $cambista->comissoes = ($cambista->comissoes ?? 0) + $comissaoAmount;
                    
                    if (count($selections) == 1) {
                        $cambista->entrada_simples = ($cambista->entrada_simples ?? 0) + $amount;
                    } else {
                        $cambista->entrada_casadinha = ($cambista->entrada_casadinha ?? 0) + $amount;
                    }
                    $cambista->save();

                    // Atualiza Gerente (Pai)
                    if (isset($gerente)) {
                        $gerente->quantidade_aposta = ($gerente->quantidade_aposta ?? 0) + 1;
                        $gerente->entradas = ($gerente->entradas ?? 0) + $amount;
                        // Gerente ganha sua própria comissão de gestão online
                        $gerente->comissoes = ($gerente->comissoes ?? 0) + ($managerCommissionAmount ?? 0); 
                        $gerente->save();
                    }
                }
            }

            // 7. Cleanup de Pré-Aposta (PIN) se existir
            if ($preBetId) {
                \App\Models\PreBet::where('id', $preBetId)->delete();
            }

            DB::commit();

            // 8. Formata palpites para o modal
            $formattedPalpites = [];
            foreach ($selections as $p) {
                $sel = is_array($p) ? $p : (array) $p;
                $sel['match_temp'] = $sel['match_temp'] ?? $sel['date'] ?? null;
                $sel['palpite'] = $sel['palpite'] ?? $sel['odd'] ?? '';
                $sel['status'] = $sel['status'] ?? 'Aberto';
                $formattedPalpites[] = $sel;
            }

            return response()->json([
                'status' => 'Aberto',
                'message' => 'Aposta realizada com sucesso!',
                'cupom' => $ticketCode,
                'id' => $bet->id,
                'cliente' => $cliente,
                'vendedor' => $user->name,
                'valor_apostado' => $amount,
                'retorno_possivel' => $potentialReturn,
                'retorno_cambista' => $potentialReturn,
                'total_palpites' => count($formattedPalpites),
                'acertos_palpites' => 0,
                'andamento_palpites' => count($formattedPalpites),
                'erros_palpites' => 0,
                'devolvidos_palpites' => 0,
                'modalidade' => 'Esporte',
                'cotacao' => $cotacao,
                'palpites' => $formattedPalpites,
                'created_at' => $bet->created_at,
                'tipo' => count($formattedPalpites) > 1 ? 'Múltipla' : 'Simples',
                'balance' => $user->fresh()->balance,
                'balance_bonus' => $user->fresh()->balance_bonus ?? 0,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Erro ao processar aposta: ' . $e->getMessage()], 500);
        }
    }

}

