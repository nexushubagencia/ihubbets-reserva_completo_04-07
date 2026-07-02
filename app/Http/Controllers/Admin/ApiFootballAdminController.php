<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApifootballLeague;
use App\Models\SiteSetting;
use App\Services\ApiProviderService;

class ApiFootballAdminController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $leagues = ApifootballLeague::where('site_id', $siteId)->orderBy('country')->orderBy('name')->get();
        $apiKey = config('services.apifootball.api_key', '');
        $provider = app(ApiProviderService::class);
        $activeProvider = $provider->getActiveProvider($siteId);

        return view('admin.api-football', compact('leagues', 'apiKey', 'activeProvider'));
    }

    public function switchProvider(Request $request)
    {
        $request->validate(['provider' => 'required|in:api-football,bets-api']);

        $siteId = config('tenant.site_id', 1);
        $provider = app(ApiProviderService::class);
        $provider->setActiveProvider($request->provider, $siteId);

        return redirect()->back()->with('success', 'Provedor alterado para: ' . $provider->getProviderLabel());
    }

    public function updateLeagues(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $request->validate([
            'leagues' => 'required|array',
            'leagues.*' => 'integer|exists:apifootball_leagues,id',
        ]);

        $allLeagues = ApifootballLeague::where('site_id', $siteId)->get();

        foreach ($allLeagues as $league) {
            $league->update([
                'active' => in_array($league->id, $request->leagues),
            ]);
        }

        return redirect()->back()->with('success', 'Ligas atualizadas com sucesso!');
    }

    public function syncNow()
    {
        try {
            $exitCode = \Artisan::call('apifootball:insert_matches');
            $output = \Artisan::output();

            return redirect()->back()->with('success', 'Sincronização iniciada com sucesso!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar API-Football: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao iniciar sincronização: ' . $e->getMessage());
        }
    }
}
