<?php

namespace App\Http\Controllers\Gerente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Aposta;
use App\Models\Palpite;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GerenteBilhetesController extends Controller
{
    public function index()
    {
        $gerenteId = auth()->user()->id;

        $cambistas = User::where('gerente_id', $gerenteId)
            ->where('nivel', 'cambista')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('gerente.bilhetes', compact('cambistas'));
    }

    public function show($id)
    {
        $gerenteId = auth()->user()->id;
        $siteId = app('tenant.site_id');

        $aposta = Aposta::where('id', $id)
            ->where('site_id', $siteId)
            ->where('gerente_id', $gerenteId)
            ->with('palpites', 'user')
            ->firstOrFail();

        return response()->json($aposta);
    }

    public function search(Request $request)
    {
        try {
            $gerenteId = auth()->user()->id;
            $siteId = app('tenant.site_id');

            $cambistasIds = User::where('gerente_id', $gerenteId)
                ->where('nivel', 'cambista')
                ->pluck('id');

            $query = Aposta::query()
                ->where('site_id', $siteId)
                ->whereIn('user_id', $cambistasIds);

            if ($request->cambista && $request->cambista != 'Todos') {
                if ($cambistasIds->contains($request->cambista)) {
                    $query->where('user_id', $request->cambista);
                }
            }

            if ($request->status && $request->status != 'Todos') {
                $query->where('status', $request->status);
            }

            if ($request->esporte && $request->esporte != 'Todos') {
                $query->where('modalidade', $request->esporte);
            }

            if ($request->valor_min) {
                $query->where('valor_apostado', '>=', $request->valor_min);
            }
            if ($request->valor_max) {
                $query->where('valor_apostado', '<=', $request->valor_max);
            }

            if ($request->cliente) {
                $query->where('cliente', 'like', '%' . $request->cliente . '%');
            }
            if ($request->cupom) {
                $query->where('codigo_bilhete', 'like', '%' . $request->cupom . '%');
            }

            if ($request->date1 && $request->date2) {
                $query->whereBetween('created_at', [$request->date1 . ' 00:00:00', $request->date2 . ' 23:59:59']);
            }

            $totals = [
                'count' => (clone $query)->count(),
                'apostado' => (float) (clone $query)->sum('valor_apostado'),
                'retorno' => (float) (clone $query)->sum('retorno_possivel'),
            ];

            $bilhetes = $query->with('user')->orderBy('id', 'desc')->limit(200)->get();

            $mapped = $bilhetes->map(function ($b) {
                return [
                    'id' => $b->id,
                    'cupom' => $b->codigo_bilhete ?? $b->cupom ?? '-',
                    'created_at' => $b->created_at,
                    'status' => $b->status,
                    'valor_apostado' => (float) $b->valor_apostado,
                    'retorno_possivel' => (float) $b->retorno_possivel,
                    'cotacao' => (float) ($b->cotacao ?? 1),
                    'tipo' => $b->tipo ?? 'Simples',
                    'vendedor' => $b->user ? $b->user->name : '-',
                    'cliente' => $b->cliente ?? '-',
                    'total_palpites' => $b->total_palpites ?? 1,
                ];
            });

            return response()->json([
                'bilhetes' => $mapped,
                'totals' => $totals
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $gerenteId = auth()->user()->id;
            $siteId = app('tenant.site_id');
            $cambistasIds = User::where('gerente_id', $gerenteId)->where('nivel', 'cambista')->pluck('id');

            $bilhete = Aposta::where('id', $id)
                ->where('site_id', $siteId)
                ->whereIn('user_id', $cambistasIds)
                ->first();

            if (!$bilhete) {
                return response()->json(['message' => 'Bilhete não encontrado ou acesso negado'], 404);
            }

            if ($bilhete->status === 'Cancelado') {
                return response()->json(['message' => 'Bilhete já está cancelado']);
            }

            DB::beginTransaction();

            $oldStatus = $bilhete->status;
            $user = User::find($bilhete->user_id);
            $gerente = User::find($bilhete->gerente_id);

            if ($user) {
                $user->quantidade_aposta -= 1;
                $user->entradas -= $bilhete->valor_apostado;
                $user->comissoes -= $bilhete->comicao;

                if (in_array($bilhete->modalidade, ['Quininha', 'Seninha'])) {
                    $user->entrada_loto -= $bilhete->valor_apostado;
                    $user->comissao_loto -= $bilhete->comicao;
                    $user->saldo_loto += $bilhete->valor_apostado;
                } else {
                    if ($bilhete->total_palpites > 1) {
                        $user->entrada_casadinha -= $bilhete->valor_apostado;
                    } else {
                        $user->entrada_simples -= $bilhete->valor_apostado;
                    }
                }

                if ($oldStatus == 'Ganhou') {
                    $user->saidas -= $bilhete->retorno_possivel;
                }
                $user->save();
            }

            if ($gerente) {
                $gerente->quantidade_aposta -= 1;
                $gerente->entradas -= $bilhete->valor_apostado;
                $gerente->comissoes -= $bilhete->comicao;

                if (in_array($bilhete->modalidade, ['Quininha', 'Seninha'])) {
                    $gerente->entrada_loto -= $bilhete->valor_apostado;
                    $gerente->comissao_loto -= $bilhete->comicao;
                } else {
                    if ($bilhete->total_palpites == 1) {
                        $gerente->entrada_simples -= $bilhete->valor_apostado;
                    }
                }

                if ($oldStatus == 'Ganhou') {
                    $gerente->saidas -= $bilhete->retorno_possivel;
                }
                $gerente->save();
            }

            foreach ($bilhete->palpites as $p) {
                $p->status = 'Cancelado';
                $p->save();
            }

            DB::table('transactions')->insert([
                'site_id' => $siteId,
                'user_id' => $bilhete->user_id,
                'type' => 'bet_cancelled',
                'amount' => $bilhete->valor_apostado,
                'gateway_ref' => "aposta_{$bilhete->id}",
                'status' => 'completed',
                'description' => "Bilhete #{$bilhete->codigo_bilhete} Cancelado via Painel Gerente",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bilhete->status = 'Cancelado';
            $bilhete->save();

            DB::commit();

            return response()->json(['message' => 'Bilhete cancelado com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
