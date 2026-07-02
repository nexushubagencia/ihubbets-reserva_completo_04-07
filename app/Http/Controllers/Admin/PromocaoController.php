<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promocao;

class PromocaoController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $promocoes = Promocao::where('site_id', $siteId)->orderBy('id', 'DESC')->get();

        return view('admin.promocoes.index', compact('promocoes'));
    }

    public function create()
    {
        return view('admin.promocoes.create');
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:porcentagem,valor_fixo',
            'porcentagem' => 'required_if:tipo,porcentagem|nullable|numeric|min:0',
            'valor_maximo' => 'required|numeric|min:0',
            'rollover_multiplicador' => 'required|numeric|min:1',
            'status' => 'required|boolean',
        ]);

        $data['site_id'] = $siteId;

        Promocao::create($data);

        return redirect()->route('admin.promocoes.index')->with('success', 'Promoção criada com sucesso!');
    }

    public function edit($id)
    {
        $siteId = config('tenant.site_id', 1);

        $promocao = Promocao::where('site_id', $siteId)->findOrFail($id);

        return view('admin.promocoes.edit', compact('promocao'));
    }

    public function update(Request $request, $id)
    {
        $siteId = config('tenant.site_id', 1);

        $promocao = Promocao::where('site_id', $siteId)->findOrFail($id);

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:porcentagem,valor_fixo',
            'porcentagem' => 'required_if:tipo,porcentagem|nullable|numeric|min:0',
            'valor_maximo' => 'required|numeric|min:0',
            'rollover_multiplicador' => 'required|numeric|min:1',
            'status' => 'required|boolean',
        ]);

        $promocao->update($data);

        return redirect()->route('admin.promocoes.index')->with('success', 'Promoção atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $siteId = config('tenant.site_id', 1);

        $promocao = Promocao::where('site_id', $siteId)->findOrFail($id);
        $promocao->delete();

        return redirect()->back()->with('success', 'Promoção removida com sucesso!');
    }
}
