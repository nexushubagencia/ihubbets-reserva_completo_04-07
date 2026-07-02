<?php

namespace App\Services;

use App\Models\CashierCloseout;
use App\Models\DailyCashSnapshot;
use App\Models\Lancamento;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CashierService
{
    public function generateClosingReport($userId, $siteId): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \RuntimeException('Usuario nao encontrado: ' . $userId);
        }

        $totalEntradas = (float) $user->entradas + (float) $user->entrada_loto;
        $totalSaidas = (float) $user->saidas;
        $totalComissoes = (float) $user->comissoes;
        $totalLancamentos = (float) $user->lancamentos;

        $betsAbertas = DB::table('bets')
            ->where('status', 'open')
            ->where('site_id', $siteId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('cambista_id', $user->id);
            })
            ->select(DB::raw('SUM(amount) as total_apostado'), DB::raw('COUNT(id) as quantidade'))
            ->first();

        $totalAbertas = (float) ($betsAbertas->total_apostado ?? 0);
        $qtdAbertas = (int) ($betsAbertas->quantidade ?? 0);

        $liquido = $totalEntradas + $totalLancamentos - ($totalSaidas + $totalComissoes);
        $comissaoGerente = max(0, $liquido) * ((float) $user->comissao_gerente / 100);

        $betStats = $this->getBetStats($user, $siteId);

        return compact(
            'user',
            'totalEntradas',
            'totalSaidas',
            'totalComissoes',
            'totalLancamentos',
            'totalAbertas',
            'qtdAbertas',
            'liquido',
            'comissaoGerente',
            'betStats'
        );
    }

    public function closeCashier($userId, $closedBy, $turno = 'integral', $siteId = null): CashierCloseout
    {
        $siteId = $siteId ?? config('tenant.site_id', 1);

        DB::beginTransaction();
        try {
            $report = $this->generateClosingReport($userId, $siteId);
            $user = User::find($userId);

            $closeout = CashierCloseout::create([
                'user_id' => $userId,
                'closed_by' => $closedBy,
                'site_id' => $siteId,
                'turno' => $turno,
                'total_entradas' => $report['totalEntradas'],
                'total_saidas' => $report['totalSaidas'],
                'total_comissoes' => $report['totalComissoes'],
                'total_lancamentos' => $report['totalLancamentos'],
                'total_entradas_abertas' => $report['totalAbertas'],
                'quantidade_apostas' => $user->quantidade_aposta,
                'total_liquido' => $report['liquido'],
                'comissao_gerente' => $report['comissaoGerente'],
                'saldo_anterior' => $report['liquido'],
                'saldo_final' => 0,
                'detalhes' => $report['betStats'],
            ]);

            DB::table('transactions')->insert([
                'site_id'     => $siteId,
                'user_id'     => $userId,
                'type'        => 'cashier_closeout',
                'amount'      => $report['liquido'],
                'gateway_ref' => 'closeout_' . $closeout->id . '_' . time(),
                'status'      => 'completed',
                'description' => 'Fechamento de Caixa (' . $closeout->turno_label . '): Liquido R$ ' . number_format($report['liquido'], 2, ',', '.'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $this->updateDailySnapshot($userId, $report);

            $user->entradas = 0;
            $user->entrada_loto = 0;
            $user->entrada_casadinha = 0;
            $user->entrada_simples = 0;
            $user->saidas = 0;
            $user->comissoes = 0;
            $user->lancamentos = 0;
            $user->quantidade_aposta = 0;
            $user->save();

            Lancamento::where('user_id', $userId)->delete();

            DB::commit();

            return $closeout;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateDailySnapshot($userId, array $report, $siteId = null): void
    {
        $siteId = $siteId ?? config('tenant.site_id', 1);
        $today = now()->toDateString();

        DailyCashSnapshot::updateOrCreate(
            ['user_id' => $userId, 'snapshot_date' => $today, 'site_id' => $siteId],
            [
                'entradas_dia'    => $report['totalEntradas'],
                'saidas_dia'      => $report['totalSaidas'],
                'comissoes_dia'   => $report['totalComissoes'],
                'lancamentos_dia' => $report['totalLancamentos'],
                'apostas_dia'     => $report['user']->quantidade_aposta,
                'lucro_dia'       => $report['liquido'],
                'saldo_fechamento' => $report['liquido'],
            ]
        );
    }

    public function getCaixaDoDia($userId, $siteId): array
    {
        $today = now()->toDateString();

        $snapshot = DailyCashSnapshot::where('user_id', $userId)
            ->where('snapshot_date', $today)
            ->where('site_id', $siteId)
            ->first();

        $closeoutsHoje = CashierCloseout::where('user_id', $userId)
            ->where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->get();

        $fechamentos = $closeoutsHoje->map(function ($c) {
            return [
                'id' => $c->id,
                'turno' => $c->turno_label,
                'horario' => $c->created_at->format('H:i'),
                'liquido' => $c->total_liquido,
                'entradas' => $c->total_entradas,
                'saidas' => $c->total_saidas,
                'closed_by' => $c->closedBy->name ?? 'N/A',
            ];
        });

        return [
            'snapshot' => $snapshot,
            'fechamentos' => $fechamentos,
            'total_fechamentos_hoje' => $closeoutsHoje->count(),
            'total_liquido_fechado' => $closeoutsHoje->sum('total_liquido'),
        ];
    }

    public function getCloseoutHistory($userId, $siteId, $limit = 30): Collection
    {
        return CashierCloseout::where('user_id', $userId)
            ->where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->with('closedBy:id,name')
            ->get();
    }

    public function getDailySummary($userId, $dateFrom, $dateTo): Collection
    {
        return DailyCashSnapshot::where('user_id', $userId)
            ->whereBetween('snapshot_date', [$dateFrom, $dateTo])
            ->orderBy('snapshot_date')
            ->get();
    }

    public function getAllCaixaCambista($siteId): array
    {
        $users = User::where('nivel', 'cambista')
            ->where('site_id', $siteId)
            ->orderBy('quantidade_aposta', 'DESC')
            ->get();

        $arr = [];
        foreach ($users as $user) {
            $bets = DB::table('bets')
                ->select(DB::raw('SUM(amount) as total_apostado'), DB::raw('COUNT(id) as qtd'))
                ->where('status', 'open')
                ->where('site_id', $siteId)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('cambista_id', $user->id);
                })
                ->first();

            $entradas = (float) $user->entradas + (float) $user->entrada_loto;
            $total = $entradas + (float) $user->lancamentos - ((float) $user->saidas + (float) $user->comissoes);

            $arr[] = [
                'id' => $user->id,
                'colaborador' => $user->name,
                'quantidade' => $user->quantidade_aposta,
                'entradas' => $entradas,
                'entradas_abertas' => (float) ($bets->total_apostado ?? 0),
                'saidas' => (float) $user->saidas,
                'comissoes' => (float) $user->comissoes,
                'lancamentos' => (float) $user->lancamentos,
                'total' => $total,
            ];
        }

        $directBets = DB::table('bets')
            ->where('site_id', $siteId)
            ->whereNull('cambista_id')
            ->where('status', 'open')
            ->select(DB::raw('SUM(amount) as entradas'), DB::raw('COUNT(id) as qtd'))
            ->first();

        if (($directBets->qtd ?? 0) > 0) {
            $arr[] = [
                'id' => 0,
                'colaborador' => 'VENDAS DIRETAS (SITE)',
                'quantidade' => $directBets->qtd,
                'entradas' => (float) $directBets->entradas,
                'entradas_abertas' => 0,
                'saidas' => 0,
                'comissoes' => 0,
                'lancamentos' => 0,
                'total' => (float) $directBets->entradas,
            ];
        }

        return $arr;
    }

    public function getCaixaDoDiaGeral($siteId): array
    {
        $today = now()->toDateString();

        $users = User::whereIn('nivel', ['cambista', 'gerente'])
            ->where('site_id', $siteId)
            ->get();

        $resumo = [
            'total_entradas' => 0,
            'total_saidas' => 0,
            'total_comissoes' => 0,
            'total_lancamentos' => 0,
            'total_liquido' => 0,
            'total_apostas' => 0,
            'usuarios' => [],
        ];

        foreach ($users as $user) {
            $entradas = (float) $user->entradas + (float) $user->entrada_loto;
            $total = $entradas + (float) $user->lancamentos - ((float) $user->saidas + (float) $user->comissoes);

            $resumo['total_entradas'] += $entradas;
            $resumo['total_saidas'] += (float) $user->saidas;
            $resumo['total_comissoes'] += (float) $user->comissoes;
            $resumo['total_lancamentos'] += (float) $user->lancamentos;
            $resumo['total_liquido'] += $total;
            $resumo['total_apostas'] += (int) $user->quantidade_aposta;

            $resumo['usuarios'][] = [
                'id' => $user->id,
                'name' => $user->name,
                'nivel' => $user->nivel,
                'entradas' => $entradas,
                'saidas' => (float) $user->saidas,
                'comissoes' => (float) $user->comissoes,
                'lancamentos' => (float) $user->lancamentos,
                'total' => $total,
                'quantidade' => $user->quantidade_aposta,
            ];
        }

        $resumo['fechamentos_hoje'] = CashierCloseout::where('site_id', $siteId)
            ->whereDate('created_at', $today)
            ->count();

        return $resumo;
    }

    private function getBetStats($user, $siteId): array
    {
        $bets = DB::table('bets')
            ->select('status', DB::raw('COUNT(id) as quantidade'), DB::raw('SUM(amount) as total'))
            ->where('site_id', $siteId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('cambista_id', $user->id);
            })
            ->groupBy('status')
            ->get();

        return $bets->pluck('total', 'status')->toArray();
    }
}
