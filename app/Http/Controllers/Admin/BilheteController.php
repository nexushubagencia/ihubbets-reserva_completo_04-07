<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Aposta;
use App\Models\Palpite;
use App\Models\Configuracao;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BilheteController extends Controller
{
    public function indexView()
    {
        return view('admin.bilhetes');
    }

    public function validarPinView()
    {
        return view('admin.validar-pin');
    }

    public function index()
    {
        $siteId = app('tenant.site_id');

        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'super_admin' || auth()->user()->role == 'admin')
            return Aposta::orderBy('id', 'desc')
                ->where('site_id', $siteId)
                ->limit(60)
                ->where('tipo', '!=', 'Bolão')
                ->get();
        else
            return Aposta::orderBy('id', 'desc')
                ->where('site_id', $siteId)
                ->where('gerente_id', auth()->user()->id)
                ->where('tipo', '!=', 'Bolão')
                ->limit(60)
                ->get();
    }

    public function search(Request $request)
    {
        try {
            $siteId = app('tenant.site_id');
            $query = Aposta::query()->where('site_id', $siteId);

            // Filtros Básicos
            if ($request->cambista && $request->cambista != 'Todos') {
                $query->where('user_id', $request->cambista);
            }
            if ($request->gerente && $request->gerente != 'Todos') {
                $query->where('gerente_id', $request->gerente);
            }
            if ($request->status && $request->status != 'Todos') {
                $query->where('status', $request->status);
            }
            if ($request->esporte && $request->esporte != 'Todos') {
                $query->where('modalidade', $request->esporte);
            }

            // Filtros de Valor
            if ($request->valor_min) {
                $query->where('valor_apostado', '>=', $request->valor_min);
            }
            if ($request->valor_max) {
                $query->where('valor_apostado', '<=', $request->valor_max);
            }

            // Filtros de Texto
            if ($request->cliente) {
                $query->where('cliente', 'like', '%' . $request->cliente . '%');
            }
            if ($request->cupom) {
                $query->where('codigo_bilhete', 'like', '%' . $request->cupom . '%');
            }
            if ($request->confronto) {
                $query->whereHas('palpites', function($q) use ($request) {
                    $q->where('home_team', 'like', '%' . $request->confronto . '%')
                      ->orWhere('away_team', 'like', '%' . $request->confronto . '%');
                });
            }

            // Filtro de Data
            if ($request->date1 && $request->date2) {
                $query->whereBetween('created_at', [$request->date1 . ' 00:00:00', $request->date2 . ' 23:59:59']);
            }

            // Totais do Período
            $totalsQuery = clone $query;
            $totals = [
                'count' => $totalsQuery->count(),
                'apostado' => (float)$totalsQuery->sum('valor_apostado'),
                'retorno' => (float)$totalsQuery->sum('retorno_possivel'),
                'comissao' => (float)$totalsQuery->sum('comicao'),
            ];

            $bilhetes = $query->with('user')->orderBy('id', 'desc')->limit(200)->get();

            $mapped = $bilhetes->map(function ($b) {
                return [
                    'id' => $b->id,
                    'user_id' => $b->user_id,
                    'cupom' => $b->codigo_bilhete ?? $b->cupom ?? '-',
                    'created_at' => $b->created_at,
                    'status' => $b->status,
                    'valor_apostado' => (float)$b->valor_apostado,
                    'retorno_possivel' => (float)$b->retorno_possivel,
                    'comissao' => (float)($b->comicao ?? 0),
                    'cotacao' => (float)($b->cotacao ?? 1),
                    'tipo' => $b->tipo ?? 'Simples',
                    'vendedor' => $b->user ? $b->user->name : 'Sistema',
                    'cliente' => $b->cliente ?? $b->cliente_nome ?? '-',
                    'progresso' => ($b->total_palpites ?? 1) . '/' . ($b->total_palpites ?? 1),
                    'ip' => $b->ip_aposta ?? $b->ip ?? '-', 
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

    public function update(Request $request, $id)
    {
        try {
            $siteId = app('tenant.site_id');
            $bilhete = Aposta::with('palpites')->where('id', $id)->where('site_id', $siteId)->first();

            if (!$bilhete) {
                return response()->json(['message' => 'Bilhete não encontrado'], 404);
            }

            DB::beginTransaction();

            // Backup do status antigo para comparar impacto financeiro
            $oldStatus = $bilhete->status;
            $newStatus = $request->status ?? $oldStatus;

            // Atualizar Cliente
            if ($request->has('cliente')) {
                $bilhete->cliente = $request->cliente;
            }

            // Fluxo Financeiro (Se o status mudou)
            if ($newStatus != $oldStatus) {
                $user = User::find($bilhete->user_id);
                $gerente = User::find($bilhete->gerente_id);

                // Caso 1: CANCELAMENTO (Estorno Total)
                if ($newStatus == 'Cancelado') {
                    if ($user) {
                        $user->quantidade_aposta -= 1;
                        $user->entradas -= $bilhete->valor_apostado;
                        $user->comissoes -= $bilhete->comicao;
                        
                        // Diferencia Esporte de Loto
                        if (in_array($bilhete->modalidade, ['Quininha', 'Seninha'])) {
                            $user->entrada_loto -= $bilhete->valor_apostado;
                            $user->comissao_loto -= $bilhete->comicao;
                            $user->saldo_loto += $bilhete->valor_apostado; // Estorna o saldo loto se foi usado
                        } else {
                            if ($bilhete->total_palpites > 1) { $user->entrada_casadinha -= $bilhete->valor_apostado; }
                            else { $user->entrada_simples -= $bilhete->valor_apostado; }
                        }
                        
                        // Se já tinha ganho, estorna a saída
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
                            if ($bilhete->total_palpites == 1) { $gerente->entrada_simples -= $bilhete->valor_apostado; }
                        }

                        if ($oldStatus == 'Ganhou') {
                            $gerente->saidas -= $bilhete->retorno_possivel;
                        }
                        $gerente->save();
                    }

                    // Cancelar palpites
                    foreach ($bilhete->palpites as $p) {
                        $p->status = 'Cancelado';
                        $p->save();
                    }

                    // Registrar Transação
                    DB::table('transactions')->insert([
                        'site_id' => $siteId, 'user_id' => $bilhete->user_id, 'type' => 'bet_cancelled',
                        'amount' => $bilhete->valor_apostado, 'gateway_ref' => "aposta_{$bilhete->id}",
                        'status' => 'completed', 'description' => "Bilhete #{$bilhete->codigo_bilhete} Cancelado/Estornado",
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                // Caso 2: MARCANDO COMO GANHOU (Registra Saída)
                if ($newStatus == 'Ganhou' && $oldStatus != 'Ganhou') {
                    if ($user) { $user->saidas += $bilhete->retorno_possivel; $user->save(); }
                    if ($gerente) { $gerente->saidas += $bilhete->retorno_possivel; $gerente->save(); }
                }

                // Caso 3: REVERTENDO GANHOU PARA OUTRO STATUS (Estorna Saída)
                if ($oldStatus == 'Ganhou' && $newStatus != 'Ganhou' && $newStatus != 'Cancelado') {
                    if ($user) { $user->saidas -= $bilhete->retorno_possivel; $user->save(); }
                    if ($gerente) { $gerente->saidas -= $bilhete->retorno_possivel; $gerente->save(); }
                }

                $bilhete->status = $newStatus;
            }

            $bilhete->save();
            DB::commit();

            return response()->json(['message' => 'Bilhete atualizado com sucesso']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function listCambistas()
    {
        return User::where('site_id', app('tenant.site_id'))
            ->whereIn('nivel', ['cambista', 'vendedor'])
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
    }

    public function listGerentes()
    {
        return User::where('site_id', app('tenant.site_id'))
            ->where('nivel', 'gerente')
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
    }

    /**
     * 📋 Retorna dados do bilhete com palpites para exibição / impressão (Legacy: palpites-bilhete/{id})
     */
    public function getPrintData($id)
    {
        $siteId = app('tenant.site_id');

        $b = Aposta::where('id', $id)
            ->where('site_id', $siteId)
            ->first();

        if (!$b) {
            return response()->json(['message' => 'Bilhete não encontrado'], 404);
        }

        return Aposta::where('id', $id)
            ->where('site_id', $siteId)
            ->with('palpites')
            ->get();
    }

    /**
     * 🔄 Altera o status de um bilhete com recálculo financeiro completo.
     *  Suporta: Ganhou, Perdeu, Cancelado, Devolvido, Cashout
     */
    public function changeStatus(Request $request, $id)
    {
        $newStatus = $request->status;
        $siteId = app('tenant.site_id');

        DB::beginTransaction();
        try {
            $bilhete = Aposta::where('id', $id)->where('site_id', $siteId)->first();
            if (!$bilhete) {
                return response()->json(['message' => 'Bilhete não encontrado'], 404);
            }

            $oldStatus = $bilhete->status;
            if ($oldStatus == $newStatus) {
                return response()->json(['message' => 'sucesso'], 200);
            }

            $user = User::find($bilhete->user_id);
            $gerente = User::find($bilhete->gerente_id);

            // 1. Reverter o efeito do status antigo
            if ($oldStatus == 'Ganhou') {
                $user->saidas -= $bilhete->retorno_possivel;
                if ($gerente) $gerente->saidas -= $bilhete->retorno_possivel;
            } elseif (in_array($oldStatus, ['Cancelado', 'Devolvido', 'Cashout'])) {
                $user->entradas += $bilhete->valor_apostado;
                if ($gerente) $gerente->entradas += $bilhete->valor_apostado;
                $user->comissoes += $bilhete->comicao;
                if ($gerente) $gerente->comissoes += $bilhete->comicao;
                if ($bilhete->total_palpites > 1) {
                    $user->entrada_casadinha += $bilhete->valor_apostado;
                } else {
                    $user->entrada_simples += $bilhete->valor_apostado;
                    if ($gerente) $gerente->entrada_simples += $bilhete->valor_apostado;
                }
            }

            // 2. Aplicar o efeito do novo status
            if ($newStatus == 'Ganhou') {
                $user->saidas += $bilhete->retorno_possivel;
                if ($gerente) $gerente->saidas += $bilhete->retorno_possivel;
            } elseif (in_array($newStatus, ['Cancelado', 'Devolvido', 'Cashout'])) {
                $user->entradas -= $bilhete->valor_apostado;
                if ($gerente) $gerente->entradas -= $bilhete->valor_apostado;
                $user->comissoes -= $bilhete->comicao;
                if ($gerente) $gerente->comissoes -= $bilhete->comicao;
                if ($bilhete->total_palpites > 1) {
                    $user->entrada_casadinha -= $bilhete->valor_apostado;
                } else {
                    $user->entrada_simples -= $bilhete->valor_apostado;
                    if ($gerente) $gerente->entrada_simples -= $bilhete->valor_apostado;
                }
            }

            // Atualizar o status do bilhete
            $bilhete->status = $newStatus;
            $bilhete->save();

            // Sincronizar palpites
            if (in_array($newStatus, ['Cancelado', 'Devolvido', 'Cashout'])) {
                Palpite::where('aposta_id', $id)->update(['status' => 'Cancelado']);
            } elseif (in_array($newStatus, ['Ganhou', 'Perdeu'])) {
                Palpite::where('aposta_id', $id)->update(['status' => $newStatus]);
            }

            $user->save();
            if ($gerente) $gerente->save();

            DB::commit();
            return response()->json(['message' => 'sucesso'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'error', 'details' => $e->getMessage()], 500);
        }
    }
}
