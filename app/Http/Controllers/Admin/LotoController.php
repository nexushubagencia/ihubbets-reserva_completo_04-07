<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuinaTaxa;
use App\Models\SenaTaxa;
use App\Models\Aposta;
use App\Models\PalpiteLoto;
use App\Models\LotoResult;
use App\Models\BlockDayLoto;
use Illuminate\Http\Request;

class LotoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $concursosQuina = Aposta::where('modalidade', 'Loto')
            ->where('tipo', 'Quininha')
            ->where('status', 'Aberto')
            ->pluck('concurso')
            ->unique()
            ->sort()
            ->values();

        $concursosSena = Aposta::where('modalidade', 'Loto')
            ->where('tipo', 'Seninha')
            ->where('status', 'Aberto')
            ->pluck('concurso')
            ->unique()
            ->sort()
            ->values();

        $totalQuininha = Aposta::where('modalidade', 'Loto')->where('tipo', 'Quininha')->count();
        $totalSeninha = Aposta::where('modalidade', 'Loto')->where('tipo', 'Seninha')->count();
        $totalAbertos = Aposta::where('modalidade', 'Loto')->where('status', 'Aberto')->count();
        $totalGanhos = Aposta::where('modalidade', 'Loto')->where('status', 'Ganhou')->count();

        return view('admin.loto', compact(
            'concursosQuina', 'concursosSena',
            'totalQuininha', 'totalSeninha', 'totalAbertos', 'totalGanhos'
        ));
    }

    public function taxasQuininha()
    {
        $taxas = QuinaTaxa::orderBy('dezena', 'asc')->get();
        return view('admin.loto-taxas-quininha', compact('taxas'));
    }

    public function taxasSeninha()
    {
        $taxas = SenaTaxa::orderBy('dezena', 'asc')->get();
        return view('admin.loto-taxas-seninha', compact('taxas'));
    }

    public function updateTaxaQuina(Request $request, $id)
    {
        $taxa = QuinaTaxa::findOrFail($id);
        $taxa->update($request->only(['taxa']));
        return response()->json(['success' => true]);
    }

    public function updateStatusQuina($id)
    {
        $taxa = QuinaTaxa::findOrFail($id);
        $taxa->update(['status' => !$taxa->status]);
        return response()->json(['success' => true, 'status' => $taxa->status]);
    }

    public function updateTaxaSena(Request $request, $id)
    {
        $taxa = SenaTaxa::findOrFail($id);
        $taxa->update($request->only(['taxa']));
        return response()->json(['success' => true]);
    }

    public function updateStatusSena($id)
    {
        $taxa = SenaTaxa::findOrFail($id);
        $taxa->update(['status' => !$taxa->status]);
        return response()->json(['success' => true, 'status' => $taxa->status]);
    }

    public function listTaxasQuininha()
    {
        $taxas = QuinaTaxa::orderBy('dezena', 'asc')->get();
        return response()->json($taxas);
    }

    public function listTaxasSeninha()
    {
        $taxas = SenaTaxa::orderBy('dezena', 'asc')->get();
        return response()->json($taxas);
    }

    public function results()
    {
        $results = LotoResult::orderBy('data_sorteio', 'desc')->limit(50)->get();
        return view('admin.loto-results', compact('results'));
    }

    public function storeResult(Request $request)
    {
        $request->validate([
            'concurso' => 'required|string',
            'tipo' => 'required|in:Quina,Mega-Sena',
            'data_sorteio' => 'required|date',
            'dezenas' => 'required|array',
        ]);

        LotoResult::create($request->only('concurso', 'tipo', 'data_sorteio', 'dezenas'));

        return response()->json(['success' => true]);
    }

    public function apostasConcurso($tipo, $concurso)
    {
        $apostas = Aposta::where('modalidade', 'Loto')
            ->where('tipo', $tipo)
            ->where('concurso', $concurso)
            ->with(['palpitesLoto', 'user'])
            ->get();

        return response()->json($apostas);
    }

    public function blockDay(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        BlockDayLoto::create(['date' => $request->date]);
        return response()->json(['success' => true]);
    }

    public function unblockDay($id)
    {
        BlockDayLoto::destroy($id);
        return response()->json(['success' => true]);
    }

    public function blockedDays()
    {
        $days = BlockDayLoto::all();
        return response()->json($days);
    }
}
