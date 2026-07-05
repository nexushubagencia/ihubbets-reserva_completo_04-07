<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Aposta;
use App\Models\Lancamento;
use App\Services\CashierService;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FinanceiroController extends Controller
{
    private $arr = array();

    private function cashierService(): CashierService
    {
        return new CashierService();
    }

    /**
     * 💰 Exibe a tela de Solicitações de Saque
     */
    public function saquesView()
    {
        return view('admin.saques');
    }

    /**
     * 💰 Exibe a tela de Histórico de Depósitos
     */
    public function depositosView()
    {
        return view('admin.depositos');
    }

    /**
     * 📊 Lista depósitos via AJAX
     */
    public function listDepositos(\Illuminate\Http\Request $request)
    {
        $siteId = app('tenant.site_id');
        $query = DB::table('transactions')
            ->join('master_users', 'transactions.user_id', '=', 'master_users.id')
            ->select('transactions.*', 'master_users.name as user_name', 'master_users.username')
            ->where('transactions.site_id', $siteId)
            ->where('transactions.type', 'deposit');

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('transactions.created_at', $request->date);
        }

        $depositos = $query->orderBy('transactions.created_at', 'desc')->get();

        return response()->json($depositos);
    }

    /**
     * 📊 Lista solicitações de saque via AJAX
     */
    public function listWithdrawals(\Illuminate\Http\Request $request)
    {
        $siteId = app('tenant.site_id');
        $query = DB::table('withdrawal_requests')
            ->join('master_users', 'withdrawal_requests.user_id', '=', 'master_users.id')
            ->select('withdrawal_requests.*', 'master_users.name as user_name', 'master_users.username', 'master_users.pix_key', 'master_users.pix_key_type')
            ->where('withdrawal_requests.site_id', $siteId);

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('withdrawal_requests.created_at', $request->date);
        }

        $withdrawals = $query->orderBy('withdrawal_requests.created_at', 'desc')->get();

        return response()->json($withdrawals);
    }

    public function index()
    {
        return $this->saquesView();
    }

    public function indexViewAdmGerente()
    {
        return view('admin.caixa-adm-gerente');
    }

    public function indexViewAdmCambista()
    {
        return view('admin.caixa-adm-cambista');
    }

    public function caixaGerente()
    {
        $siteId = app('tenant.site_id');

        $users = User::where('nivel', 'gerente')
            ->where('site_id', $siteId)
            ->orderBy('quantidade_aposta', 'DESC')
            ->get();

        $i = 0;
        foreach ($users as $user) {
            $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                ->groupBy('status')
                ->where('status', 'Aberto')
                ->where('site_id', $siteId)
                ->where('gerente_id', $user->id)
                ->get();

            $total = $user->entradas + $user->lancamentos - ($user->saidas + $user->comissoes);
            $this->arr[$i]['id'] = $user->id;
            $this->arr[$i]['colaborador'] = $user->name;
            $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
            $this->arr[$i]['entradas'] = $user->entradas;
            $this->arr[$i]['entradas_abertas'] = $bets->sum('total_apostado');
            $this->arr[$i]['saidas'] = $user->saidas;
            $this->arr[$i]['comissoes'] = $user->comissoes;
            $this->arr[$i]['lancamentos'] = $user->lancamentos;
            $this->arr[$i]['total'] = $total;
            if ($total > 0) {
                $this->arr[$i]['comissao_gerente'] = $total * $user->comissao_gerente / 100;
            } else {
                $this->arr[$i]['comissao_gerente'] = 0;
            }

            $i++;
        }

        return $this->arr;
    }

    public function caixaCambista()
    {
        $siteId = app('tenant.site_id');

        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'super_admin' || auth()->user()->role == 'admin') {
            $users = User::where('nivel', 'cambista')
                ->where('site_id', $siteId)
                ->get();

            $i = 0;
            foreach ($users as $user) {
                // Soma Híbrida: Rua (user_id) + Online (cambista_id)
                $bets = DB::table('bets')
                    ->select(DB::raw('sum(amount) as total_apostado'))
                    ->where('status', 'open')
                    ->where('site_id', $siteId)
                    ->where(function($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhere('cambista_id', $user->id);
                    })
                    ->first();

                $this->arr[$i]['id'] = $user->id;
                $this->arr[$i]['colaborador'] = $user->name;
                $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
                $this->arr[$i]['entradas_abertas'] = $bets->total_apostado ?? 0;
                $this->arr[$i]['saidas'] = $user->saidas;
                $this->arr[$i]['comissoes'] = $user->comissoes;
                $this->arr[$i]['lancamentos'] = $user->lancamentos;
                $this->arr[$i]['total'] = $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);

                $i++;
            }

            // 🏠 Adiciona linha de VENDAS DIRETAS (sem cambista vinculado)
            $directBets = DB::table('bets')
                ->where('site_id', $siteId)
                ->whereNull('cambista_id')
                ->where('status', 'open')
                ->select(DB::raw('sum(amount) as entradas'), DB::raw('count(id) as qtd'))
                ->first();
            
            if (($directBets->qtd ?? 0) > 0) {
                $this->arr[$i] = [
                    'id' => 0,
                    'colaborador' => 'VENDAS DIRETAS (SITE)',
                    'quantidade' => $directBets->qtd,
                    'entradas' => $directBets->entradas,
                    'entradas_abertas' => 0,
                    'saidas' => 0,
                    'comissoes' => 0,
                    'lancamentos' => 0,
                    'total' => $directBets->entradas
                ];
            }
        }

        if (auth()->user()->nivel == 'gerente' || auth()->user()->role == 'manager') {
            $users = User::where('nivel', 'cambista')
                ->where('site_id', $siteId)
                ->where('gerente_id', auth()->user()->id)
                ->orderBy('quantidade_aposta', 'DESC')
                ->get();

            $i = 0;
            foreach ($users as $user) {
                $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
                    ->groupBy('status')
                    ->where('status', 'Aberto')
                    ->where('site_id', $siteId)
                    ->where('user_id', $user->id)
                    ->get();

                $this->arr[$i]['id'] = $user->id;
                $this->arr[$i]['colaborador'] = $user->name;
                $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
                $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
                $this->arr[$i]['entradas_abertas'] = $bets->sum('total_apostado');
                $this->arr[$i]['saidas'] = $user->saidas;
                $this->arr[$i]['comissoes'] = $user->comissoes;
                $this->arr[$i]['lancamentos'] = $user->lancamentos;
                $this->arr[$i]['total'] = $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);

                $i++;
            }
        }

        return $this->arr;
    }

    public function viewCaixaGerente($id)
    {
        $siteId = app('tenant.site_id');

        $users = User::where('id', $id)->get();

        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
            ->groupBy('status')
            ->where('status', 'Aberto')
            ->where('site_id', $siteId)
            ->where('gerente_id', $id)
            ->get();

        $i = 0;
        foreach ($users as $user) {
            $total = $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);
            $this->arr[$i]['id'] = $user->id;
            $this->arr[$i]['colaborador'] = $user->name;
            $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
            $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
            $this->arr[$i]['entradas_abertas'] = $bets->sum('total_apostado');
            $this->arr[$i]['saidas'] = $user->saidas;
            $this->arr[$i]['comissoes'] = $user->comissoes;
            $this->arr[$i]['lancamentos'] = $user->lancamentos;
            $this->arr[$i]['total'] = $total;
            $this->arr[$i]['comissao_gerente'] = $total * ($user->comissao_gerente / 100);

            $i++;
        }

        return $this->arr;
    }

    public function caixaUser($id)
    {
        $siteId = app('tenant.site_id');

        $users = User::where('gerente_id', $id)
            ->where('site_id', $siteId)
            ->get();

        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
            ->groupBy('status')
            ->where('status', 'Aberto')
            ->where('site_id', $siteId)
            ->where('gerente_id', $id)
            ->get();

        $i = 0;
        foreach ($users as $user) {
            $this->arr[$i]['id'] = $user->id;
            $this->arr[$i]['colaborador'] = $user->name;
            $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
            $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
            $this->arr[$i]['entradas_abertas'] = $bets->sum('total_apostado');
            $this->arr[$i]['saidas'] = $user->saidas;
            $this->arr[$i]['comissoes'] = $user->comissoes;
            $this->arr[$i]['lancamentos'] = $user->lancamentos;
            $this->arr[$i]['total'] = $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);
            $this->arr[$i]['comissao_gerente'] = ($user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes)) * ($user->comissao_gerente / 100);

            $i++;
        }

        return $this->arr;
    }

    public function caixaUserCambista($id)
    {
        $siteId = app('tenant.site_id');

        $users = User::where('id', $id)
            ->where('site_id', $siteId)
            ->get();

        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
            ->groupBy('status')
            ->where('status', 'Aberto')
            ->where('site_id', $siteId)
            ->where('user_id', $id)
            ->get();

        $i = 0;
        foreach ($users as $user) {
            $this->arr[$i]['id'] = $user->id;
            $this->arr[$i]['colaborador'] = $user->name;
            $this->arr[$i]['quantidade'] = $user->quantidade_aposta;
            $this->arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
            $this->arr[$i]['entradas_abertas'] = $bets->sum('total_apostado');
            $this->arr[$i]['saidas'] = $user->saidas;
            $this->arr[$i]['comissoes'] = $user->comissoes;
            $this->arr[$i]['lancamentos'] = $user->lancamentos;
            $this->arr[$i]['total'] = $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);
            $this->arr[$i]['comissao_gerente'] = ($user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes)) * ($user->comissao_gerente / 100);

            $i++;
        }

        return $this->arr;
    }

    public function encerraCaixa($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario nao encontrado.'], 404);
        }

        try {
            $service = $this->cashierService();
            $closeout = $service->closeCashier($id, auth()->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Caixa fechado com sucesso!',
                'closeout' => $closeout,
            ]);
        } catch (\Exception $e) {
            \Log::error('encerraCaixa error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao fechar caixa.'], 500);
        }
    }

    public function previewCaixa($id)
    {
        try {
            $service = $this->cashierService();
            $report = $service->generateClosingReport($id, app('tenant.site_id'));
            return response()->json(['success' => true, 'data' => $report]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function caixaDoDia()
    {
        return view('admin.caixa-do-dia');
    }

    public function caixaDoDiaData()
    {
        $service = $this->cashierService();
        $userId = auth()->user()->id;
        $data = $service->getCaixaDoDia($userId, app('tenant.site_id'));
        return response()->json($data);
    }

    public function caixaDoDiaGeral()
    {
        $service = $this->cashierService();
        $data = $service->getCaixaDoDiaGeral(app('tenant.site_id'));
        return response()->json($data);
    }

    public function historicoFechamentos($id)
    {
        $service = $this->cashierService();
        $history = $service->getCloseoutHistory($id, app('tenant.site_id'));
        return response()->json($history);
    }

    public function searchCaixaGerente(Request $request)
    {
        $siteId = app('tenant.site_id');
        $gerenteId = $request->gerente;

        $user = User::where('id', $gerenteId)
            ->where('site_id', $siteId)
            ->first();

        if (!$user) return [];

        $bets = Aposta::select('status', DB::raw('sum( valor_apostado ) as total_apostado'))
            ->groupBy('status')
            ->where('status', 'Aberto')
            ->where('site_id', $siteId)
            ->where('gerente_id', $user->id)
            ->get();

        $total = $user->entradas + $user->lancamentos - ($user->saidas + $user->comissoes);
        $comissao_gerente = $total > 0 ? $total * $user->comissao_gerente / 100 : 0;

        return [[
            'id' => $user->id,
            'colaborador' => $user->name,
            'quantidade' => $user->quantidade_aposta,
            'entradas' => $user->entradas,
            'entradas_abertas' => $bets->sum('total_apostado'),
            'saidas' => $user->saidas,
            'comissoes' => $user->comissoes,
            'lancamentos' => $user->lancamentos,
            'total' => $total,
            'comissao_gerente' => $comissao_gerente,
        ]];
    }

    public function searchCaixaCambista(Request $request)
    {
        $siteId = app('tenant.site_id');
        $gerenteId = $request->gerente;

        $users = User::where('nivel', 'cambista')
            ->where('site_id', $siteId)
            ->where('gerente_id', $gerenteId)
            ->orderBy('quantidade_aposta', 'DESC')
            ->get();

        $arr = [];
        $i = 0;

        if ($gerenteId == 'Todos') {
            return $this->caixaCambista();
        }

        foreach ($users as $user) {
            $bets = DB::table('bets')
                ->select(DB::raw('sum(amount) as total_apostado'))
                ->where('status', 'open')
                ->where('site_id', $siteId)
                ->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('cambista_id', $user->id);
                })
                ->first();

            $arr[$i]['id'] = $user->id;
            $arr[$i]['colaborador'] = $user->name;
            $arr[$i]['quantidade'] = $user->quantidade_aposta;
            $arr[$i]['entradas'] = $user->entradas + $user->entrada_loto;
            $arr[$i]['entradas_abertas'] = $bets->total_apostado ?? 0;
            $arr[$i]['saidas'] = $user->saidas;
            $arr[$i]['comissoes'] = $user->comissoes;
            $arr[$i]['lancamentos'] = $user->lancamentos;
            $arr[$i]['total'] = $user->entradas + $user->entrada_loto + $user->lancamentos - ($user->saidas + $user->comissoes);

            $i++;
        }

        return $arr;
    }

    /**
     * ✅ Aprova solicitação de Saque (Com suporte a Comprovante)
     */
    public function approveWithdrawal(Request $request, $id)
    {
        $siteId = app('tenant.site_id');
        $withdrawal = DB::table('withdrawal_requests')->where('id', $id)->where('site_id', $siteId)->first();

        if (!$withdrawal || $withdrawal->status != 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Solicitação inválida ou já processada.'], 400);
        }

        DB::beginTransaction();
        try {
            $gatewayRef = "manual_" . auth()->id() . "_" . time();
            $note = $request->input('admin_note', 'Saque processado manualmente');

            // TODO: Salvar comprovante ($request->file('receipt')) se necessário em disco
            
            // Atualiza status do saque
            DB::table('withdrawal_requests')->where('id', $id)->update([
                'status' => 'approved',
                'gateway_ref' => $gatewayRef,
                'description' => $note,
                'updated_at' => now()
            ]);

            // Log de Transação
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $withdrawal->user_id,
                'type'       => 'withdrawal',
                'amount'     => $withdrawal->amount,
                'gateway_ref'=> $gatewayRef,
                'status'     => 'completed',
                'description'=> "Saque de R$ " . number_format($withdrawal->amount, 2, ',', '.') . " Aprovado via Mercado Pago",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Saque aprovado com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('approveWithdrawal error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Erro ao aprovar saque.'], 500);
        }
    }

    /**
     * ❌ Rejeita solicitação de Saque (Estorna saldo ao Usuário)
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $siteId = app('tenant.site_id');
        $withdrawal = DB::table('withdrawal_requests')->where('id', $id)->where('site_id', $siteId)->first();

        if (!$withdrawal || $withdrawal->status != 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Solicitação inválida.'], 400);
        }

        $reason = $request->input('admin_note', 'Rejeitado pelo administrador');

        DB::beginTransaction();
        try {
            // Atualiza status
            DB::table('withdrawal_requests')->where('id', $id)->update([
                'status' => 'rejected',
                'description' => $reason,
                'updated_at' => now()
            ]);

            // Estorna saldo ao usuário ou afiliado dependendo do tipo
            if (($withdrawal->type ?? 'user') === 'affiliate') {
                DB::table('affiliates')
                    ->where('user_id', $withdrawal->user_id)
                    ->where('site_id', $siteId)
                    ->increment('total_commissions', $withdrawal->amount);
            } else {
                DB::table('master_users')->where('id', $withdrawal->user_id)->increment('balance', $withdrawal->amount);
            }

            // Log de Transação (Estorno)
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $withdrawal->user_id,
                'type'       => $withdrawal->type === 'affiliate' ? 'affiliate_withdrawal_refund' : 'withdrawal_refund',
                'amount'     => $withdrawal->amount,
                'gateway_ref'=> "refund_" . $id,
                'status'     => 'completed',
                'description'=> "Estorno de Saque Rejeitado: " . $reason,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Saque rejeitado e saldo estornado.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Erro ao rejeitar saque.'], 500);
        }
    }
}
