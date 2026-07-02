<?php

namespace App\Http\Controllers\Gerente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aposta;
use Illuminate\Support\Facades\DB;

class GerenteRelatorioController extends Controller
{
    public function index()
    {
        $gerenteId = auth()->user()->id;

        $cambistas = User::where('gerente_id', $gerenteId)
            ->where('nivel', 'cambista')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $today = now()->format('Y-m-d');

        return view('gerente.relatorio', compact('cambistas', 'today'));
    }

    public function filtrar(Request $request)
    {
        try {
            $gerenteId = auth()->user()->id;
            $siteId = app('tenant.site_id');

            $date1 = ($request->date1 ?? now()->format('Y-m-d')) . ' 00:00:00';
            $date2 = ($request->date2 ?? now()->format('Y-m-d')) . ' 23:59:59';

            $query = User::where('site_id', $siteId)
                ->where('gerente_id', $gerenteId)
                ->where('nivel', 'cambista');

            if ($request->cambista && $request->cambista != 'Todos') {
                $query->where('id', $request->cambista);
            }

            $cambistas = $query->get();
            $result = [];

            foreach ($cambistas as $cambista) {
                $statsModern = DB::table('bets')
                    ->select(
                        DB::raw('sum(amount) as total_apostado'),
                        DB::raw('sum(commission_amount) as comissoes'),
                        DB::raw('sum(prize_commission_amount) as comissoes_premios'),
                        DB::raw('count(id) as quantidade'),
                        DB::raw('sum(case when status = "won" then potential_payout else 0 end) as total_premios')
                    )
                    ->where('user_id', $cambista->id)
                    ->where('created_at', '>=', $date1)
                    ->where('created_at', '<=', $date2)
                    ->where('status', '!=', 'cancelled')
                    ->first();

                $statsLegacy = DB::table('apostas')
                    ->select(
                        DB::raw('sum(valor_apostado) as total_apostado'),
                        DB::raw('sum(comicao) as comissoes'),
                        DB::raw('count(id) as quantidade'),
                        DB::raw('sum(case when status = "won" or status = "Ganhou" then retorno_possivel else 0 end) as total_premios')
                    )
                    ->where('user_id', $cambista->id)
                    ->where('created_at', '>=', $date1)
                    ->where('created_at', '<=', $date2)
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'Cancelado')
                    ->first();

                $totalApostado = ($statsModern->total_apostado ?? 0) + ($statsLegacy->total_apostado ?? 0);
                $comissoes = ($statsModern->comissoes ?? 0) + ($statsModern->comissoes_premios ?? 0) + ($statsLegacy->comissoes ?? 0);
                $quantidade = ($statsModern->quantidade ?? 0) + ($statsLegacy->quantidade ?? 0);
                $premios = ($statsModern->total_premios ?? 0) + ($statsLegacy->total_premios ?? 0);

                $result[] = [
                    'name' => $cambista->name,
                    'entradas' => $totalApostado,
                    'saidas' => $premios,
                    'quantidade' => $quantidade,
                    'comissoes' => $comissoes,
                    'total' => $totalApostado - ($comissoes + $premios),
                ];
            }

            $totals = [
                'entradas' => collect($result)->sum('entradas'),
                'saidas' => collect($result)->sum('saidas'),
                'quantidade' => collect($result)->sum('quantidade'),
                'comissoes' => collect($result)->sum('comissoes'),
                'total' => collect($result)->sum('total'),
            ];

            return response()->json([
                'cambistas' => $result,
                'totals' => $totals,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
