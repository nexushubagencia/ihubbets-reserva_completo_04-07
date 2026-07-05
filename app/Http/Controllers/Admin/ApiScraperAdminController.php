<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuracao;

class ApiScraperAdminController extends Controller
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
        'Pênalti na Partida', 'Cartão Vermelho na Partida',
    ];

    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $config = Configuracao::where('site_id', $siteId)->first();

        $configFile = storage_path('app/scraper_markets.json');
        $activeMarkets = file_exists($configFile)
            ? (json_decode(file_get_contents($configFile), true) ?? self::ALL_MARKETS)
            : self::ALL_MARKETS;

        $allMarkets = self::ALL_MARKETS;

        $stats = [
            'total_matches' => \App\Models\MatchModel::where('site_id', $siteId)->where('sport_id', 1)->count(),
            'active_leagues' => \App\Models\ApifootballLeague::where('site_id', $siteId)->where('active', 1)->count(),
            'last_update' => $config->updated_at ?? 'N/A',
        ];

        return view('admin.scraper', compact('config', 'allMarkets', 'activeMarkets', 'stats'));
    }

    public function updateConfig(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $data = $request->validate([
            'scraper_mode' => 'required|in:master,client',
            'scraper_url' => 'nullable|url',
            'scraper_token' => 'nullable|string',
        ]);

        $config = Configuracao::where('site_id', $siteId)->first();
        if ($config) {
            $config->update($data);
        }

        return redirect()->back()->with('success', 'Configuração do Scraper atualizada!');
    }

    public function saveMarkets(Request $request)
    {
        $markets = $request->input('markets', []);
        $configFile = storage_path('app/scraper_markets.json');
        file_put_contents($configFile, json_encode($markets));

        return response()->json(['success' => true, 'message' => 'Filtro de mercados salvo!']);
    }

    public function startScraper()
    {
        try {
            $exitCode = \Artisan::call('scraper:start');
            $output = \Artisan::output();
            return redirect()->back()->with('success', 'Scraper iniciado!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao iniciar scraper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function stopScraper()
    {
        try {
            $exitCode = \Artisan::call('scraper:stop');
            $output = \Artisan::output();
            return redirect()->back()->with('success', 'Scraper parado!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao parar scraper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function syncNow()
    {
        try {
            $exitCode = \Artisan::call('scraper:sync-jogadinha');
            $output = \Artisan::output();
            return redirect()->back()->with('success', 'Sincronização concluída!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar scraper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function getLogs()
    {
        $logPath = storage_path('logs/apifootball.log');
        if (!file_exists($logPath)) {
            return response()->json(['logs' => 'Nenhum log encontrado. Execute o scraper primeiro.']);
        }

        $filesize = filesize($logPath);
        $offset = max(0, $filesize - 200000);
        $logs = file_get_contents($logPath, false, null, $offset);
        $logs = mb_convert_encoding($logs, 'UTF-8', 'UTF-8');

        return response()->json(['logs' => $logs]);
    }
}
