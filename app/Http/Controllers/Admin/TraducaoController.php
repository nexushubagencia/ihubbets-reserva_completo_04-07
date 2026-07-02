<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Traducao;

class TraducaoController extends Controller
{
    public function index(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $query = Traducao::where('site_id', $siteId);

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('texto_original', 'LIKE', "%{$search}%")
                  ->orWhere('texto_traduzido', 'LIKE', "%{$search}%");
            });
        }

        $traducoes = $query->orderBy('tipo')->orderBy('texto_original')->paginate(50)->appends($request->query());
        $tipos = Traducao::where('site_id', $siteId)->distinct()->pluck('tipo')->sort()->values();

        return view('admin.traducoes', compact('traducoes', 'tipos'));
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $data = $request->validate([
            'tipo' => 'required|string|max:255',
            'texto_original' => 'required|string',
            'texto_traduzido' => 'required|string',
        ]);

        $data['site_id'] = $siteId;

        $traducao = Traducao::where('site_id', $siteId)
            ->where('tipo', $data['tipo'])
            ->where('texto_original', $data['texto_original'])
            ->first();

        if ($traducao) {
            $traducao->update(['texto_traduzido' => $data['texto_traduzido']]);
        } else {
            Traducao::create($data);
        }

        Traducao::limparCache($siteId);

        return redirect()->back()->with('success', 'Tradução salva com sucesso!');
    }

    public function destroy($id)
    {
        $siteId = config('tenant.site_id', 1);

        $traducao = Traducao::where('site_id', $siteId)->findOrFail($id);
        $traducao->delete();

        Traducao::limparCache($siteId);

        return redirect()->back()->with('success', 'Tradução removida com sucesso!');
    }
}
