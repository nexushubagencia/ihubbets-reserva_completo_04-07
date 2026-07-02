<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\User;
use App\Models\Bet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GerenciadorController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'super_admin') {
                abort(403, 'Acesso restrito ao Administrador Global.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $totalSites = Site::count();
        $activeSites = Site::where('status', 'active')->count();
        $suspendedSites = Site::where('status', 'suspended')->count();
        
        $totalBets = Bet::withoutGlobalScope('site')->count();
        $totalVolume = Bet::withoutGlobalScope('site')->sum('amount');
        $expectedRevenue = Site::where('status', 'active')->sum('due_value');

        $billingStats = [
            'paid' => Site::where('billing_status', 'paid')->count(),
            'pending' => Site::where('billing_status', 'pending')->count(),
            'overdue' => Site::where('billing_status', 'overdue')->count(),
        ];

        $chartVolume = Bet::withoutGlobalScope('site')->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse();

        $topSites = Site::withCount(['bets' => function ($query) {
                $query->withoutGlobalScope('site');
            }])
            ->withSum(['bets' => function ($query) {
                $query->withoutGlobalScope('site');
            }], 'amount')
            ->orderBy('bets_sum_amount', 'desc')
            ->limit(5)
            ->get();

        $sites = Site::withCount([
            'bets' => function ($query) { $query->withoutGlobalScope('site'); }, 
            'users' => function ($query) { $query->withoutGlobalScope('site'); }
        ])->get();

        return view('admin.gerenciador.index', compact(
            'totalSites', 'activeSites', 'suspendedSites', 
            'totalBets', 'totalVolume', 'sites', 'expectedRevenue',
            'billingStats', 'chartVolume', 'topSites'
        ));
    }

    public function sites()
    {
        $sites = Site::latest()->get();
        return view('admin.gerenciador.sites', compact('sites'));
    }

    /**
     * Provisiona uma nova banca com um clique (Banca Base / Banca Mãe)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'domain' => 'required|string|max:191|unique:sites',
            'admin_email' => 'required|email',
        ]);

        DB::beginTransaction();
        try {
            // 1. Definição do Usuário (Automática ou Manual)
            $rawPassword = $request->admin_password ?? Str::random(10);
            $username = $request->username ?? Str::slug($request->name) . '_admin';

            // 2. Criar a Banca (Baseado na "Banca Mãe" / Defaults Premium)
            $site = Site::create([
                'uuid' => (string) Str::uuid(),
                'name' => $request->name,
                'domain' => $request->domain,
                'status' => 'active',
                'layout_theme' => $request->layout_theme ?? 'modern-dark',
                'primary_color' => $request->primary_color ?? '#1c3464',
                'secondary_color' => $request->secondary_color ?? '#2a4b8d',
                'due_value' => $request->due_value ?? 500.00,
                'billing_day' => $request->billing_day ?? 10,
                'billing_status' => 'paid',
                'next_due_date' => now()->addMonth()->setDay($request->billing_day ?? 10)
            ]);

            // 3. Criar as Configurações Iniciais de Tenancy (Sincronizado com o motor V2)
            DB::table('site_settings')->insert([
                'site_id' => $site->id,
                'aposta_ativa' => true,
                'min_bet_amount' => 2.00,
                'max_bet_amount' => 1000.00,
                'premio_max' => 20000.00,
                'cotacao_mini_bilhete' => 1.40,
                'quantidade_jogos_max_bilhete' => 25,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. Criar o Usuário Master da Banca
            $user = User::withoutGlobalScope('site')->create([
                'site_id' => $site->id,
                'name' => 'Adm ' . $site->name,
                'username' => $username,
                'email' => $request->admin_email,
                'password' => bcrypt($rawPassword),
                'role' => 'admin',
                'status' => 1,
                'balance' => 0.00
            ]);

            // 5. Inicializar Carteira (Wallet) do novo dono
            DB::table('wallets')->insert([
                'site_id' => $site->id,
                'user_id' => $user->id,
                'balance_real' => 0.00,
                'balance_bonus' => 0.00,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            // Retornar com as credenciais geradas
            return redirect()->back()->with('success_provision', [
                'message' => "Banca {$site->name} provisionada com sucesso!",
                'username' => $username,
                'password' => $rawPassword,
                'domain' => $site->domain,
                'admin_link' => "http://{$site->domain}/admin"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao provisionar banca: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $site = Site::findOrFail($id);
        $settings = DB::table('site_settings')->where('site_id', $id)->first();
        // Fallback case setting was missing
        if (!$settings) {
            DB::table('site_settings')->insert(['site_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
            $settings = DB::table('site_settings')->where('site_id', $id)->first();
        }
        return view('admin.gerenciador.edit', compact('site', 'settings'));
    }

    public function update(Request $request, $id)
    {
        $site = Site::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:191',
            'domain' => 'required|string|max:191|unique:sites,domain,'.$site->id,
            'due_value' => 'required|numeric',
            'billing_day' => 'required|integer',
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon_file' => 'nullable|image|mimes:png,ico|max:512'
        ]);

        // 1. Atualizar dados da Banca
        $site->update([
            'name' => $request->name,
            'domain' => $request->domain,
            'due_value' => $request->due_value,
            'billing_day' => $request->billing_day,
            'layout_theme' => $request->layout_theme,
            'active_custom_colors' => $request->has('active_custom_colors'),
            'custom_colors' => $request->has('custom_colors') ? json_encode($request->custom_colors) : null
        ]);

        // 2. Atualizar Configurações Extras (Logos)
        $updateSettings = [
            'updated_at' => now()
        ];

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('public/logos');
            $updateSettings['logo_path'] = str_replace('public/', 'storage/', $path);
        }

        if ($request->hasFile('favicon_file')) {
            $path = $request->file('favicon_file')->store('public/logos');
            $updateSettings['favicon_path'] = str_replace('public/', 'storage/', $path);
        }

        DB::table('site_settings')->where('site_id', $site->id)->update($updateSettings);

        // 3. Upload Rápido de Banners Múltiplos
        if ($request->hasFile('new_banners')) {
            \Log::info('Detectados novos banners para upload: ' . count($request->file('new_banners')));
            foreach ($request->file('new_banners') as $bannerFile) {
                try {
                    $bPath = $bannerFile->store('public/banners');
                    $storedPath = str_replace('public/', 'storage/', $bPath);
                    
                    DB::table('banners')->insert([
                        'site_id' => $site->id,
                        'image_path' => $storedPath,
                        'order_index' => 0,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    \Log::info('Banner salvo com sucesso: ' . $storedPath);
                } catch (\Exception $e) {
                    \Log::error('Erro ao salvar banner individual: ' . $e->getMessage());
                }
            }
        } else {
            \Log::warning('Nenhum arquivo encontrado no campo new_banners. Campos recebidos: ' . implode(', ', array_keys($request->all())));
        }

        return redirect()->route('admin.gerenciador.index')->with('success', 'Banca e Banners atualizados com sucesso pelo Gerenciador Master!');
    }

    public function toggleSiteStatus($id)
    {
        $site = Site::find($id);
        if (!$site) return redirect()->back()->with('error', 'Banca não encontrada.');

        $newStatus = ($site->status == 'active') ? 'suspended' : 'active';
        $site->status = $newStatus;
        $site->save();

        if ($newStatus == 'suspended') {
            User::withoutGlobalScope('site')->where('site_id', $site->id)->update(['status' => 0]);
        } else {
            User::withoutGlobalScope('site')->where('site_id', $site->id)->update(['status' => 1]);
        }

        return redirect()->back()->with('success', "Status da banca {$site->name} alterado para {$newStatus}.");
    }

    public function oddsCorrectionView()
    {
        return view('admin.gerenciador.odds_correction');
    }

    public function applyGlobalOddAdjustment(Request $request)
    {
        $data = $request->validate([
            'sport' => 'required|string',
            'league_name' => 'required|string',
            'adjustment_percent' => 'required|numeric'
        ]);

        DB::table('global_odd_adjustments')->updateOrInsert(
            ['sport' => $data['sport'], 'league_name' => $data['league_name']],
            ['adjustment_percent' => $data['adjustment_percent'], 'updated_at' => now()]
        );

        return redirect()->back()->with('success', 'Ajuste global de cotação aplicado com sucesso!');
    }

    public function downloadBackup($id)
    {
        $site = Site::findOrFail($id);
        
        // Coleta profunda de dados isolados por Site ID
        $backup = [
            'metadata' => [
                'site_name' => $site->name,
                'site_id' => $site->id,
                'domain' => $site->domain,
                'backup_date' => now()->toDateTimeString(),
                'version' => 'IHUB V2 - Multi-Tenant Pro'
            ],
            'site' => $site->toArray(),
            'settings' => DB::table('site_settings')->where('site_id', $id)->first(),
            'users' => User::withoutGlobalScope('site')->where('site_id', $id)->get()->toArray(),
            'bets' => Bet::withoutGlobalScope('site')
                        ->where('site_id', $id)
                        ->with(['items']) // Traz os itens do bilhete junto
                        ->get()->toArray(),
            'financial' => DB::table('transactions')
                            ->whereIn('user_id', function($query) use ($id) {
                                $query->select('id')->from('master_users')->where('site_id', $id);
                            })->get()->toArray(),
            'banners' => DB::table('banners')->where('site_id', $id)->get()->toArray(),
            'pages' => DB::table('site_pages')->where('site_id', $id)->get()->toArray(),
            'regions' => DB::table('regions')->where('site_id', $id)->get()->toArray(),
            'manual_events' => DB::table('manual_events')
                                ->where('site_id', $id)
                                ->get()->map(function($event) {
                                    $event->markets = DB::table('manual_markets')
                                        ->where('event_id', $event->id)
                                        ->get()->map(function($market) {
                                            $market->odds = DB::table('manual_odds')->where('market_id', $market->id)->get();
                                            return $market;
                                        });
                                    return $event;
                                })->toArray()
        ];

        $fileName = 'BACKUP_' . strtoupper(Str::slug($site->name)) . '_' . date('Ymd') . '.json';
        $json = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', "attachment; filename={$fileName}");
    }
}
