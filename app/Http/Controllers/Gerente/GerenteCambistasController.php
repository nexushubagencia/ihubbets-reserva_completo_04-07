<?php

namespace App\Http\Controllers\Gerente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aposta;
use Illuminate\Support\Facades\DB;

class GerenteCambistasController extends Controller
{
    public function index()
    {
        $gerenteId = auth()->user()->id;

        $cambistas = User::where('gerente_id', $gerenteId)
            ->where('nivel', 'cambista')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'username' => $c->username,
                    'status' => $c->status,
                    'saldo_simples' => (float) $c->balance,
                    'saldo_casadinha' => (float) ($c->balance_bonus ?? 0),
                    'saldo_total' => (float) ($c->balance + ($c->balance_bonus ?? 0)),
                    'entradas' => (float) $c->entradas,
                    'saidas' => (float) $c->saidas,
                    'comissoes' => (float) $c->comissoes,
                    'quantidade_aposta' => (int) $c->quantidade_aposta,
                    'contato' => $c->contato,
                ];
            });

        return view('gerente.cambistas', compact('cambistas'));
    }

    public function show($id)
    {
        $gerenteId = auth()->user()->id;

        $cambista = User::where('id', $id)
            ->where('gerente_id', $gerenteId)
            ->where('nivel', 'cambista')
            ->firstOrFail();

        $ultimasApostas = Aposta::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $totalApostado = Aposta::where('user_id', $id)->where('status', '!=', 'Cancelado')->sum('valor_apostado');
        $totalPremios = Aposta::where('user_id', $id)->whereIn('status', ['Ganhou', 'Venceu'])->sum('retorno_possivel');

        return view('gerente.cambista-detail', compact('cambista', 'ultimasApostas', 'totalApostado', 'totalPremios'));
    }
}
