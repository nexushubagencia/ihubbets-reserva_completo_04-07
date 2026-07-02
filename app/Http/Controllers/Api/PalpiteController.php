<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aposta;
use Illuminate\Support\Facades\Auth;

class PalpiteController extends Controller
{
    public function show($id)
    {
        $user = Auth::user();
        $siteId = config('tenant.site_id', 1);

        $aposta = Aposta::where('id', $id)
            ->where('site_id', $siteId)
            ->first();

        if (!$aposta) {
            return response()->json(['error' => 'Aposta não encontrada'], 404);
        }

        if ($aposta->modalidade == 'Loto') {
            return Aposta::where('id', $id)
                ->where('site_id', $siteId)
                ->with('palpitesLoto')
                ->get();
        }

        return Aposta::where('id', $id)
            ->where('site_id', $siteId)
            ->with('palpites')
            ->get();
    }
}
