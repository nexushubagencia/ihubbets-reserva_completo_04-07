<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MasterPanelController — Painel Master IHUB V2
 *
 * Visão do "Dono da Franquia" (Super Admin / Gerenciador).
 * Exibe performance financeira de TODAS as bancas (sites),
 * gestão de licenças, comissões de white-label e ranking de bancas.
 *
 * Equivalente à visão consolidada do sistema original em
 * ORIGINAL_SOURCE/gerenciador e ao painel demo.mybetserver.com.
 */
class MasterPanelController extends Controller
{

    /* ------------------------------------------------------------------ */
    /*  VIEWS                                                               */
    /* ------------------------------------------------------------------ */

    public function index()
    {
        $sites = DB::table('sites')->get();
        $totalSites = $sites->count();
        $activeSites = $sites->where('status', 'active')->count();
        
        $totalVolume = DB::table('bets')->sum('amount');
        $todayVolume = DB::table('bets')->whereDate('created_at', Carbon::today())->sum('amount');
        
        $billingStats = [
            'total_due' => $sites->sum('due_value'),
            'paid' => $sites->where('billing_status', 'paid')->count(),
            'pending' => $sites->where('billing_status', 'pending')->count(),
            'overdue' => $sites->where('billing_status', 'overdue')->count(),
        ];

        // Dados para gráfico de 7 dias (Volume Global)
        $chartVolume = DB::table('bets')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.master.dashboard', compact(
            'totalSites', 'activeSites', 'totalVolume', 'todayVolume', 'billingStats', 'chartVolume'
        ));
    }

    public function bancasView()
    {
        $sites = DB::table('sites')
            ->leftJoin('users', function($join) {
                $join->on('sites.id', '=', 'users.site_id')
                     ->whereIn('users.role', ['admin', 'manager', 'super_admin'])
                     ->whereRaw('users.id = (select min(id) from users as u where u.site_id = sites.id)');
            })
            ->select('sites.*', 'users.email as admin_email')
            ->orderBy('sites.id', 'asc')
            ->get();

        return view('admin.master.bancas', compact('sites'));
    }

    public function financeiraMasterView()
    {
        return view('admin.master.financeiro');
    }

    public function marketingView()
    {
        $templates = DB::table('banner_templates')->get();
        $assets = DB::table('banner_assets')->get();
        // Fontes populares do Google Fonts para o Studio
        $googleFonts = ['Roboto', 'Oswald', 'Montserrat', 'Bebas Neue', 'Anton', 'Kanit', 'Poppins'];
        
        return view('admin.master.marketing', compact('templates', 'assets', 'googleFonts'));
    }

    /**
     * Busca partidas para o Gerador de Jogos do Studio
     */
    public function getMatches(Request $request)
    {
        $search = $request->query('search');
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $query = DB::table('matchs')
            ->select('id', 'home', 'away', 'date as start_time', 'league', DB::raw('0 as is_manual'))
            ->whereDate('date', '>=', $date)
            ->where('date', '<=', Carbon::parse($date)->endOfDay())
            ->orderBy('date', 'asc')
            ->limit(100);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('home', 'like', "%{$search}%")
                  ->orWhere('away', 'like', "%{$search}%")
                  ->orWhere('league', 'like', "%{$search}%");
            });
        }

        return response()->json($query->get());
    }

    /**
     * Busca dados de um bilhete para o Studio
     */
    public function getTicketData($id)
    {
        try {
            // Busca a aposta (pode ser pelo ID ou Código)
            $aposta = DB::table('apostas')
                ->where('id', $id)
                ->orWhere('codigo_bilhete', $id)
                ->first();

            if (!$aposta) {
                return response()->json(['status' => 'error', 'message' => 'Bilhete não encontrado'], 404);
            }

            $palpites = DB::table('palpites')->where('aposta_id', $aposta->id)->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $aposta->id,
                    'codigo' => $aposta->codigo_bilhete,
                    'cliente' => $aposta->cliente ?? 'Cliente Especial',
                    'valor_apostado' => number_format($aposta->valor_apostado, 2, ',', '.'),
                    'retorno_possivel' => number_format($aposta->retorno_possivel, 2, ',', '.'),
                    'status' => $aposta->status,
                    'palpites' => $palpites->map(function($p) {
                        return [
                            'home' => $p->home_team,
                            'away' => $p->away_team,
                            'market' => $p->market_name,
                            'odd' => $p->odd,
                            'status' => $p->status
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Salva um template do Canvas (JSON)
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'layout_data' => 'required|json',
            'type' => 'required|string'
        ]);

        try {
            DB::table('banner_templates')->updateOrInsert(
                ['name' => $request->name],
                [
                    'type' => $request->type,
                    'layout_data' => $request->layout_data,
                    'background_path' => $request->background_path,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return response()->json(['status' => 'success', 'message' => 'Template salvo com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload de assets para o Studio (fundos, jogadores, logos, elementos)
     */
    public function uploadAsset(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120',
            'type' => 'required|in:background,player,icon,logo,element,sticker',
            'name' => 'required|string'
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . \Illuminate\Support\Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $path = 'assets/marketing/' . $request->type . 's';
            
            // Garantir que a pasta existe
            if (!file_exists(public_path('storage/' . $path))) {
                mkdir(public_path('storage/' . $path), 0755, true);
            }

            $file->move(public_path('storage/' . $path), $fileName);
            $finalPath = 'storage/' . $path . '/' . $fileName;

            $assetId = DB::table('banner_assets')->insertGetId([
                'name' => $request->name,
                'type' => $request->type,
                'file_path' => $finalPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'Elemento enviado com sucesso!', 
                'asset' => [
                    'id' => $assetId,
                    'name' => $request->name,
                    'type' => $request->type,
                    'file_path' => asset($finalPath)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove um asset do sistema
     */
    public function deleteAsset($id)
    {
        try {
            $asset = DB::table('banner_assets')->where('id', $id)->first();
            if (!$asset) return response()->json(['status' => 'error', 'message' => 'Asset não encontrado']);

            // Remover arquivo físico
            if (file_exists(public_path($asset->file_path))) {
                unlink(public_path($asset->file_path));
            }

            DB::table('banner_assets')->where('id', $id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Asset removido!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove um template do sistema
     */
    public function deleteTemplate($id)
    {
        try {
            DB::table('banner_templates')->where('id', $id)->delete();
            return response()->json(['status' => 'success', 'message' => 'Template removido!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Aplica um banner (Canvas Base64) para TODAS as bancas do sistema.
     */
    public function applyGlobalBanner(Request $request)
    {
        $request->validate(['image' => 'required|string']);

        try {
            $imageData = $request->image;
            $image = str_replace('data:image/png;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'global_banner_' . time() . '.png';
            $path = 'banners/' . $imageName;
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($image));
            $finalPath = 'storage/' . $path;

            // Replicar para TODOS os sites
            $sites = DB::table('sites')->pluck('id');
            foreach ($sites as $siteId) {
                DB::table('banners')->insert([
                    'site_id'    => $siteId,
                    'image_path' => $finalPath,
                    'title'      => 'Banner Global ' . date('d/m/Y'),
                    'link'       => '#',
                    'position'   => 'home_main',
                    'display_to' => 'all',
                    'status'     => 1,
                    'order'      => 0,
                    'order_index'=> 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['status' => 'success', 'message' => 'Marketing replicado com sucesso!', 'path' => asset($finalPath)]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  DADOS — DASHBOARD MASTER                                            */
    /* ------------------------------------------------------------------ */

    /**
     * KPIs consolidados de todas as bancas.
     */
    public function stats(Request $request)
    {
        $from = $request->query('from', Carbon::today()->format('Y-m-d'));
        $to   = $request->query('to',   Carbon::today()->format('Y-m-d'));

        $sites = DB::table('sites')->select('id', 'name', 'domain', 'status')->get();

        $consolidated = [];
        $totals = ['entradas' => 0, 'saidas' => 0, 'abertas' => 0, 'bancas' => count($sites)];

        foreach ($sites as $site) {
            $entradas = DB::table('bets')
                ->where('site_id', $site->id)
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
                ->sum('amount');

            $saidas = DB::table('bets')
                ->where('site_id', $site->id)
                ->where('status', 'won')
                ->whereBetween(DB::raw('DATE(updated_at)'), [$from, $to])
                ->sum('potential_payout');

            $abertas = DB::table('bets')
                ->where('site_id', $site->id)
                ->where('status', 'open')
                ->sum('amount');

            $lucro = $entradas - $saidas;

            $consolidated[] = [
                'site_id'  => $site->id,
                'name'     => $site->name,
                'domain'   => $site->domain,
                'status'   => $site->status,
                'entradas' => round($entradas, 2),
                'saidas'   => round($saidas, 2),
                'abertas'  => round($abertas, 2),
                'lucro'    => round($lucro, 2),
            ];

            $totals['entradas'] += $entradas;
            $totals['saidas']   += $saidas;
            $totals['abertas']  += $abertas;
        }

        // Ordenar por lucro desc
        usort($consolidated, fn($a, $b) => $b['lucro'] <=> $a['lucro']);

        return response()->json([
            'period'       => ['from' => $from, 'to' => $to],
            'totals'       => array_map('round', $totals),
            'lucro_total'  => round($totals['entradas'] - $totals['saidas'], 2),
            'bancas'       => $consolidated,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  GESTÃO DE BANCAS                                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Lista todas as bancas com status e contagens de usuários.
     */
    public function bancas()
    {
        $bancas = DB::table('sites')
            ->select('sites.*')
            ->selectRaw('(SELECT email FROM master_users WHERE site_id = sites.id AND role IN ("admin", "manager", "super_admin") ORDER BY id ASC LIMIT 1) AS admin_email')
            ->selectRaw('(SELECT COUNT(*) FROM master_users WHERE site_id = sites.id AND role = "seller") AS total_cambistas')
            ->selectRaw('(SELECT COUNT(*) FROM master_users WHERE site_id = sites.id AND role = "manager") AS total_gerentes')
            ->selectRaw('(SELECT COUNT(*) FROM bets WHERE site_id = sites.id AND status = "open") AS apostas_abertas')
            ->orderBy('sites.created_at', 'desc')
            ->get();

        return response()->json($bancas);
    }

    /**
     * Suspende ou reativa uma banca inteira.
     */
    public function toggleBanca(Request $request, int $siteId)
    {
        $site = DB::table('sites')->where('id', $siteId)->first();
        if (!$site) return response()->json(['error' => 'Banca não encontrada.'], 404);

        $newStatus = $site->status === 'active' ? 'suspended' : 'active';

        DB::table('sites')->where('id', $siteId)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        // Suspender todos os usuários da banca
        if ($newStatus === 'suspended') {
            DB::table('master_users')->where('site_id', $siteId)->update(['status' => 0]);
        } else {
            DB::table('master_users')->where('site_id', $siteId)->update(['status' => 1]);
        }

        DB::table('audit_logs')->insert([
            'site_id'     => $siteId,
            'user_id'     => auth()->id(),
            'action'      => 'TOGGLE_BANCA',
            'target_type' => 'sites',
            'target_id'   => $siteId,
            'new_values'  => json_encode(['status' => $newStatus]),
            'ip_address'  => $request->ip(),
            'created_at'  => now(),
        ]);

        return response()->json(['status' => $newStatus, 'message' => "Banca {$newStatus}."]);
    }

    /**
     * Cria uma nova banca / site white-label (Ported from old system).
     */
    public function criarBanca(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:191',
            'domain'      => 'required|string|max:191|unique:sites,domain',
            'admin_email' => 'required|email',
        ]);

        DB::beginTransaction();
        try {
            $rawPassword = $request->admin_password ?? \Illuminate\Support\Str::random(10);
            $username    = $request->username ?? \Illuminate\Support\Str::slug($request->name) . '_admin';

            // 1. Criar a Banca
            $siteId = DB::table('sites')->insertGetId([
                'uuid'           => \Illuminate\Support\Str::uuid(),
                'name'           => $request->name,
                'domain'         => $request->domain,
                'status'         => 'active',
                'due_value'      => $request->due_value ?? 500.00,
                'billing_day'    => $request->billing_day ?? 10,
                'billing_status' => 'paid',
                'next_due_date'  => now()->addMonth()->setDay($request->billing_day ?? 10),
                'active_affiliates'   => $request->has('active_affiliates') ? 1 : 0,
                'active_payments'     => $request->has('active_payments') ? 1 : 0,
                'active_mercado_pago' => $request->has('active_mercado_pago') ? 1 : 0,
                'active_loto'         => $request->has('active_loto') ? 1 : 0,
                'active_marketing'    => $request->has('active_marketing') ? 1 : 0,
                'active_bonus'        => $request->has('active_bonus') ? 1 : 0,
                'active_configuracoes'=> $request->has('active_configuracoes') ? 1 : 0,
                'active_riscos'       => $request->has('active_riscos') ? 1 : 0,
                'active_lancamentos'  => $request->has('active_lancamentos') ? 1 : 0,
                'active_extrato'      => $request->has('active_extrato') ? 1 : 0,
                'active_banner_generator' => $request->has('active_banner_generator') ? 1 : 0,
                'active_gateway_deposito' => $request->has('active_gateway_deposito') ? 1 : 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // 2. Criar as Configurações Iniciais
            DB::table('site_settings')->insert([
                'site_id'                      => $siteId,
                'aposta_ativa'                 => 1,
                'min_bet_amount'               => 1.00,
                'max_bet_amount'               => 1000.00,
                'max_payout'                   => 20000.00,
                'cotacao_mini_bilhete_mult'    => 1.40,
                'quantidade_jogos_max_bilhete' => 25,
                'created_at'                   => now(),
                'updated_at'                   => now(),
            ]);

            // 3. Criar o Usuário Admin da Banca
            $userId = DB::table('master_users')->insertGetId([
                'site_id'    => $siteId,
                'name'       => 'Adm ' . $request->name,
                'username'   => $username,
                'email'      => $request->admin_email,
                'password'   => bcrypt($rawPassword),
                'role'       => 'admin',
                'nivel'      => 'adm',
                'status'     => 1,
                'balance'    => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json([
                'status'   => 'success',
                'message'  => "Banca {$request->name} criada com sucesso!",
                'data'     => [
                    'username'   => $username,
                    'password'   => $rawPassword,
                    'admin_url'  => "http://{$request->domain}/admin"
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza dados de uma banca existente.
     */
    public function updateBanca(Request $request)
    {
        $request->validate([
            'site_id'     => 'required|exists:sites,id',
            'name'        => 'required|string|max:191',
            'domain'      => 'required|string|max:191',
            'admin_email' => 'required|email',
        ]);

        DB::beginTransaction();
        try {
            // 1. Atualizar a Banca
            DB::table('sites')->where('id', $request->site_id)->update([
                'name'          => $request->name,
                'domain'        => $request->domain,
                'due_value'     => $request->due_value ?? 0,
                'active_affiliates'   => $request->has('active_affiliates') ? 1 : 0,
                'active_payments'     => $request->has('active_payments') ? 1 : 0,
                'active_mercado_pago' => $request->has('active_mercado_pago') ? 1 : 0,
                'active_loto'         => $request->has('active_loto') ? 1 : 0,
                'active_marketing'    => $request->has('active_marketing') ? 1 : 0,
                'active_bonus'        => $request->has('active_bonus') ? 1 : 0,
                'active_configuracoes'=> $request->has('active_configuracoes') ? 1 : 0,
                'active_riscos'       => $request->has('active_riscos') ? 1 : 0,
                'active_lancamentos'  => $request->has('active_lancamentos') ? 1 : 0,
                'active_extrato'      => $request->has('active_extrato') ? 1 : 0,
                'active_banner_generator' => $request->has('active_banner_generator') ? 1 : 0,
                'active_gateway_deposito' => $request->has('active_gateway_deposito') ? 1 : 0,
                'updated_at'    => now(),
            ]);

            // 2. Atualizar Usuário Admin Principal
            // Pegamos o primeiro usuário com cargo de admin daquela banca
            $admin = DB::table('master_users')
                ->where('site_id', $request->site_id)
                ->whereIn('role', ['admin', 'manager', 'super_admin'])
                ->orderBy('id', 'asc')
                ->first();

            if ($admin) {
                $updateData = [
                    'email'      => $request->admin_email,
                    'updated_at' => now(),
                ];

                if (!empty($request->password)) {
                    $updateData['password'] = bcrypt($request->password);
                }

                DB::table('master_users')->where('id', $admin->id)->update($updateData);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => "Banca {$request->name} atualizada!"]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Backup completo de uma banca (Exportação JSON).
     */
    public function downloadBackup($id)
    {
        $site = DB::table('sites')->where('id', $id)->first();
        if (!$site) abort(404);
        
        $backup = [
            'metadata' => [
                'site_name'   => $site->name,
                'domain'      => $site->domain,
                'backup_date' => now()->toDateTimeString(),
                'version'     => 'Nexus Hub v2.1.0'
            ],
            'site'     => (array)$site,
            'settings' => DB::table('site_settings')->where('site_id', $id)->first(),
            'users'    => DB::table('master_users')->where('site_id', $id)->get()->toArray(),
            'bets'     => DB::table('bets')->where('site_id', $id)->get()->toArray(),
        ];

        $fileName = 'BACKUP_' . strtoupper(\Illuminate\Support\Str::slug($site->name)) . '_' . date('Ymd') . '.json';
        return response()->json($backup)
                ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    /* ------------------------------------------------------------------ */
    /*  RANKING DE BANCAS                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Top 10 bancas por faturamento no período.
     */
    public function ranking(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->query('to',   Carbon::today()->format('Y-m-d'));

        $ranking = DB::table('bets')
            ->whereBetween(DB::raw('DATE(bets.created_at)'), [$from, $to])
            ->join('sites', 'bets.site_id', '=', 'sites.id')
            ->groupBy('bets.site_id', 'sites.name', 'sites.domain')
            ->select(
                'sites.name',
                'sites.domain',
                DB::raw('SUM(bets.amount) as faturamento'),
                DB::raw('COUNT(bets.id) as total_apostas'),
                DB::raw('SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END) as total_pago')
            )
            ->orderByDesc('faturamento')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $row->lucro = round($row->faturamento - $row->total_pago, 2);
                $row->margem = $row->faturamento > 0
                    ? round(($row->lucro / $row->faturamento) * 100, 1) . '%'
                    : '0%';
                return $row;
            });

        return response()->json($ranking);
    }

    /* ------------------------------------------------------------------ */
    /*  FECHAMENTO FINANCEIRO GLOBAL                                        */
    /* ------------------------------------------------------------------ */

    /**
     * Extrato financeiro consolidado de todas as bancas por período.
     */
    public function extratoGlobal(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->query('to',   Carbon::today()->format('Y-m-d'));

        $data = DB::table('bets')
            ->whereBetween(DB::raw('DATE(bets.created_at)'), [$from, $to])
            ->join('sites', 'bets.site_id', '=', 'sites.id')
            ->groupBy('bets.site_id', 'sites.name')
            ->select(
                'sites.name',
                DB::raw('SUM(bets.amount) as entradas'),
                DB::raw('SUM(CASE WHEN bets.status = "won" THEN bets.potential_payout ELSE 0 END) as saidas'),
                DB::raw('SUM(CASE WHEN bets.status = "open" THEN bets.amount ELSE 0 END) as em_risco'),
                DB::raw('COUNT(bets.id) as qtd_apostas')
            )
            ->get()
            ->map(fn($r) => array_merge((array)$r, [
                'lucro'  => round($r->entradas - $r->saidas, 2),
                'margem' => $r->entradas > 0
                    ? round((($r->entradas - $r->saidas) / $r->entradas) * 100, 1) . '%'
                    : '0%',
            ]));

        return response()->json([
            'period' => compact('from', 'to'),
            'data'   => $data,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  GERENCIADOR MASTER DE TEMAS                                        */
    /* ------------------------------------------------------------------ */

    /**
     * Lista todos os temas globais.
     */
    public function temasView()
    {
        $themes = \App\Models\GlobalTheme::orderBy('is_base', 'desc')->orderBy('name', 'asc')->get();
        $bancas = DB::table('sites')->select('id', 'name', 'layout_theme')->get();
        return view('admin.master.temas', compact('themes', 'bancas'));
    }

    /**
     * Form para criar novo tema.
     */
    public function createTemaView()
    {
        $theme = null; // Novo tema
        $sections = $this->getThemeSections();
        return view('admin.master.tema-editor', compact('theme', 'sections'));
    }

    /**
     * Form para editar tema existente.
     */
    public function editTemaView($id)
    {
        $theme = \App\Models\GlobalTheme::findOrFail($id);
        $sections = $this->getThemeSections($theme->colors);
        return view('admin.master.tema-editor', compact('theme', 'sections'));
    }

    /**
     * Salva um novo tema.
     */
    public function storeTema(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $colors = $request->except(['_token', 'name', 'slug']);

        $theme = new \App\Models\GlobalTheme();
        $theme->name = $request->name;
        $theme->slug = \Illuminate\Support\Str::slug($request->name);
        $theme->colors = $colors;
        $theme->is_active = true;
        $theme->is_base = false;
        $theme->save();

        // Limpar cache de TODOS os sites
        $this->clearAllThemeCache();

        return redirect()->route('admin.master.temas')->with('success', "Tema '{$request->name}' criado com sucesso!");
    }

    /**
     * Atualiza tema existente.
     */
    public function updateTema(Request $request, $id)
    {
        $theme = \App\Models\GlobalTheme::findOrFail($id);
        
        $colors = $request->except(['_token', '_method', 'name', 'slug']);

        $theme->name = $request->name ?? $theme->name;
        $theme->colors = $colors;
        $theme->save();

        // Limpar cache de TODOS os sites que usam este tema
        $this->clearAllThemeCache();

        return redirect()->route('admin.master.temas')->with('success', "Tema '{$theme->name}' atualizado com sucesso!");
    }

    /**
     * Exclui um tema (protege os base se quiser).
     */
    public function deleteTema($id)
    {
        $theme = \App\Models\GlobalTheme::findOrFail($id);
        
        // Verificar se alguma banca usa esse tema
        $inUse = DB::table('sites')->where('layout_theme', $theme->slug)->count();
        if ($inUse > 0) {
            return redirect()->route('admin.master.temas')
                ->with('error', "Este tema está em uso por {$inUse} banca(s). Troque o tema das bancas primeiro.");
        }

        $themeName = $theme->name;
        $theme->delete();
        
        $this->clearAllThemeCache();

        return redirect()->route('admin.master.temas')->with('success', "Tema '{$themeName}' excluído.");
    }

    /**
     * Duplica um tema existente.
     */
    public function duplicateTema($id)
    {
        $original = \App\Models\GlobalTheme::findOrFail($id);
        
        $newTheme = new \App\Models\GlobalTheme();
        $newTheme->name = $original->name . ' (Cópia)';
        $newTheme->slug = $original->slug . '-copy-' . time();
        $newTheme->colors = $original->colors;
        $newTheme->is_active = true;
        $newTheme->is_base = false;
        $newTheme->save();

        return redirect()->route('admin.master.temas')->with('success', "Tema duplicado: '{$newTheme->name}'");
    }

    /**
     * Limpa cache de CSS de todos os sites.
     */
    private function clearAllThemeCache()
    {
        $siteIds = DB::table('sites')->pluck('id');
        foreach ($siteIds as $id) {
            \Illuminate\Support\Facades\Cache::forget("tenant_css_{$id}");
        }
    }

    /**
     * Define TODAS as seções editáveis e seus campos.
     * Este é o "blueprint" do editor — TUDO que existe no frontend deve estar aqui.
     */
    private function getThemeSections($currentColors = [])
    {
        $c = function($field, $default) use ($currentColors) {
            return $currentColors[$field] ?? $default;
        };

        return [
            'cores_gerais' => [
                'title' => '🎨 Cores Gerais',
                'icon' => 'fas fa-palette',
                'description' => 'Cores base que definem a identidade visual do tema.',
                'fields' => [
                    ['field' => 'primary_color', 'label' => 'Cor Primária', 'hint' => 'Cor principal do tema (headers, botões ativos, links).', 'value' => $c('primary_color', '#35aa71')],
                    ['field' => 'header_bg_color', 'label' => 'Fundo do Header (Barra Topo)', 'hint' => 'Cor de fundo da barra superior do site.', 'value' => $c('header_bg_color', $c('sidebar_color', '#173133'))],
                    ['field' => 'header_logo_text_color', 'label' => 'Cor Texto Logo (IHUB BETS)', 'hint' => 'Cor do nome do site no canto superior esquerdo.', 'value' => $c('header_logo_text_color', '#ffffff')],
                    ['field' => 'header_hamburger_color', 'label' => 'Cor Botão Hamburger (≡)', 'hint' => 'Cor de fundo do botão de menu≡ ao lado da logo.', 'value' => $c('header_hamburger_color', '#162b2d')],
                    ['field' => 'header_hamburger_hover_color', 'label' => 'Hover Botão Hamburger', 'hint' => 'Cor ao passar o mouse no≡.', 'value' => $c('header_hamburger_hover_color', '#0f2022')],
                    ['field' => 'background_color', 'label' => 'Fundo do Site', 'hint' => 'Cor de fundo geral de todas as páginas.', 'value' => $c('background_color', '#f9f9f9')],
                    ['field' => 'border_color', 'label' => 'Bordas e Linhas', 'hint' => 'Divisores entre blocos e tabelas.', 'value' => $c('border_color', '#dddddd')],
                    ['field' => 'game_container_color', 'label' => 'Fundo Container Jogos', 'hint' => 'Cor de fundo das listas de jogos.', 'value' => $c('game_container_color', '#ffffff')],
                ],
            ],
            'sidebar' => [
                'title' => '📋 Menu Lateral (Sidebar)',
                'icon' => 'fas fa-columns',
                'description' => 'Menu esquerdo com ligas, especiais, e navegação.',
                'fields' => [
                    ['field' => 'sidebar_color', 'label' => 'Fundo Sidebar', 'hint' => 'Cor de fundo do menu lateral.', 'value' => $c('sidebar_color', '#173133')],
                    ['field' => 'sidebar_text_color', 'label' => 'Texto Sidebar', 'hint' => 'Cor dos links e ícones do menu.', 'value' => $c('sidebar_text_color', '#ffffff')],
                    ['field' => 'sidebar_header_color', 'label' => 'Faixas do Menu (Headers)', 'hint' => 'Fundo de MENU PRINCIPAL, LIGAS, etc.', 'value' => $c('sidebar_header_color', '#35aa71')],
                    ['field' => 'sidebar_header_text_color', 'label' => 'Texto das Faixas', 'hint' => 'Cor do texto MENU PRINCIPAL, etc.', 'value' => $c('sidebar_header_text_color', '#ffffff')],
                    ['field' => 'logo_container_color', 'label' => 'Container da Logo', 'hint' => 'Topo da sidebar atrás da logo.', 'value' => $c('logo_container_color', '#173133')],
                    ['field' => 'menu_hover_color', 'label' => 'Hover Menu (Fundo)', 'hint' => 'Cor ao passar o mouse nos itens.', 'value' => $c('menu_hover_color', '#33a26c')],
                    ['field' => 'menu_hover_text_color', 'label' => 'Hover Menu (Texto)', 'hint' => 'Texto ao passar o mouse.', 'value' => $c('menu_hover_text_color', '#ffffff')],
                    ['field' => 'menu_button_color', 'label' => 'Botão Menu Principal', 'hint' => 'Fundo dos itens FUTEBOL, BASQUETE...', 'value' => $c('menu_button_color', 'transparent')],
                    ['field' => 'menu_item_active_bg_color', 'label' => 'Item Ativo (Fundo)', 'hint' => 'Destaque do item selecionado na sidebar.', 'value' => $c('menu_item_active_bg_color', '#35aa71')],
                    ['field' => 'menu_item_active_text_color', 'label' => 'Item Ativo (Texto)', 'hint' => 'Texto do item selecionado.', 'value' => $c('menu_item_active_text_color', '#ffffff')],
                ],
            ],
            'esportes' => [
                'title' => '🏅 Abas de Esportes',
                'icon' => 'fas fa-th-large',
                'description' => 'Barra de navegação FUTEBOL, LUTA, BASQUETE, TÊNIS, VOLEI.',
                'fields' => [
                    ['field' => 'modalidade_ativa_color', 'label' => 'Aba Ativa (Fundo)', 'hint' => 'Fundo da aba do esporte selecionado (ex: FUTEBOL).', 'value' => $c('modalidade_ativa_color', '#35aa71')],
                    ['field' => 'modalidade_ativa_text_color', 'label' => 'Aba Ativa (Texto)', 'hint' => 'Cor do texto da aba selecionada.', 'value' => $c('modalidade_ativa_text_color', '#ffffff')],
                ],
            ],
            'busca' => [
                'title' => '🔍 Barra de Pesquisa',
                'icon' => 'fas fa-search',
                'description' => 'Campo de busca de jogos, times e ligas.',
                'fields' => [
                    ['field' => 'search_bar_bg_color', 'label' => 'Fundo da Busca', 'hint' => 'Cor do campo de pesquisa.', 'value' => $c('search_bar_bg_color', '#ffffff')],
                    ['field' => 'search_bar_text_color', 'label' => 'Texto da Busca', 'hint' => 'Cor do texto digitado.', 'value' => $c('search_bar_text_color', '#333333')],
                    ['field' => 'search_icon_bg_color', 'label' => 'Ícone Pesquisa (Lupa)', 'hint' => 'Cor de fundo da lupa.', 'value' => $c('search_icon_bg_color', '#35aa71')],
                    ['field' => 'search_icon_text_color', 'label' => 'Ícone Pesquisa (Cor)', 'hint' => 'Cor do ícone da lupa.', 'value' => $c('search_icon_text_color', '#ffffff')],
                ],
            ],
            'jogos' => [
                'title' => '⚽ Listagem de Jogos',
                'icon' => 'fas fa-futbol',
                'description' => 'Cabeçalho de campeonatos e nomes dos times.',
                'fields' => [
                    ['field' => 'card_header_bg_color', 'label' => 'Topo da Liga', 'hint' => 'Fundo da barra do campeonato (ex: Premier League).', 'value' => $c('card_header_bg_color', '#35aa71')],
                    ['field' => 'card_header_text_color', 'label' => 'Texto da Liga', 'hint' => 'Nome do campeonato.', 'value' => $c('card_header_text_color', '#ffffff')],
                    ['field' => 'team_name_text_color', 'label' => 'Cor Nome dos Times', 'hint' => 'Cor do texto dos nomes dos times (ex: Flamengo).', 'value' => $c('team_name_text_color', '#333333')],
                ],
            ],
            'odds' => [
                'title' => '📊 Cotações (Odds)',
                'icon' => 'fas fa-chart-line',
                'description' => 'Botões de odds C/E/F, seleção, compartilhar e +mercados.',
                'fields' => [
                    ['field' => 'odd_button_bg_color', 'label' => 'Fundo Cotação (C/E/F)', 'hint' => 'Fundo dos botões de cotação.', 'value' => $c('odd_button_bg_color', '#ffffff')],
                    ['field' => 'odd_button_text_color', 'label' => 'Texto Cotação (C/E/F)', 'hint' => 'Cor da letra e valor da odd.', 'value' => $c('odd_button_text_color', '#333333')],
                    ['field' => 'odd_button_hover_bg_color', 'label' => 'Fundo Cotação Selecionada', 'hint' => 'Cor de fundo quando a cotação é clicada.', 'value' => $c('odd_button_hover_bg_color', '#35aa71')],
                    ['field' => 'odd_button_hover_text_color', 'label' => 'Texto Cotação Selecionada', 'hint' => 'Cor do texto quando clicada.', 'value' => $c('odd_button_hover_text_color', '#ffffff')],
                    ['field' => 'button_selected_color', 'label' => 'Borda Cotação Ativa', 'hint' => 'Contorno da odd ativada.', 'value' => $c('button_selected_color', '#0692bc')],
                    ['field' => 'button_selected_border_color', 'label' => 'Borda Extra Cotação Ativa', 'hint' => 'Contorno adicional da odd selecionada.', 'value' => $c('button_selected_border_color', '#047a9e')],
                    ['field' => 'odds_plus_button_color', 'label' => 'Botão + Mercados', 'hint' => 'Botão de mais mercados (+0).', 'value' => $c('odds_plus_button_color', '#1aa6d0')],
                    ['field' => 'odds_plus_button_hover_color', 'label' => 'Hover + Mercados', 'hint' => 'Hover do botão + mercados.', 'value' => $c('odds_plus_button_hover_color', '#1590b8')],
                    ['field' => 'share_button_bg_color', 'label' => 'Botão Compartilhar (Fundo)', 'hint' => 'Fundo do ícone de foto/banner.', 'value' => $c('share_button_bg_color', '#1aa6d0')],
                    ['field' => 'share_button_icon_color', 'label' => 'Botão Compartilhar (Ícone)', 'hint' => 'Cor do ícone de compartilhar.', 'value' => $c('share_button_icon_color', '#ffffff')],
                ],
            ],
            'ao_vivo' => [
                'title' => '🔴 AO VIVO',
                'icon' => 'fas fa-broadcast-tower',
                'description' => 'Indicador de jogos ao vivo e placar.',
                'fields' => [
                    ['field' => 'live_color', 'label' => 'Cor AO VIVO', 'hint' => 'Cor do badge "AO VIVO", placar e borda da partida.', 'value' => $c('live_color', '#cc3333')],
                ],
            ],
            'destaque' => [
                'title' => '🏆 Jogos em Destaque',
                'icon' => 'fas fa-star',
                'description' => 'Carrossel e destaques no topo da página.',
                'fields' => [
                    ['field' => 'destaque_header_bg_color', 'label' => 'Topo Destaque', 'hint' => 'Cabeçalho do bloco.', 'value' => $c('destaque_header_bg_color', '#35aa71')],
                    ['field' => 'destaque_header_text_color', 'label' => 'Texto Topo', 'hint' => 'Título JOGOS EM DESTAQUE.', 'value' => $c('destaque_header_text_color', '#ffffff')],
                    ['field' => 'destaque_btn_bg_color', 'label' => 'Botão Apostar Agora', 'hint' => 'Botão dentro do card destaque.', 'value' => $c('destaque_btn_bg_color', '#1aa6d0')],
                    ['field' => 'destaque_btn_text_color', 'label' => 'Texto Apostar Agora', 'hint' => 'Cor do texto do botão.', 'value' => $c('destaque_btn_text_color', '#ffffff')],
                ],
            ],
            'cupom' => [
                'title' => '🎫 Cupom / Bilhete de Apostas',
                'icon' => 'fas fa-ticket-alt',
                'description' => 'Bilhete lateral com valores, botão Apostar e cabeçalho.',
                'fields' => [
                    ['field' => 'cupom_header_color', 'label' => 'Topo do Cupom', 'hint' => 'Cabeçalho do bilhete.', 'value' => $c('cupom_header_color', '#173133')],
                    ['field' => 'cupom_body_bg_color', 'label' => 'Fundo do Cupom', 'hint' => 'Cor de fundo do corpo do bilhete.', 'value' => $c('cupom_body_bg_color', '#ffffff')],
                    ['field' => 'cupom_valor_btn_color', 'label' => 'Botões de Valor (5, 10, 20...)', 'hint' => 'Botões rápidos de valor.', 'value' => $c('cupom_valor_btn_color', '#1aa6d0')],
                    ['field' => 'cupom_valor_btn_text_color', 'label' => 'Texto Botões Valor', 'hint' => 'Cor do texto dos botões de valor.', 'value' => $c('cupom_valor_btn_text_color', '#ffffff')],
                    ['field' => 'cupom_valor_btn_hover_color', 'label' => 'Hover Botões Valor', 'hint' => 'Ao selecionar um valor.', 'value' => $c('cupom_valor_btn_hover_color', '#0692bc')],
                    ['field' => 'cupom_apostar_btn_color', 'label' => 'Botão FINALIZAR APOSTA', 'hint' => 'Botão de concluir aposta.', 'value' => $c('cupom_apostar_btn_color', '#35aa71')],
                    ['field' => 'cupom_apostar_btn_hover_color', 'label' => 'Hover FINALIZAR', 'hint' => 'Mouse sobre o botão.', 'value' => $c('cupom_apostar_btn_hover_color', '#21965d')],
                    ['field' => 'cupom_trash_color', 'label' => 'Ícone Lixeira', 'hint' => 'Cor do ícone de remover item do cupom.', 'value' => $c('cupom_trash_color', '#ff0000')],
                    ['field' => 'ticket_consult_bg_color', 'label' => 'Fundo Consulta Bilhete', 'hint' => 'Cor do campo de busca de bilhetes.', 'value' => $c('ticket_consult_bg_color', '#173133')],
                ],
            ],
            'botoes_auth' => [
                'title' => '🔘 Botões de Ação',
                'icon' => 'fas fa-sign-in-alt',
                'description' => 'Botões Entrar, Cadastrar, e ações gerais.',
                'fields' => [
                    ['field' => 'btn_entrar_color', 'label' => 'Fundo Botão Entrar', 'hint' => 'Cor do botão de login.', 'value' => $c('btn_entrar_color', '#173133')],
                    ['field' => 'btn_entrar_text_color', 'label' => 'Texto Entrar', 'hint' => 'Cor da palavra ENTRAR.', 'value' => $c('btn_entrar_text_color', '#ffffff')],
                    ['field' => 'btn_entrar_hover_color', 'label' => 'Hover Botão Entrar', 'hint' => 'Cor ao passar o mouse no ENTRAR.', 'value' => $c('btn_entrar_hover_color', '#0f2628')],
                    ['field' => 'btn_cadastrar_color', 'label' => 'Fundo Cadastrar', 'hint' => 'Cor do botão Cadastre-se.', 'value' => $c('btn_cadastrar_color', '#074b34')],
                    ['field' => 'btn_cadastrar_text_color', 'label' => 'Texto Cadastrar', 'hint' => 'Cor da palavra CADASTRE-SE.', 'value' => $c('btn_cadastrar_text_color', '#ffffff')],
                    ['field' => 'btn_cadastrar_hover_color', 'label' => 'Hover Cadastrar', 'hint' => 'Cor ao passar o mouse no CADASTRE-SE.', 'value' => $c('btn_cadastrar_hover_color', '#053a28')],
                    ['field' => 'btn_login_border_color', 'label' => 'Borda dos Botões', 'hint' => 'Contorno dos botões superiores.', 'value' => $c('btn_login_border_color', '#35aa71')],
                    ['field' => 'btn_primary_text_color', 'label' => 'Texto Botão Primário', 'hint' => 'Texto genérico em botões primários.', 'value' => $c('btn_primary_text_color', '#ffffff')],
                    ['field' => 'action_button_color', 'label' => 'Botão de Ação', 'hint' => 'Botões de formulários e salvamento.', 'value' => $c('action_button_color', '#1aa6d0')],
                    ['field' => 'action_button_hover_color', 'label' => 'Hover Botão Ação', 'hint' => 'Hover dos botões de ação.', 'value' => $c('action_button_hover_color', '#1590b8')],
                ],
            ],
            'modais' => [
                'title' => '📱 Modais e Overlays',
                'icon' => 'fas fa-clone',
                'description' => 'Fundo dos popups e modais do site.',
                'fields' => [
                    ['field' => 'modal_bg_color', 'label' => 'Fundo Modal', 'hint' => 'Cor de fundo dos popups (ex: detalhes do jogo).', 'value' => $c('modal_bg_color', '#ffffff')],
                ],
            ],
            'rodape' => [
                'title' => '🦶 Rodapé',
                'icon' => 'fas fa-shoe-prints',
                'description' => 'Fundo, textos e abas de dias (Hoje/Amanhã).',
                'fields' => [
                    ['field' => 'footer_bg_color', 'label' => 'Fundo Rodapé', 'hint' => 'Cor de fundo da seção final.', 'value' => $c('footer_bg_color', '#ffffff')],
                    ['field' => 'footer_text_color', 'label' => 'Texto Rodapé', 'hint' => 'Textos e links no rodapé.', 'value' => $c('footer_text_color', '#555555')],
                    ['field' => 'tab_active_bg_color', 'label' => 'Aba Dia Ativa (Fundo)', 'hint' => 'Fundo da aba Hoje/Amanhã.', 'value' => $c('tab_active_bg_color', '#ffffff')],
                    ['field' => 'tab_active_text_color', 'label' => 'Aba Dia Ativa (Texto)', 'hint' => 'Texto da aba selecionada.', 'value' => $c('tab_active_text_color', '#333333')],
                ],
            ],
        ];
    }
}

