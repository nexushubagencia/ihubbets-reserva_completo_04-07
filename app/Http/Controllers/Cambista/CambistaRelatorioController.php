<?php

namespace App\Http\Controllers\Cambista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CambistaRelatorioController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $siteId = app('tenant.site_id');

        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));

        $apostas = DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$dataInicio . ' 00:00:00', $dataFim . ' 23:59:59'])
            ->whereNotIn('status', ['Cancelado'])
            ->get();

        $totalApostado = (float) $apostas->sum('valor_apostado');
        $totalRetorno = (float) $apostas->where('status', 'Ganhou')->sum('retorno_possivel');
        $totalComissao = (float) $apostas->sum('comicao');
        $lucro = $totalApostado - $totalRetorno;
        $totalApostas = $apostas->count();

        $porDia = $apostas->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        })->map(function ($group, $dia) {
            $ganhas = $group->where('status', 'Ganhou');
            return [
                'data'           => $dia,
                'qtd_apostas'    => $group->count(),
                'total_apostado' => (float) $group->sum('valor_apostado'),
                'totalretorno'   => (float) $ganhas->sum('retorno_possivel'),
                'comissao'       => (float) $group->sum('comicao'),
                'lucro'          => (float) $group->sum('valor_apostado') - (float) $ganhas->sum('retorno_possivel'),
            ];
        })->sortByDesc('data')->values();

        return view('cambista.relatorio', compact(
            'user',
            'dataInicio',
            'dataFim',
            'totalApostado',
            'totalRetorno',
            'totalComissao',
            'lucro',
            'totalApostas',
            'porDia'
        ));
    }

    public function filtrar(Request $request)
    {
        return redirect()->route('cambista.relatorio', $request->only('data_inicio', 'data_fim'));
    }
}
