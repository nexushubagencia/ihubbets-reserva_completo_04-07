<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aposta;
use App\Models\Configuracao;
use Illuminate\Support\Facades\DB;

class CashOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $user = auth()->user();

        $query = Aposta::where('site_id', $siteId)
            ->where('status', 'Aberto')
            ->with(['user', 'palpites'])
            ->latest();

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_bilhete', 'LIKE', "%{$busca}%")
                  ->orWhere('cliente', 'LIKE', "%{$busca}%")
                  ->orWhere('vendedor', 'LIKE', "%{$busca}%");
            });
        }

        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        $apostas = $query->paginate(20);

        $config = Configuracao::where('site_id', $siteId)->first();
        $taxa = $config->cash_out_taxa ?? 70;

        return view('admin.cash-out', compact('apostas', 'taxa'));
    }

    public function calcularCashOut(Request $request, $id)
    {
        $aposta = Aposta::findOrFail($id);
        $config = Configuracao::where('site_id', config('tenant.site_id', 1))->first();

        if (!$config || !$config->cash_out_ativo) {
            return response()->json(['error' => 'Cash Out desabilitado'], 422);
        }

        $taxa = $config->cash_out_taxa ?? 70;
        $cashOutAmount = ($aposta->valor_apostado * $taxa) / 100;

        return response()->json([
            'valor_apostado' => $aposta->valor_apostado,
            'retorno_possivel' => $aposta->retorno_possivel,
            'taxa_percentual' => $taxa,
            'cash_out_valor' => round($cashOutAmount, 2),
        ]);
    }

    public function executarCashOut(Request $request, $id)
    {
        $request->validate([
            'senha' => 'required|string',
        ]);

        $siteId = config('tenant.site_id', 1);
        $aposta = Aposta::findOrFail($id);
        $user = auth()->user();

        if ($aposta->status !== 'Aberto') {
            return response()->json(['error' => 'Aposta nao esta aberta'], 422);
        }

        $config = Configuracao::where('site_id', $siteId)->first();

        if (!$config || !$config->cash_out_ativo) {
            return response()->json(['error' => 'Cash Out desabilitado'], 422);
        }

        $senhaCorreta = $user->password;
        if (!\Illuminate\Support\Facades\Hash::check($request->senha, $senhaCorreta)) {
            return response()->json(['error' => 'Senha incorreta'], 422);
        }

        DB::beginTransaction();
        try {
            $taxa = $config->cash_out_taxa ?? 70;
            $cashOutAmount = ($aposta->valor_apostado * $taxa) / 100;

            $aposta->update([
                'status' => 'CashOut',
                'retorno_possivel' => $cashOutAmount,
            ]);

            if ($aposta->user) {
                $aposta->user->increment('saldo_simples', $cashOutAmount);
                $aposta->user->increment('saidas', $cashOutAmount);
            }

            if ($aposta->gerente_id) {
                $gerente = \App\Models\User::find($aposta->gerente_id);
                if ($gerente) {
                    $gerente->increment('entradas', $cashOutAmount);
                    $gerente->increment('comissoes', $cashOutAmount);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Cash Out de R$ " . number_format($cashOutAmount, 2, ',', '.') . " realizado!",
                'valor' => $cashOutAmount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erro ao processar Cash Out: ' . $e->getMessage()], 500);
        }
    }
}
