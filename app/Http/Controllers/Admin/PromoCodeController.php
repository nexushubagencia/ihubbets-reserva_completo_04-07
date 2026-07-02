<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromoCodeController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $coupons = DB::table('promocodes')->where('site_id', $siteId)->get();
        return view('admin.bonus', compact('coupons'));
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        
        $data = $request->validate([
            'code' => 'required|string|unique:promocodes,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_deposit' => 'nullable|numeric|min:0',
            'rollover' => 'required|integer|min:1',
            'min_odd' => 'required|numeric|min:1',
            'expires_at' => 'nullable|date',
        ]);

        $auth = auth()->user();
        
        // 🛡️ BLINDAGEM: Verifica se o gerente tem permissão para criar cupons
        if ($auth->nivel == 'gerente' && !$auth->can_create_coupons) {
            return redirect()->back()->with('error', 'Você não tem permissão para criar cupons. Solicite ao Admin.');
        }

        $managerId = ($auth->nivel == 'gerente') ? $auth->id : ($request->manager_id ?? null);

        DB::table('promocodes')->insert([
            'site_id' => $siteId,
            'manager_id' => $managerId,
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_deposit' => $data['min_deposit'] ?? 0,
            'rollover' => $data['rollover'],
            'min_odd' => $data['min_odd'],
            'expires_at' => $data['expires_at'],
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Cupom de bônus criado com sucesso!');
    }

    public function destroy($id)
    {
        DB::table('promocodes')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Cupom removido!');
    }
}
