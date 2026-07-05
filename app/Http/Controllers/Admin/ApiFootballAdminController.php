<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\ApifootballLeague;
use App\Models\SiteSetting;
use App\Services\ApiProviderService;

class ApiFootballAdminController extends Controller
{
    private const ALL_MARKETS = [
        'Vencedor do Encontro', 'Ambas Marcam', 'Dupla Chance', 'Gols Acima/Abaixo',
        'Empate Anula a Aposta', 'Gols Ímpar/Par', 'Resultado Exato', 'Número Exato de Gols',
        'Escanteios Acima/Abaixo', 'Escanteios 1x2', 'Cartões Acima/Abaixo', 'Cartões 1x2',
        'Handicap Asiático', 'Vencedor do Encontro (1T)', 'Gols Acima/Abaixo (1T)', 'Ambas Marcam (1T)',
        'Escanteios Acima/Abaixo (1T)', 'Escanteios 1x2 (1T)', 'Handicap Asiático (1T)',
        'Dupla Chance (1T)', 'Gols Ímpar/Par (1T)', 'Resultado Exato (1T)',
        'Vencedor do Encontro (2T)', 'Gols Acima/Abaixo (2T)', 'Ambas Marcam (2T)',
        'Escanteios Acima/Abaixo (2T)', 'Handicap Asiático (2T)', 'Dupla Chance (2T)',
        'Gols Ímpar/Par (2T)', 'Resultado Exato (2T)',
        'Intervalo / Final do Jogo', 'Tempo com Mais Gols', 'Primeiro Time a Marcar',
        'Último Time a Marcar', 'Time da Casa não sofre Gol', 'Time de Fora não sofre Gol',
        'Time da Casa Vence sem sofrer Gols', 'Time de Fora Vence sem sofrer Gols',
        'Pênalti na Partida', 'Cartão Vermelho na Partida',
    ];

    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $leagues = ApifootballLeague::where('site_id', $siteId)->orderBy('country')->orderBy('name')->get();
        $apiKey = config('services.apifootball.api_key', '');
        $provider = app(ApiProviderService::class);
        $activeProvider = $provider->getActiveProvider($siteId);

        $configFile = storage_path('app/api_markets.json');
        $activeMarkets = file_exists($configFile)
            ? (json_decode(file_get_contents($configFile), true) ?? self::ALL_MARKETS)
            : self::ALL_MARKETS;

        $allMarkets = self::ALL_MARKETS;

        return view('admin.api-football', compact('leagues', 'apiKey', 'activeProvider', 'allMarkets', 'activeMarkets'));
    }

    public function getStatus(Request $request)
    {
        $apiKey = config('services.apifootball.api_key', '');
        if (empty($apiKey)) {
            return response()->json([
                'status' => 'Não configurada',
                'requests_current' => 0,
                'requests_limit' => 0,
                'plan' => 'N/A',
            ]);
        }

        $sport = $request->input('sport', 'football');
        $sportUrls = config('services.apifootball.urls', []);
        $baseUrl = $sportUrls[$sport] ?? 'https://v3.football.api-sports.io';
        $url = rtrim($baseUrl, '/') . '/status';

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-apisports-key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['response']['account'])) {
                    $account = $data['response']['account'];
                    $requests = $data['response']['requests'];
                    return response()->json([
                        'status' => 'Ativa (' . ($account['plan'] ?? 'Free') . ')',
                        'requests_current' => $requests['current'] ?? 0,
                        'requests_limit' => $requests['limit_day'] ?? 100,
                        'plan' => $account['plan'] ?? 'Free',
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Erro ao consultar status API-Football: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'Chave Inválida',
            'requests_current' => 0,
            'requests_limit' => 0,
            'plan' => 'N/A',
        ]);
    }

    public function updateKey(Request $request)
    {
        $request->validate(['api_key' => 'required|string']);

        $envFile = base_path('.env');
        if (!File::exists($envFile)) {
            return redirect()->back()->with('error', 'Arquivo .env não encontrado.');
        }

        $content = file_get_contents($envFile);
        if (strpos($content, 'API_FOOTBALL_KEY=') !== false) {
            $content = preg_replace('/^API_FOOTBALL_KEY=.*/m', 'API_FOOTBALL_KEY=' . $request->input('api_key'), $content);
        } else {
            $content .= "\nAPI_FOOTBALL_KEY=" . $request->input('api_key') . "\n";
        }
        File::put($envFile, $content);

        return redirect()->back()->with('success', 'Chave da API atualizada com sucesso!');
    }

    public function saveMarkets(Request $request)
    {
        $markets = $request->input('markets', []);
        $configFile = storage_path('app/api_markets.json');
        file_put_contents($configFile, json_encode($markets));

        return response()->json(['success' => true, 'message' => 'Filtro de mercados salvo!']);
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
            $league->update(['active' => in_array($league->id, $request->leagues)]);
        }

        return redirect()->back()->with('success', 'Ligas atualizadas com sucesso!');
    }

    public function syncNow()
    {
        try {
            $exitCode = \Artisan::call('apifootball:insert_matches');
            $output = \Artisan::output();
            return redirect()->back()->with('success', 'Sincronização concluída!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar API-Football: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function runOdds(Request $request)
    {
        try {
            $sport = $request->input('sport', 'football');
            \Artisan::call('apifootball:update_odds', ['--sport' => $sport]);
            return redirect()->back()->with('success', 'Atualização de odds iniciada!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function runLive()
    {
        try {
            \Artisan::call('apifootball:live');
            return redirect()->back()->with('success', 'Atualização ao vivo iniciada!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function getLogs()
    {
        $logPath = storage_path('logs/apifootball_insert.log');
        if (!file_exists($logPath)) {
            return response()->json(['logs' => 'Nenhum log encontrado. Execute uma sincronização primeiro.']);
        }

        $filesize = filesize($logPath);
        $offset = max(0, $filesize - 200000);
        $logs = file_get_contents($logPath, false, null, $offset);
        $logs = mb_convert_encoding($logs, 'UTF-8', 'UTF-8');

        return response()->json(['logs' => $logs]);
    }
}
