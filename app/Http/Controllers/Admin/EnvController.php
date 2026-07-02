<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuracao;

class EnvController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $config = Configuracao::where('site_id', $siteId)->first();

        return view('admin.configuracao', compact('config'));
    }

    public function update(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $data = $request->validate([
            'valor_mini_aposta' => 'required|numeric|min:0',
            'valor_max_aposta' => 'required|numeric|min:0',
            'menor_valor_loto' => 'required|numeric|min:0',
            'max_valor_loto' => 'required|numeric|min:0',
            'premio_max' => 'required|numeric|min:0',
            'cotacao_mini_bilhete' => 'required|numeric|min:0',
            'cotacao_max_bilhete' => 'required|numeric|min:0',
            'bloquear_odd_abaixo' => 'nullable|numeric|min:0',
            'travar_odd_acima' => 'nullable|numeric|min:0',
            'quantidade_jogos_mini_bilhete' => 'required|integer|min:1',
            'quantidade_jogos_max_bilhete' => 'required|integer|min:1',
            'quantidade_times_visitantes_mesmo_camp' => 'nullable|integer|min:0',
            'data_limite_jogos' => 'nullable|date',
            'min_deposit' => 'nullable|numeric|min:0',
            'max_deposit' => 'nullable|numeric|min:0',
            'min_withdrawal' => 'nullable|numeric|min:0',
            'max_withdrawal' => 'nullable|numeric|min:0',
            'withdrawal_limit_day' => 'nullable|numeric|min:0',
            'comissao_premio' => 'nullable|numeric|min:0',
            'max_bonus_conversion' => 'nullable|numeric|min:0',
        ]);

        $config = Configuracao::where('site_id', $siteId)->first();

        if ($config) {
            $config->update($data);
        }

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}
