<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aposta;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function indexViewRelatorioCambista()
    {
        return view('admin.relatorio-cambista');
    }

    public function indexViewRelatorioGerente()
    {
        return view('admin.relatorio-gerente');
    }

    public function relatorioGerente(Request $request)
    {
        $siteId = app('tenant.site_id');
        $arr = array();
        $date1 = $request->date1 . ' 00:00:00';
        $date2 = $request->date2 . ' 23:59:59';
        $i = 0;

        $query = User::where('site_id', $siteId)->where('nivel', 'gerente');
        if ($request->gerente != 'Todos') {
            $query->where('id', $request->gerente);
        }
        $gerentes = $query->get();

        foreach ($gerentes as $gerente) {
            // 📊 1. DADOS ESPORTE (Unificado: Modern + Legacy)
            $statsModern = DB::table('bets')
                ->select(
                    DB::raw('sum(amount) as total_apostado'),
                    DB::raw('sum(commission_amount) as comissoes_cambistas'),
                    DB::raw('sum(manager_commission_amount) as comissao_gerente_online'),
                    DB::raw('sum(prize_commission_amount) as comissoes_premios'),
                    DB::raw('count(id) as quantidade'),
                    DB::raw('sum(case when status = "won" then potential_payout else 0 end) as total_premios')
                )
                ->where('manager_id', $gerente->id)
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'cancelled')
                ->first();

            $statsLegacyEsporte = DB::table('apostas')
                ->select(
                    DB::raw('sum(valor_apostado) as total_apostado'),
                    DB::raw('sum(comicao) as comissoes_cambistas'),
                    DB::raw('count(id) as quantidade'),
                    DB::raw('sum(case when status = "won" or status = "Ganhou" then retorno_possivel else 0 end) as total_premios')
                )
                ->where('gerente_id', $gerente->id)
                ->where('modalidade', 'Esporte')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'Cancelado')
                ->first();

            $totalApostado = ($statsModern->total_apostado ?? 0) + ($statsLegacyEsporte->total_apostado ?? 0);
            $comissoesCambistas = ($statsModern->comissoes_cambistas ?? 0) + ($statsLegacyEsporte->comissoes_cambistas ?? 0);
            $comissaoGerenteOnline = $statsModern->comissao_gerente_online ?? 0;
            $comissoesPremios = $statsModern->comissoes_premios ?? 0;
            $quantidade = ($statsModern->quantidade ?? 0) + ($statsLegacyEsporte->quantidade ?? 0);
            $premios = ($statsModern->total_premios ?? 0) + ($statsLegacyEsporte->total_premios ?? 0);

            // 🎰 DADOS LOTO (Apostas de Quininha/Seninha)
            $lotoStats = DB::table('apostas')
                ->select(
                    DB::raw('sum(valor_apostado) as total_loto'),
                    DB::raw('sum(comicao) as comissoes_loto'),
                    DB::raw('count(id) as qtd_loto')
                )
                ->where('gerente_id', $gerente->id)
                ->where('modalidade', 'Loto')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'Cancelado')
                ->first();

            $lotoPremios = DB::table('apostas')
                ->where('gerente_id', $gerente->id)
                ->where('modalidade', 'Loto')
                ->where('status', 'won')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->sum('retorno_possivel');

            $totalApostadoLoto = $lotoStats->total_loto ?? 0;
            $comissoesLoto = $lotoStats->comissoes_loto ?? 0;
            $qtdLoto = $lotoStats->qtd_loto ?? 0;

            // Soma Esportes + Loto
            $totalGeralApostado = $totalApostado + $totalApostadoLoto;
            $totalGeralPremios = $premios + $lotoPremios;
            $totalGeralComissoesCambistas = $comissoesCambistas + $comissoesLoto;

            // Cálculo do Saldo Líquido da Banca (sob gestão deste gerente)
            $saldoBanca = $totalGeralApostado - ($totalGeralComissoesCambistas + $totalGeralPremios);
            
            // 1. Comissão de Rua (Sobre o Lucro Líquido da banca)
            $comissao_gerente_rua = 0;
            if ($saldoBanca > 0) {
                $comissao_gerente_rua = ($saldoBanca * ($gerente->comissao_gerente ?? 0)) / 100;
            }

            // 2. Comissão Online (Já somada via manager_commission_amount no DB)
            // $comissaoGerenteOnline (Vem do sum(manager_commission_amount))

            $arr[$i] = [
                'name'              => $gerente->name,
                'colaborador'       => $gerente->name,
                'entradas'          => $totalGeralApostado,
                'entradas_abertas'  => 0,
                'saidas'            => $totalGeralPremios,
                'quantidade'        => $quantidade + $qtdLoto,
                'comissao_rua'      => $totalGeralComissoesCambistas + $comissoesPremios, // Ganho total do cambista (Venda + Prêmio)
                'comissao_online'   => $comissaoGerenteOnline,
                'comissoes'         => $totalGeralComissoesCambistas + $comissoesPremios + $comissao_gerente_rua + $comissaoGerenteOnline,
                'lancamentos'       => 0,
                'total'             => $saldoBanca - ($comissao_gerente_rua + $comissaoGerenteOnline), // Saldo banca já desconta o prêmio cheio (que inclui a comissão de prêmio)
            ];
            $i++;
        }

        return $arr;
    }

    public function relatorioCambista(Request $request)
    {
        $siteId = app('tenant.site_id');
        $arr = array();
        $date1 = $request->date1 . ' 00:00:00';
        $date2 = $request->date2 . ' 23:59:59';
        $i = 0;

        $query = User::where('site_id', $siteId)->where('nivel', 'cambista');
        
        // Se não for admin, filtra cambistas do gerente logado
        if (auth()->user()->nivel != 'adm' && auth()->user()->role != 'admin') {
            $query->where('gerente_id', auth()->user()->id);
        }

        if ($request->cambista != 'Todos') {
            $query->where('id', $request->cambista);
        }

        $cambistas = $query->get();

        foreach ($cambistas as $cambista) {
            // 📊 1. DADOS ESPORTE (Unificado: Modern + Legacy)
            $statsModern = DB::table('bets')
                ->select(
                    DB::raw('sum(amount) as total_apostado'),
                    DB::raw('sum(commission_amount) as comissoes'),
                    DB::raw('sum(prize_commission_amount) as comissoes_premios'),
                    DB::raw('count(id) as quantidade'),
                    DB::raw('sum(case when status = "won" then potential_payout else 0 end) as total_premios')
                )
                ->where(function($q) use ($cambista) {
                    $q->where('user_id', $cambista->id)
                      ->orWhere('cambista_id', $cambista->id);
                })
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'cancelled')
                ->first();

            $statsLegacyEsporte = DB::table('apostas')
                ->select(
                    DB::raw('sum(valor_apostado) as total_apostado'),
                    DB::raw('sum(comicao) as comissoes'),
                    DB::raw('count(id) as quantidade'),
                    DB::raw('sum(case when status = "won" or status = "Ganhou" then retorno_possivel else 0 end) as total_premios')
                )
                ->where('user_id', $cambista->id)
                ->where('modalidade', 'Esporte')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'Cancelado')
                ->first();

            $totalApostado = ($statsModern->total_apostado ?? 0) + ($statsLegacyEsporte->total_apostado ?? 0);
            $premios = ($statsModern->total_premios ?? 0) + ($statsLegacyEsporte->total_premios ?? 0);
            $quantidadeEsporte = ($statsModern->quantidade ?? 0) + ($statsLegacyEsporte->quantidade ?? 0);
            $comissoesEsporte = ($statsModern->comissoes ?? 0) + ($statsModern->comissoes_premios ?? 0) + ($statsLegacyEsporte->comissoes ?? 0);
            
            // 🎰 DADOS LOTO (Apostas de Quininha/Seninha)
            $lotoStats = DB::table('apostas')
                ->select(
                    DB::raw('sum(valor_apostado) as total_loto'),
                    DB::raw('sum(comicao) as comissoes_loto'),
                    DB::raw('count(id) as qtd_loto')
                )
                ->where('user_id', $cambista->id) // Em 'apostas', user_id é o cambista
                ->where('modalidade', 'Loto')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'Cancelado')
                ->first();

            $lotoPremios = DB::table('apostas')
                ->where('user_id', $cambista->id)
                ->where('modalidade', 'Loto')
                ->where('status', 'won')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->sum('retorno_possivel');

            $totalApostadoLoto = $lotoStats->total_loto ?? 0;
            $comissoesLoto = $lotoStats->comissoes_loto ?? 0;
            $qtdLoto = $lotoStats->qtd_loto ?? 0;

            // Soma Esportes + Loto
            $totalGeralApostado = $totalApostado + $totalApostadoLoto;
            $totalGeralPremios = $premios + $lotoPremios;
            $totalGeralComissoes = $comissoesEsporte + $comissoesLoto;
            
            $arr[$i] = [
                'name'              => $cambista->name,
                'colaborador'       => $cambista->name,
                'entradas'          => $totalGeralApostado,
                'entradas_abertas'  => 0,
                'saidas'            => $totalGeralPremios,
                'quantidade'        => $quantidadeEsporte + $qtdLoto,
                'comissao_rua'      => $totalGeralComissoes,
                'comissao_online'   => 0,
                'comissoes'         => $totalGeralComissoes,
                'lancamentos'       => 0,
                'total'             => $totalGeralApostado - ($totalGeralComissoes + $totalGeralPremios),
            ];
            $i++;
        }

        // 🏠 Adiciona linha de VENDAS DIRETAS para o Admin
        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'admin') {
            $directStats = DB::table('bets')
                ->select(
                    DB::raw('sum(amount) as total_apostado'),
                    DB::raw('count(id) as quantidade')
                )
                ->where('site_id', $siteId)
                ->whereNull('cambista_id')
                ->where('created_at', '>=', $date1)
                ->where('created_at', '<=', $date2)
                ->where('status', '!=', 'cancelled')
                ->first();

            if (($directStats->quantidade ?? 0) > 0) {
                $directPremios = DB::table('bets')
                    ->where('site_id', $siteId)
                    ->whereNull('cambista_id')
                    ->where('status', 'won')
                    ->where('created_at', '>=', $date1)
                    ->where('created_at', '<=', $date2)
                    ->sum('potential_payout');

                $arr[$i] = [
                    'name'              => 'VENDAS DIRETAS (SITE)',
                    'colaborador'       => 'VENDAS DIRETAS (SITE)',
                    'entradas'          => $directStats->total_apostado,
                    'entradas_abertas'  => 0,
                    'saidas'            => $directPremios,
                    'quantidade'        => $directStats->quantidade,
                    'comissao_rua'      => 0,
                    'comissao_online'   => 0,
                    'comissoes'         => 0,
                    'lancamentos'       => 0,
                    'total'             => $directStats->total_apostado - $directPremios,
                ];
            }
        }

        return $arr;
    }
}
