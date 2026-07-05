<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConfigOdd;
use App\Models\ConfigMercados;

class OddsController extends Controller
{
    private $arr;

    public function __construct()
    {
        $this->arr = array();
    }

    public function indexView()
    {
        return view('admin.bloqueio-odd');
    }

    public function index(Request $request)
    {
        $siteId = app('tenant.site_id');
        return ConfigOdd::where('mercado_name', $request->mercado_name)
            ->where('site_id', $siteId)
            ->where('user_id', auth()->user()->id)
            ->orderBy('header', 'asc')
            ->get();
    }

    public function indexViewCambista()
    {
        return view('admin.bloqueio-odd-cambista');
    }

    public function oddsUser($id)
    {
        $siteId = app('tenant.site_id');

        $mercados = ConfigMercados::select('name')
            ->where('site_id', $siteId)
            ->where('user_id', $id)
            ->get();

        $i = 0;
        foreach ($mercados as $mercado) {
            $odds = ConfigOdd::where('mercado_name', $mercado->name)
                ->where('user_id', $id)
                ->where('site_id', $siteId)
                ->get();

            $this->arr[$i]['mercado'] = $mercado->name;
            $j = 0;
            foreach ($odds as $odd) {
                $this->arr[$i]['odds'][$j]['id'] = $odd->id;
                $this->arr[$i]['odds'][$j]['name'] = $odd->name;
                $this->arr[$i]['odds'][$j]['porcentagem'] = $odd->porcentagem;
                $j++;
            }
            $i++;
        }

        return $this->arr;
    }

    public function update(Request $request, $id)
    {
        $mercado = ConfigOdd::find($id);
        if (!$mercado) {
            return response()->json(['success' => false, 'message' => 'Odd não encontrada.'], 404);
        }
        $mercado->update($request->only([
            'mercado_name', 'user_id', 'site_id', 'porcentagem',
            'header', 'mercado_full_name', 'name', 'status'
        ]));
        return response()->json(['success' => true]);
    }
}
