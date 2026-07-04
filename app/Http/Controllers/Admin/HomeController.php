<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InfoBanca;
use App\Models\Aposta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class HomeController extends Controller
{
    private $adm;
    private $data = 'adm';
    private $gerente;
    private $arr = array();

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->hoje = Carbon::today();
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $siteId = app('tenant.site_id');
        $site = \App\Models\Site::find($siteId);
        $nomeBanca = $site ? $site->name : 'IHUB BETS';

        return view('admin.home', compact('nomeBanca'));
    }

    public function relatorioHome()
    {
        $siteId = app('tenant.site_id');

        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'super_admin' || auth()->user()->role == 'admin') {

            // === CAMBISTAS (LEGADO) ===
            $users = User::where('nivel', 'cambista')
                ->where('site_id', $siteId)
                ->get();

            // === ONLINE BETS (TODAS as apostas online) ===
            // Bets COM cambista_id = já contadas no master_users.entradas do cambista
            // Bets SEM cambista_id = vendas diretas online (não contadas em ninguém)
            $betsOnline = DB::table('bets')->where('site_id', $siteId);
            $apostasLegacy = Aposta::where('site_id', $siteId)->where('tipo', '!=', 'Bolão');

            // ENTRADAS = cambistas legado + entrada_loto + vendas diretas online (sem cambista)
            $vendasDiretasOnline = (clone $betsOnline)->whereNull('cambista_id')->where('status', '!=', 'cancelled')->sum('amount');
            $this->arr['entradas'] = $users->sum('entradas') + $users->sum('entrada_loto') + $vendasDiretasOnline;

            // SAIDAS = cambistas legado + premios diretos online (sem cambista)
            $premiosDiretosOnline = (clone $betsOnline)->whereNull('cambista_id')->where('status', 'won')->sum('potential_payout');
            $this->arr['saidas'] = $users->sum('saidas') + $premiosDiretosOnline;

            // EM ABERTO = apostas legado Aberto + apostas online open/pending (TODAS, com e sem cambista)
            $abertasLegacy = (clone $apostasLegacy)->where('status', 'Aberto')->sum('valor_apostado');
            $abertasOnline = (clone $betsOnline)->whereIn('status', ['pending', 'open'])->sum('amount');
            $this->arr['entradas_abertas'] = $abertasLegacy + $abertasOnline;

            // QUANTIDADE = apostas legado + apostas online
            $qtdLegacy = (clone $apostasLegacy)->count();
            $qtdOnline = (clone $betsOnline)->count();
            $this->arr['quantidade'] = $qtdLegacy + $qtdOnline;

            // COMISSOES = cambistas + online commissions
            $comissoesOnline = (clone $betsOnline)->where('status', '!=', 'cancelled')->sum('commission_amount');
            $this->arr['comissoes'] = $users->sum('comissoes') + $comissoesOnline;

            // LANCAMENTOS = cambistas legado
            $this->arr['lancamentos'] = $users->sum('lancamentos');
            
            // TOTAL LIQUIDO = (entradas + lancamentos) - (saidas + comissoes)
            $this->arr['total'] = ($this->arr['entradas'] + $this->arr['lancamentos']) - ($this->arr['saidas'] + $this->arr['comissoes']);

            // === DADOS DOS USUARIOS ONLINE ===
            $totalUsuarios = User::where('site_id', $siteId)->where('nivel', 'cliente')->count();
            $somaSaldoUsuarios = User::where('site_id', $siteId)->where('nivel', 'cliente')
                ->sum(DB::raw('COALESCE(balance, 0) + COALESCE(balance_bonus, 0)'));
            $totalSaquesPendentes = DB::table('withdrawal_requests')
                ->where('site_id', $siteId)
                ->where('status', 'pending')
                ->sum('amount');
            $totalDepositosHoje = DB::table('pix_deposits')
                ->where('site_id', $siteId)
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'approved')
                ->sum('amount');

            $this->arr['total_usuarios'] = $totalUsuarios;
            $this->arr['saldo_usuarios'] = (float) $somaSaldoUsuarios;
            $this->arr['saques_pendentes'] = (float) $totalSaquesPendentes;
            $this->arr['depositos_hoje'] = (float) $totalDepositosHoje;

            // Stats de apostas dos clientes online
            $clienteIds = User::where('site_id', $siteId)->where('nivel', 'cliente')->pluck('id');
            $this->arr['bilhetes_usuarios'] = Aposta::whereIn('user_id', $clienteIds)->count();
            $this->arr['entradas_usuarios'] = (float) Aposta::whereIn('user_id', $clienteIds)->where('status', '!=', 'Cancelado')->sum('valor_apostado');
            $this->arr['entradas_abertas_usuarios'] = (float) Aposta::whereIn('user_id', $clienteIds)->where('status', 'Aberto')->sum('valor_apostado');

            // 🚀 Gráfico 7 dias
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                $last7Days[$date] = [
                    'entradas' => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $date)->where('status', '!=', 'cancelled')->sum('amount') + Aposta::where('site_id', $siteId)->whereDate('created_at', $date)->where('status', '!=', 'Cancelado')->sum('valor_apostado'),
                    'saidas' => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $date)->where('status', 'won')->sum('potential_payout') + Aposta::where('site_id', $siteId)->whereDate('created_at', $date)->where('status', 'Venceu')->sum('retorno_possivel'),
                    'abertas' => DB::table('bets')->where('site_id', $siteId)->whereDate('created_at', $date)->where('status', 'pending')->sum('amount') + Aposta::where('site_id', $siteId)->whereDate('created_at', $date)->where('status', 'Aberto')->sum('valor_apostado'),
                ];
            }
            $this->arr['chart_7_days'] = $last7Days;

            // 🚀 Tickets Stats
            $this->arr['tickets_ganhos'] = DB::table('bets')->where('site_id', $siteId)->where('status', 'won')->count() + Aposta::where('site_id', $siteId)->whereIn('status', ['Venceu', 'Ganhou'])->count();
            $this->arr['tickets_perdidos'] = DB::table('bets')->where('site_id', $siteId)->where('status', 'lost')->count() + Aposta::where('site_id', $siteId)->where('status', 'Perdeu')->count();
            $this->arr['tickets_abertos_count'] = DB::table('bets')->where('site_id', $siteId)->whereIn('status', ['pending', 'open'])->count() + Aposta::where('site_id', $siteId)->where('status', 'Aberto')->count();

            // 🚀 Top Cambistas
            $this->arr['top_users'] = User::where('site_id', $siteId)
                ->where('nivel', 'cambista')
                ->orderByDesc('entradas')
                ->take(5)
                ->get(['name', 'entradas', 'saidas', 'quantidade_aposta', 'comissoes']);
        }

        if (auth()->user()->nivel == 'gerente' || auth()->user()->role == 'manager') {
            $gerente = User::find(auth()->user()->id);
            $users = User::where('gerente_id', $gerente->id)->get();

            // === CAMBISTAS DESTE GERENTE (LEGADO) ===
            $entradasCambistas = $users->sum('entradas');
            $entradasLotoCambistas = $users->sum('entrada_loto');
            $saidasCambistas = $users->sum('saidas');
            $comissoesCambistas = $users->sum('comissoes');
            $lancamentosCambistas = $users->sum('lancamentos');

            // === ONLINE BETS deste gerente (via manager_id) ===
            $betsOnlineGerente = DB::table('bets')->where('manager_id', $gerente->id);
            $apostasLegacyGerente = Aposta::where('gerente_id', $gerente->id)->where('tipo', '!=', 'Bolão');

            // Bets SEM cambista_id = vendas diretas online deste gerente
            $vendasDiretasGerente = (clone $betsOnlineGerente)->whereNull('cambista_id')->where('status', '!=', 'cancelled')->sum('amount');
            $premiosDiretosGerente = (clone $betsOnlineGerente)->whereNull('cambista_id')->where('status', 'won')->sum('potential_payout');

            // Comissão online deste gerente (de todas as bets, inclusive com cambista)
            $comissaoOnlineGerente = (clone $betsOnlineGerente)->where('status', '!=', 'cancelled')->sum('manager_commission_amount');

            // Lucro da Rua (legado)
            $lucroRua = $entradasCambistas - ($saidasCambistas + $comissoesCambistas);
            $comissaoRuaGerente = ($lucroRua > 0) ? ($lucroRua * ($gerente->comissao_gerente / 100)) : 0;

            // EM ABERTO = legado + online open/pending
            $abertasLegacyGerente = (clone $apostasLegacyGerente)->where('status', 'Aberto')->sum('valor_apostado');
            $abertasOnlineGerente = (clone $betsOnlineGerente)->whereIn('status', ['pending', 'open'])->sum('amount');

            // QUANTIDADE TOTAL
            $qtdLegacyGerente = (clone $apostasLegacyGerente)->count();
            $qtdOnlineGerente = (clone $betsOnlineGerente)->count();

            // Totais consolidados (legado + online)
            $this->arr['quantidade'] = $qtdLegacyGerente + $qtdOnlineGerente;
            $this->arr['entradas'] = $entradasCambistas + $entradasLotoCambistas + $vendasDiretasGerente;
            $this->arr['entradas_abertas'] = $abertasLegacyGerente + $abertasOnlineGerente;
            $this->arr['saidas'] = $saidasCambistas + $premiosDiretosGerente;
            $this->arr['comissoes'] = $comissaoRuaGerente + $comissaoOnlineGerente;
            $this->arr['lancamentos'] = $lancamentosCambistas;
            
            // Total Líquido para o Gerente
            $this->arr['total'] = ($this->arr['entradas'] + $this->arr['lancamentos']) - ($this->arr['saidas'] + $this->arr['comissoes']); 

            // 🚀 Gráfico 7 dias
            $last7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i)->format('Y-m-d');
                $last7Days[$date] = [
                    'entradas' => DB::table('bets')->where('manager_id', $gerente->id)->whereDate('created_at', $date)->where('status', '!=', 'cancelled')->sum('amount') + Aposta::where('gerente_id', $gerente->id)->whereDate('created_at', $date)->where('status', '!=', 'Cancelado')->sum('valor_apostado'),
                    'saidas' => DB::table('bets')->where('manager_id', $gerente->id)->whereDate('created_at', $date)->where('status', 'won')->sum('potential_payout') + Aposta::where('gerente_id', $gerente->id)->whereDate('created_at', $date)->where('status', 'Venceu')->sum('retorno_possivel'),
                    'abertas' => DB::table('bets')->where('manager_id', $gerente->id)->whereDate('created_at', $date)->where('status', 'pending')->sum('amount') + Aposta::where('gerente_id', $gerente->id)->whereDate('created_at', $date)->where('status', 'Aberto')->sum('valor_apostado'),
                ];
            }
            $this->arr['chart_7_days'] = $last7Days;

            // 🚀 Tickets Stats
            $this->arr['tickets_ganhos'] = DB::table('bets')->where('manager_id', $gerente->id)->where('status', 'won')->count() + Aposta::where('gerente_id', $gerente->id)->whereIn('status', ['Venceu', 'Ganhou'])->count();
            $this->arr['tickets_perdidos'] = DB::table('bets')->where('manager_id', $gerente->id)->where('status', 'lost')->count() + Aposta::where('gerente_id', $gerente->id)->where('status', 'Perdeu')->count();
            $this->arr['tickets_abertos_count'] = DB::table('bets')->where('manager_id', $gerente->id)->whereIn('status', ['pending', 'open'])->count() + Aposta::where('gerente_id', $gerente->id)->where('status', 'Aberto')->count();

            // 🚀 Top Cambistas
            $this->arr['top_users'] = User::where('gerente_id', $gerente->id)
                ->where('nivel', 'cambista')
                ->orderByDesc('entradas')
                ->take(5)
                ->get(['name', 'entradas', 'saidas', 'quantidade_aposta', 'comissoes']);
        }

        return $this->arr;
    }

    public function editBanca()
    {
        return view('admin.edit-banca');
    }

    public function mostraDadosAdm()
    {
        $siteId = app('tenant.site_id');
        return InfoBanca::where('site_id', $siteId)->first();
    }

    public function viewRegulamento()
    {
        return view('admin.regulamento');
    }

    public function indexRegulamento()
    {
        $siteId = app('tenant.site_id');
        $site = \App\Models\Site::find($siteId);
        return response()->json([['id' => $site->id, 'regulamento' => $site->regulation]]);
    }

    public function regulamentoUpdate(Request $request, $id)
    {
        $data = \App\Models\Site::find($id);
        $data->regulation = $request->regulamento;
        $data->save();
        return response()->json(['status' => 'success']);
    }

    public function userLogado()
    {
        return User::find(auth()->user()->id);
    }

    /**
     * 👥 Retorna lista de gerentes do tenant atual
     */
    public function listGerentes()
    {
        $siteId = app('tenant.site_id');
        return User::where('site_id', $siteId)
            ->where('nivel', 'gerente')
            ->get(['id', 'name']);
    }

    /**
     * 🟢 Retorna jogadores ativos nos últimos 15 minutos (Real-time Metrics)
     */
    public function activePlayers()
    {
        $siteId = app('tenant.site_id');
        $count = DB::table('master_users')
            ->where('site_id', $siteId)
            ->where('last_activity', '>=', now()->subMinutes(15))
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * 🔄 Altera situação do usuário (Ativo/Bloqueado)
     */
    public function alterarUser(Request $request)
    {
        $siteId = app('tenant.site_id');
        $user = User::where('id', $request->id)->where('site_id', $siteId)->first();

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        $status = $request->status;
        $user->status = $status;
        $user->situacao = ($status == 1) ? 'ativo' : 'inativo';
        $user->is_active = ($status == 1) ? 1 : 0;
        $user->save();

        return response()->json(['success' => true]);
    }
}
