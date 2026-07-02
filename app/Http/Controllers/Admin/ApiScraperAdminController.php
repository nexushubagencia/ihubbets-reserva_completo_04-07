<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuracao;

class ApiScraperAdminController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $config = Configuracao::where('site_id', $siteId)->first();

        return view('admin.scraper', compact('config'));
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
            $config->update([
                'scraper_mode' => $data['scraper_mode'],
                'scraper_url' => $data['scraper_url'],
                'scraper_token' => $data['scraper_token'],
            ]);
        }

        return redirect()->back()->with('success', 'Configuração do Scraper atualizada com sucesso!');
    }

    public function startScraper()
    {
        try {
            $exitCode = \Artisan::call('scraper:start');
            $output = \Artisan::output();

            return redirect()->back()->with('success', 'Scraper iniciado com sucesso!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao iniciar scraper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao iniciar scraper: ' . $e->getMessage());
        }
    }

    public function stopScraper()
    {
        try {
            $exitCode = \Artisan::call('scraper:stop');
            $output = \Artisan::output();

            return redirect()->back()->with('success', 'Scraper parado com sucesso!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao parar scraper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao parar scraper: ' . $e->getMessage());
        }
    }

    public function syncNow()
    {
        try {
            $exitCode = \Artisan::call('scraper:sync-jogadinha');
            $output = \Artisan::output();

            return redirect()->back()->with('success', 'Sincronização iniciada com sucesso!' . ($output ? '<br><small>' . e($output) . '</small>' : ''));
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar scraper: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao sincronizar: ' . $e->getMessage());
        }
    }
}
