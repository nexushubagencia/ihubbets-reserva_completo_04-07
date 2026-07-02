<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bonus;
use Illuminate\Support\Facades\DB;

class BonusController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $bonuses = Bonus::where('site_id', $siteId)->get();
        return view('admin.bonus', compact('bonuses'));
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        
        $data = $request->validate([
            'code' => 'required|string|unique:bonuses,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0.01',
            'min_deposit' => 'required|numeric|min:0',
            'rollover_multiplier' => 'required|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        $auth = auth()->user();

        // 🛡️ BLINDAGEM: Verifica se o gerente tem permissão para gerenciar bônus
        if ($auth->nivel == 'gerente' && !$auth->can_manage_bonuses) {
            return redirect()->back()->with('error', 'Você não tem permissão para gerenciar bônus. Solicite ao Admin.');
        }

        Bonus::create([
            'site_id' => $siteId,
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_deposit' => $data['min_deposit'],
            'rollover_multiplier' => $data['rollover_multiplier'],
            'expires_at' => $data['expires_at'],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Bônus criado com sucesso!');
    }

    public function destroy($id)
    {
        $siteId = config('tenant.site_id', 1);
        Bonus::where('id', $id)->where('site_id', $siteId)->delete();
        return redirect()->back()->with('success', 'Bônus removido!');
    }

    public function toggle($id)
    {
        $siteId = config('tenant.site_id', 1);
        $bonus = Bonus::where('id', $id)->where('site_id', $siteId)->firstOrFail();
        $bonus->update(['is_active' => !$bonus->is_active]);
        return redirect()->back()->with('success', 'Status do bônus atualizado!');
    }
}
