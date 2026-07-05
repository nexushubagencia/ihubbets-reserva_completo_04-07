<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Traducao;
use Illuminate\Support\Facades\Cache;

class TraducaoController extends Controller
{
    public function index(Request $request)
    {
        $query = Traducao::query();

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('texto_original', 'like', "%{$busca}%")
                  ->orWhere('texto_traduzido', 'like', "%{$busca}%")
                  ->orWhere('tipo', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $traducoes = $query->orderBy('id', 'desc')->paginate(50);

        return view('admin.traducoes', compact('traducoes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:liga,time',
            'texto_original' => 'required|string|max:255',
            'texto_traduzido' => 'required|string|max:255',
        ]);

        $siteId = config('tenant.site_id', 1);

        $traducao = Traducao::where('site_id', $siteId)
            ->where('tipo', $request->tipo)
            ->where('texto_original', $request->texto_original)
            ->first();

        if ($traducao) {
            $traducao->update(['texto_traduzido' => $request->texto_traduzido]);
        } else {
            Traducao::create([
                'site_id' => $siteId,
                'tipo' => $request->tipo,
                'texto_original' => $request->texto_original,
                'texto_traduzido' => $request->texto_traduzido,
            ]);
        }

        Cache::forget('traducoes_todas');

        return redirect()->back()->with('success', 'Tradução salva com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'texto_traduzido' => 'required|string|max:255',
        ]);

        $traducao = Traducao::findOrFail($id);
        $traducao->update(['texto_traduzido' => $request->texto_traduzido]);

        Cache::forget('traducoes_todas');

        return response()->json(['success' => true, 'message' => 'Tradução atualizada!']);
    }

    public function destroy($id)
    {
        $traducao = Traducao::findOrFail($id);
        $traducao->delete();

        Cache::forget('traducoes_todas');

        return redirect()->back()->with('success', 'Tradução removida com sucesso!');
    }

    public function importFromApi(Request $request)
    {
        $leagues = \App\Models\League::where('sport', 'football')->get();
        $count = 0;

        foreach ($leagues as $league) {
            $exists = Traducao::where('site_id', config('tenant.site_id', 1))
                ->where('tipo', 'liga')
                ->where('texto_original', $league->name)
                ->exists();

            if (!$exists) {
                Traducao::create([
                    'site_id' => config('tenant.site_id', 1),
                    'tipo' => 'liga',
                    'texto_original' => $league->name,
                    'texto_traduzido' => $league->name,
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "{$count} ligas importadas para tradução!");
    }
}
