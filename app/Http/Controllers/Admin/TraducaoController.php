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
            $query->where('texto_original', 'like', '%' . $request->busca . '%')
                  ->orWhere('texto_traduzido', 'like', '%' . $request->busca . '%');
        }

        $traducoes = $query->orderBy('id', 'desc')->paginate(30);

        return view('admin.traducoes', compact('traducoes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:liga,time',
            'texto_original' => 'required|string|max:255',
            'texto_traduzido' => 'required|string|max:255',
        ]);

        $traducao = Traducao::where('tipo', $request->tipo)
            ->where('texto_original', $request->texto_original)
            ->first();

        if ($traducao) {
            $traducao->update(['texto_traduzido' => $request->texto_traduzido]);
        } else {
            Traducao::create([
                'tipo' => $request->tipo,
                'texto_original' => $request->texto_original,
                'texto_traduzido' => $request->texto_traduzido,
            ]);
        }

        Cache::forget('traducoes_todas');

        return redirect()->back()->with('sucesso', 'Tradução salva com sucesso!');
    }

    public function destroy($id)
    {
        $traducao = Traducao::findOrFail($id);
        $traducao->delete();

        Cache::forget('traducoes_todas');

        return redirect()->back()->with('sucesso', 'Tradução removida com sucesso!');
    }
}
