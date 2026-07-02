<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Aposta;

class GerenciamentoRiscos extends Controller
{
    public function viewGerenciamento()
    {
        return view('admin.gerenciamento-risco');
    }

    public function riscos(Request $request)
    {
        $siteId = app('tenant.site_id');

        if ($request->opcao == 'Possível Retorno') {
            return Aposta::where('status', 'Aberto')
                ->where('site_id', $siteId)
                ->where('modalidade', '!=', 'Loto')
                ->orderBy('retorno_possivel', 'desc')
                ->get();
        }

        if ($request->opcao == 'Quantida de Bilhetes') {
            return Aposta::where('status', 'Aberto')
                ->where('site_id', $siteId)
                ->where('modalidade', '!=', 'Loto')
                ->orderBy('total_palpites', 'desc')
                ->get();
        }

        if ($request->opcao == 'Valor Apostado') {
            return Aposta::where('status', 'Aberto')
                ->where('site_id', $siteId)
                ->where('modalidade', '!=', 'Loto')
                ->orderBy('valor_apostado', 'desc')
                ->get();
        }

        if ($request->opcao == 'Quantidade de Apostas em Aberto') {
            return Aposta::where('status', 'Aberto')
                ->where('site_id', $siteId)
                ->where('modalidade', '!=', 'Loto')
                ->orderBy('andamento_palpites', 'desc')
                ->get();
        }

        if ($request->opcao == 'Quntidade de Apostas no Bilhete') {
            return Aposta::where('status', 'Aberto')
                ->where('site_id', $siteId)
                ->where('modalidade', '!=', 'Loto')
                ->orderBy('total_palpites', 'desc')
                ->get();
        }
    }
}
