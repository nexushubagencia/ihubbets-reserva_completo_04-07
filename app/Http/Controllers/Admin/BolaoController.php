<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rodada;
use App\Models\Aposta;
use App\Models\PalpiteBolao;
use Illuminate\Support\Facades\DB;

class BolaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rodadas = Rodada::where('site_id', config('tenant.site_id', 1))
            ->orderBy('created_at', 'desc')
            ->get();

        $totalApostas = Aposta::where('modalidade', 'Bolao')
            ->where('site_id', config('tenant.site_id', 1))
            ->count();

        $totalArrecadado = Aposta::where('modalidade', 'Bolao')
            ->where('site_id', config('tenant.site_id', 1))
            ->sum('valor_apostado');

        return view('admin.bolao', compact('rodadas', 'totalApostas', 'totalArrecadado'));
    }

    public function storeRodada(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'premio_max' => 'required|numeric|min:0',
            'premio_primeiro' => 'required|numeric|min:0',
            'premio_segundo' => 'nullable|numeric|min:0',
            'premio_terceiro' => 'nullable|numeric|min:0',
            'data_fechamento' => 'required|date',
        ]);

        $siteId = config('tenant.site_id', 1);

        Rodada::create(array_merge($request->only([
            'nome', 'premio_max', 'premio_primeiro', 'premio_segundo',
            'premio_terceiro', 'data_fechamento',
        ]), ['site_id' => $siteId]));

        return response()->json(['success' => true]);
    }

    public function updateRodada(Request $request, $id)
    {
        $siteId = config('tenant.site_id', 1);
        $rodada = Rodada::where('id', $id)->where('site_id', $siteId)->firstOrFail();
        $rodada->update($request->only([
            'nome', 'status', 'premio_max', 'premio_primeiro',
            'premio_segundo', 'premio_terceiro', 'data_fechamento',
        ]));

        return response()->json(['success' => true]);
    }

    public function destroyRodada($id)
    {
        $siteId = config('tenant.site_id', 1);
        $rodada = Rodada::where('id', $id)->where('site_id', $siteId)->firstOrFail();

        if ($rodada->quantidade > 0) {
            return response()->json(['error' => 'Rodada com apostas nao pode ser excluida'], 422);
        }

        $rodada->delete();
        return response()->json(['success' => true]);
    }

    public function apostasRodada($id)
    {
        $siteId = config('tenant.site_id', 1);
        $apostas = Aposta::where('rodada_id', $id)
            ->where('site_id', $siteId)
            ->with(['user', 'palpitesBolao'])
            ->get();

        return response()->json($apostas);
    }

    public function fecharRodada($id)
    {
        $siteId = config('tenant.site_id', 1);
        $rodada = Rodada::where('id', $id)->where('site_id', $siteId)->firstOrFail();

        $rodada->update(['status' => 'Fechada', 'data_fechamento' => now()]);

        return response()->json(['success' => true]);
    }

    public function finalizarRodada(Request $request, $id)
    {
        $siteId = config('tenant.site_id', 1);
        $rodada = Rodada::where('id', $id)->where('site_id', $siteId)->firstOrFail();

        $request->validate([
            'resultados' => 'required|array',
            'resultados.*.match_id' => 'required|integer',
            'resultados.*.resultado' => 'required|string|in:1,X,2',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->resultados as $r) {
                PalpiteBolao::where('rodada_id', $id)
                    ->where('match_id', $r['match_id'])
                    ->update(['resultado' => $r['resultado']]);
            }

            $apostas = Aposta::where('rodada_id', $id)
                ->where('site_id', $siteId)
                ->where('status', 'Aberto')
                ->with('palpitesBolao')
                ->get();

            foreach ($apostas as $aposta) {
                $acertos = 0;
                $erros = 0;

                foreach ($aposta->palpitesBolao as $palpite) {
                    if ($palpite->resultado && $palpite->palpite === $palpite->resultado) {
                        $acertos++;
                        $palpite->update(['status' => 'Acertou']);
                    } else {
                        $erros++;
                        $palpite->update(['status' => 'Errou']);
                    }
                }

                $aposta->update([
                    'acertos_palpites' => $acertos,
                    'erros_palpites' => $erros,
                    'status' => $acertos >= count($aposta->palpitesBolao) ? 'Ganhou' : 'Perdeu',
                ]);
            }

            $rodada->update(['status' => 'Finalizada']);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
