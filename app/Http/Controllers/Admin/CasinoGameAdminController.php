<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Casino\CasinoCategory;
use App\Models\Casino\CasinoGame;
use App\Models\Casino\CasinoGamesKey;
use App\Models\Casino\CasinoOrder;
use App\Models\Casino\CasinoProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CasinoGameAdminController extends Controller
{
    public function games(Request $request)
    {
        $games = CasinoGame::with('provider')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($request->query());

        $providers = CasinoProvider::orderBy('name')->get();
        $categories = CasinoCategory::orderBy('name')->get();

        return view('admin.casino.games', compact('games', 'providers', 'categories'));
    }

    public function providers(Request $request)
    {
        $providers = CasinoProvider::orderBy('name')->paginate(50)->appends($request->query());
        return view('admin.casino.providers', compact('providers'));
    }

    public function categories(Request $request)
    {
        $categories = CasinoCategory::orderBy('name')->paginate(50)->appends($request->query());
        return view('admin.casino.categories', compact('categories'));
    }

    public function orders(Request $request)
    {
        $orders = CasinoOrder::with('user')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($request->query());

        return view('admin.casino.orders', compact('orders'));
    }

    public function keys(Request $request)
    {
        $keys = CasinoGamesKey::first();
        return view('admin.casino.keys', compact('keys'));
    }

    public function saveKeys(Request $request)
    {
        $data = $request->except(['_token']);

        try {
            CasinoGamesKey::updateOrCreate(['id' => 1], $data);
            return redirect()->route('admin.casino.keys')->with('success', 'Chaves salvas com sucesso!');
        } catch (\Exception $e) {
            Log::error('Casino save keys error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao salvar chaves.');
        }
    }
}
