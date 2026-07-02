<?php

namespace App\Http\Controllers\Gerente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lancamento;
use Illuminate\Support\Facades\DB;

class GerenteCaixaController extends Controller
{
    public function index()
    {
        $gerenteId = auth()->user()->id;

        $cambistas = User::where('gerente_id', $gerenteId)
            ->where('nivel', 'cambista')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($c) {
                $lancamentos = Lancamento::where('user_id', $c->id)->get();
                $entradas = $lancamentos->where('tipo', 'Crédito')->sum('valor');
                $saidas = $lancamentos->where('tipo', 'Débito')->sum('valor');

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'saldo_simples' => (float) $c->balance,
                    'saldo_casadinha' => (float) ($c->balance_bonus ?? 0),
                    'saldo_total' => (float) ($c->balance + ($c->balance_bonus ?? 0)),
                    'entradas' => (float) $entradas,
                    'saidas' => (float) $saidas,
                    'comissoes' => (float) $c->comissoes,
                    'lancamentos' => (float) $c->lancamentos,
                    'total' => (float) $c->entradas - (float) $c->saidas - (float) $c->comissoes,
                ];
            });

        $totalEntradas = $cambistas->sum('entradas');
        $totalSaidas = $cambistas->sum('saidas');
        $totalComissoes = $cambistas->sum('comissoes');
        $totalSaldo = $cambistas->sum('saldo_total');

        return view('gerente.caixa', compact(
            'cambistas',
            'totalEntradas',
            'totalSaidas',
            'totalComissoes',
            'totalSaldo'
        ));
    }

    public function show($id)
    {
        $gerenteId = auth()->user()->id;

        $cambista = User::where('id', $id)
            ->where('gerente_id', $gerenteId)
            ->where('nivel', 'cambista')
            ->firstOrFail();

        $lancamentos = Lancamento::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return view('gerente.caixa-detail', compact('cambista', 'lancamentos'));
    }
}
