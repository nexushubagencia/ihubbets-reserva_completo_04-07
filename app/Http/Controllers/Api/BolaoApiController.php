<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rodada;
use App\Models\Aposta;
use App\Models\PalpiteBolao;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BolaoApiController extends Controller
{
    public function rodadasAbertas()
    {
        $siteId = config('tenant.site_id', 1);
        $rodadas = Rodada::where('site_id', $siteId)
            ->where('status', 'Aberta')
            ->where('data_fechamento', '>', now())
            ->orderBy('data_fechamento', 'asc')
            ->get();

        return response()->json(['rodadas' => $rodadas]);
    }

    public function rodadaDetalhes($id)
    {
        $rodada = Rodada::findOrFail($id);
        return response()->json(['rodada' => $rodada]);
    }

    public function sendBolao(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Nao autenticado'], 401);
        }

        $request->validate([
            'rodada_id' => 'required|integer',
            'valor_apostado' => 'required|numeric|min:1',
            'palpites' => 'required|array|min:1',
            'palpites.*.match_id' => 'required|integer',
            'palpites.*.home' => 'required|string',
            'palpites.*.away' => 'required|string',
            'palpites.*.palpite' => 'required|string|in:1,X,2',
        ]);

        $siteId = config('tenant.site_id', 1);
        $rodada = Rodada::where('id', $request->rodada_id)
            ->where('site_id', $siteId)
            ->where('status', 'Aberta')
            ->first();

        if (!$rodada) {
            return response()->json(['error' => 'Rodada nao encontrada ou fechada'], 422);
        }

        if (now()->greaterThan($rodada->data_fechamento)) {
            return response()->json(['error' => 'Prazo de apostas encerrado'], 422);
        }

        if ($request->valor_apostado < 1) {
            return response()->json(['error' => 'Valor minimo: R$ 1,00'], 422);
        }

        $config = \App\Models\Configuracao::where('site_id', $siteId)->first();
        if ($config && $config->bloq_aposta_madrugada) {
            $hour = (int) now()->format('H');
            if ($hour >= 0 && $hour < 6) {
                return response()->json(['error' => 'Apostas bloqueadas na madrugada (00h-06h)'], 422);
            }
        }

        $userBalance = ($user->saldo_bolao ?? 0) - ($user->entrada_bolao ?? 0);
        if ($userBalance < $request->valor_apostado) {
            return response()->json(['error' => 'Saldo insuficiente na carteira bolao'], 422);
        }

        $comissao = ($request->valor_apostado * ($user->comissao_bolao ?? 0)) / 100;

        $cupom = strtoupper(\Illuminate\Support\Str::random(8));

        DB::beginTransaction();
        try {
            $aposta = Aposta::create([
                'site_id' => $siteId,
                'user_id' => $user->id,
                'gerente_id' => $user->gerente_id ?? 0,
                'modalidade' => 'Bolao',
                'tipo' => 'Bolao',
                'valor_apostado' => $request->valor_apostado,
                'retorno_possivel' => $rodada->premio_max,
                'comicao' => $comissao,
                'status' => 'Aberto',
                'codigo_bilhete' => $cupom,
                'total_palpites' => count($request->palpites),
                'vendedor' => $user->name,
                'cliente' => $request->cliente ?? $user->name,
                'andamento_palpites' => count($request->palpites),
                'rodada_id' => $rodada->id,
            ]);

            foreach ($request->palpites as $p) {
                PalpiteBolao::create([
                    'aposta_id' => $aposta->id,
                    'rodada_id' => $rodada->id,
                    'match_id' => $p['match_id'],
                    'home' => $p['home'],
                    'away' => $p['away'],
                    'mercado' => $p['mercado'] ?? 'Resultado Final',
                    'palpite' => $p['palpite'],
                    'status' => 'Aberto',
                ]);
            }

            $rodada->increment('quantidade');
            $rodada->increment('arrecadado', $request->valor_apostado);

            $cambista = User::find($user->id);
            $cambista->quantidade_aposta = ($cambista->quantidade_aposta ?? 0) + 1;
            $cambista->entradas = ($cambista->entradas ?? 0) + $request->valor_apostado;
            $cambista->entrada_bolao = ($cambista->entrada_bolao ?? 0) + $request->valor_apostado;
            $cambista->comissoes = ($cambista->comissoes ?? 0) + $comissao;
            $cambista->save();

            $gerente = User::find($user->gerente_id ?? $user->manager_id);
            if ($gerente) {
                $gerente->quantidade_aposta = ($gerente->quantidade_aposta ?? 0) + 1;
                $gerente->entradas = ($gerente->entradas ?? 0) + $request->valor_apostado;
                $gerente->comissoes = ($gerente->comissoes ?? 0) + $comissao;
                $gerente->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aposta Bolao registrada!',
                'cupom' => $cupom,
                'aposta_id' => $aposta->id,
                'valor_apostado' => $request->valor_apostado,
                'retorno_possivel' => $rodada->premio_max,
                'total_palpites' => count($request->palpites),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao processar: ' . $e->getMessage()], 500);
        }
    }
}
