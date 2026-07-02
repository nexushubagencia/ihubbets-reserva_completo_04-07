<?php

namespace App\Http\Controllers\Gerente;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aposta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GerenteHomeController extends Controller
{
    public function index()
    {
        $gerenteId = auth()->user()->id;
        $hoje = Carbon::today();

        $cambistas = User::where('gerente_id', $gerenteId)->where('nivel', 'cambista')->get();

        $totalCambistas = $cambistas->count();

        $apostasHojeLegacy = Aposta::where('gerente_id', $gerenteId)
            ->whereDate('created_at', $hoje)
            ->where('tipo', '!=', 'Bolão')
            ->count();

        $apostasHojeOnline = DB::table('bets')
            ->where('manager_id', $gerenteId)
            ->whereDate('created_at', $hoje)
            ->where('status', '!=', 'cancelled')
            ->count();

        $totalApostasHoje = $apostasHojeLegacy + $apostasHojeOnline;

        $volumeHojeLegacy = Aposta::where('gerente_id', $gerenteId)
            ->whereDate('created_at', $hoje)
            ->where('tipo', '!=', 'Bolão')
            ->where('status', '!=', 'Cancelado')
            ->sum('valor_apostado');

        $volumeHojeOnline = DB::table('bets')
            ->where('manager_id', $gerenteId)
            ->whereDate('created_at', $hoje)
            ->where('status', '!=', 'cancelled')
            ->sum('amount');

        $volumeHoje = $volumeHojeLegacy + $volumeHojeOnline;

        $lucroRua = $cambistas->sum('entradas') - ($cambistas->sum('saidas') + $cambistas->sum('comissoes'));
        $comissaoGerente = ($lucroRua > 0) ? ($lucroRua * (auth()->user()->comissao_gerente / 100)) : 0;

        $comissaoOnline = DB::table('bets')
            ->where('manager_id', $gerenteId)
            ->where('status', '!=', 'cancelled')
            ->sum('manager_commission_amount');

        $minhaComissao = $comissaoGerente + $comissaoOnline;

        $ultimasApostas = Aposta::where('gerente_id', $gerenteId)
            ->with('user')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('gerente.home', compact(
            'totalCambistas',
            'totalApostasHoje',
            'volumeHoje',
            'minhaComissao',
            'cambistas',
            'ultimasApostas'
        ));
    }

    public function perfil()
    {
        $user = auth()->user();
        return view('gerente.perfil', compact('user'));
    }

    public function updatePerfil(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'contato' => 'nullable|string|max:255',
            'endereco' => 'nullable|string|max:255',
            'pix_key' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'contato', 'endereco', 'pix_key']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('gerente.perfil')->with('success', 'Perfil atualizado com sucesso!');
    }
}
