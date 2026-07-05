<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Lancamento;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;
use Illuminate\Support\Facades\DB;

class CambistaController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function indexView()
    {
        return view('admin.cambistas');
    }

    public function index()
    {
        $siteId = app('tenant.site_id');

        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'super_admin' || auth()->user()->role == 'admin') {
            $cambistas = $this->user->with('gerente')
                ->where('nivel', 'cambista')
                ->where('site_id', $siteId)
                ->orderBy('name', 'asc')
                ->get();
        } else {
            $cambistas = $this->user->with('gerente')
                ->where('nivel', 'cambista')
                ->where('site_id', $siteId)
                ->where('gerente_id', auth()->user()->id)
                ->orderBy('name', 'asc')
                ->get();
        }

        return $cambistas->map(function($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'username' => $c->username,
                'gerente_name' => $c->gerente ? $c->gerente->name : null,
                'created_at' => $c->created_at,
                'status' => $c->status,
                'contato' => $c->contato,
                'endereco' => $c->endereco,
                'address' => $c->address,
                'qtd_clientes' => \App\Models\User::where('cambista_id', $c->id)->count(),
                'saldo_simples' => $c->balance,
                'saldo_casadinha' => $c->balance_bonus ?? 0,
                'saldo_loto' => $c->saldo_loto ?? 0,
                'saldo_total' => $c->balance + ($c->balance_bonus ?? 0) + ($c->saldo_loto ?? 0),
                'em_uso' => $c->is_active ? 'Sim' : 'Não',
                'comissao1' => $c->comissao1,
                'comissao2' => $c->comissao2,
                'comissao3' => $c->comissao3,
                'comissao4' => $c->comissao4,
                'comissao5' => $c->comissao5,
                'comissao6' => $c->comissao6,
                'comissao7' => $c->comissao7,
                'comissao8' => $c->comissao8,
                'comissao9' => $c->comissao9,
                'comissao10' => $c->comissao10,
            ];
        });
    }

    public function listGerentes()
    {
        $siteId = app('tenant.site_id');
        return $this->user->where('nivel', 'gerente')
            ->where('site_id', $siteId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

    }

    public function lancamento()
    {
        return view('admin.store-lancamento');
    }
    public function lancamentosView()
    {
        return view('admin.lancamentos');
    }

    public function lancamentos()
    {
        $siteId = app('tenant.site_id');
        $auth = auth()->user();

        $query = \App\Models\Lancamento::with('user')->where('site_id', $siteId)->orderBy('id', 'desc');
        
        if ($auth->nivel == 'gerente') {
            $cambistasIds = User::where('gerente_id', $auth->id)->pluck('id');
            $query->whereIn('user_id', $cambistasIds);
        }

        $list = $query->get()->map(function($l) {
            return [
                'id' => $l->id,
                'colaborador' => $l->user ? $l->user->name : '-',
                'tipo' => $l->tipo,
                'valor' => $l->valor,
                'descricao' => $l->descricao,
                'created_at' => $l->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json($list);
    }

    public function destroyLancamento($id)
    {
        $siteId = app('tenant.site_id');
        $lancamento = \App\Models\Lancamento::where('id', $id)->where('site_id', $siteId)->first();
        if (!$lancamento) return response()->json(['error' => 'Not found'], 404);

        $user = User::find($lancamento->user_id);
        if ($user) {
            \DB::beginTransaction();
            try {
                $walletField = 'balance';
                if (strpos(strtolower($lancamento->descricao), 'casadinha') !== false) $walletField = 'balance_bonus';
                if (strpos(strtolower($lancamento->descricao), 'loto') !== false) $walletField = 'saldo_loto';

                if (strtolower($lancamento->tipo) == 'crédito' || strtolower($lancamento->tipo) == 'credito') {
                    $user->decrement('lancamentos', $lancamento->valor);
                    $user->decrement($walletField, $lancamento->valor);
                    if ($walletField == 'balance') $user->decrement('saldo_simples', $lancamento->valor);
                } else {
                    $user->increment('lancamentos', $lancamento->valor);
                    $user->increment($walletField, $lancamento->valor);
                    if ($walletField == 'balance') $user->increment('saldo_simples', $lancamento->valor);
                }
                $lancamento->delete();
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            $lancamento->delete();
        }
        return response()->json(['success' => true]);
    }

    public function ajustarLimite(Request $request)
    {
        $siteId = app('tenant.site_id');
        $auth = auth()->user();
        $user = User::find($request->user_id);

        if (!$user) return response()->json(['error' => 'User not found'], 404);

        if ($auth->nivel == 'gerente' && $user->gerente_id != $auth->id && $user->id != $auth->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $valor = (float)$request->valor;
        $tipo = $request->tipo; // "aumentar" ou "diminuir"
        $carteira = $request->carteira ?? 'simples'; 

        if ($valor <= 0) {
            return response()->json(['error' => 'Valor deve ser maior que zero'], 400);
        }

        $walletField = [
            'simples'   => 'balance',
            'casadinha' => 'balance_bonus',
            'loto'      => 'saldo_loto'
        ][$carteira] ?? 'balance';

        DB::beginTransaction();
        try {
            if ($tipo == 'aumentar') {
                $user->increment($walletField, $valor);
                if ($walletField == 'balance') {
                    $user->increment('saldo_simples', $valor);
                    if ($user->nivel == 'gerente') $user->increment('saldo_gerente', $valor);
                }
                if ($walletField == 'balance_bonus') $user->increment('saldo_casadinha', $valor);
                if ($walletField == 'saldo_loto') $user->increment('saldo_loto', $valor);
                $desc = "Aumento de Limite";
            } else {
                $user->decrement($walletField, $valor);
                if ($walletField == 'balance') {
                    $user->decrement('saldo_simples', $valor);
                    if ($user->nivel == 'gerente') $user->decrement('saldo_gerente', $valor);
                }
                if ($walletField == 'balance_bonus') $user->decrement('saldo_casadinha', $valor);
                if ($walletField == 'saldo_loto') $user->decrement('saldo_loto', $valor);
                $desc = "Redução de Limite";
            }

            // Apenas registra a transação para auditoria (NÃO ADICIONA EM lancamentos!)
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => $tipo == 'aumentar' ? 'limit_increase' : 'limit_decrease',
                'amount'     => $valor,
                'gateway_ref'=> "limit_adj_" . time() . "_" . $user->id,
                'status'     => 'completed',
                'description'=> $desc . " via " . $auth->name . " na carteira " . ucfirst($carteira),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function storeLancamento(Request $request)
    {
        $siteId = app('tenant.site_id');
        $auth = auth()->user();
        $user = User::find($request->user_id);

        if (!$user) return response()->json(['error' => 'User not found'], 404);

        // Segurança: Gerente só lança para seus cambistas
        if ($auth->nivel == 'gerente' && $user->gerente_id != $auth->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $valor = (float)$request->valor;
        $tipo = $request->tipo; // "credito" ou "debito"
        $carteira = $request->carteira ?? 'simples'; // simples, casadinha, loto

        // Se for Gerente lançando CRÉDITO, ele precisa ter saldo
        if ($auth->nivel == 'gerente' && $tipo == 'credito') {
            if ($auth->balance < $valor) {
                return response()->json(['error' => 'Saldo do gerente insuficiente'], 400);
            }
        }

        $typeKey = ($tipo == "credito") ? "manual_credit" : "manual_debit";
        
        // Mapeamento de campos de saldo reais no DB
        $walletField = [
            'simples'   => 'balance',
            'casadinha' => 'balance_bonus',
            'loto'      => 'saldo_loto'
        ][$carteira] ?? 'balance';

        DB::beginTransaction();
        try {
            if ($typeKey == "manual_credit") {
                $user->increment('lancamentos', $valor);
                $user->increment($walletField, $valor);
                
                // Sincroniza campos legados apenas se forem diferentes do walletField principal
                if ($walletField != 'saldo_simples' && $walletField == 'balance') $user->increment('saldo_simples', $valor);
                if ($walletField != 'saldo_casadinha' && $walletField == 'balance_bonus') $user->increment('saldo_casadinha', $valor);
            } else {
                $user->decrement('lancamentos', $valor);
                $user->decrement($walletField, $valor);
                
                if ($walletField != 'saldo_simples' && $walletField == 'balance') $user->decrement('saldo_simples', $valor);
                if ($walletField != 'saldo_casadinha' && $walletField == 'balance_bonus') $user->decrement('saldo_casadinha', $valor);
            }

            // Se quem está lançando é um GERENTE, ajustamos o saldo dele também
            if ($auth->nivel == 'gerente') {
                $gerente = User::find($auth->id);
                if ($typeKey == "manual_credit") {
                    $gerente->decrement('balance', $valor);
                } else {
                    $gerente->increment('balance', $valor);
                }
            }

            $lancamento = Lancamento::create([
                'user_id'   => $user->id,
                'tipo'      => $tipo == 'credito' ? 'Crédito' : 'Débito',
                'descricao' => $request->descricao . " [Carteira: ".ucfirst($carteira)."]",
                'valor'     => $valor,
                'site_id'   => $siteId,
            ]);

            // LOG DE TRANSAÇÃO PARA O CAMBISTA
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $user->id,
                'type'       => $typeKey,
                'amount'     => $valor,
                'gateway_ref'=> "manual_camb_{$lancamento->id}",
                'status'     => 'completed',
                'description'=> ($typeKey == "manual_credit" ? "Crédito" : "Débito") . " via " . $auth->name . " na carteira " . ucfirst($carteira),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // SE FOR GERENTE, LOGAMOS A SAÍDA NO EXTRATO DELE TAMBÉM
            if ($auth->nivel == 'gerente') {
                DB::table('transactions')->insert([
                    'site_id'    => $siteId,
                    'user_id'    => $auth->id,
                    'type'       => $typeKey == "manual_credit" ? 'manual_debit' : 'manual_credit',
                    'amount'     => $valor,
                    'gateway_ref'=> "manager_transfer_{$lancamento->id}",
                    'status'     => 'completed',
                    'description'=> ($typeKey == "manual_credit" ? "Transferência de Saldo para " : "Estorno de Saldo de ") . $user->name . " [".ucfirst($carteira)."]",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function storeView()
    {
        return view('admin.cadastrar-cambista');
    }

    public function store(Request $request)
    {
        $siteId = app('tenant.site_id');

        // Validação básica no servidor
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:4',
        ]);

        // Verifica se login já existe (Apenas nesta banca, pois agora o sistema é 100% White Label)
        $exists = User::where('username', $request->username)->where('site_id', $siteId)->exists();
        if ($exists) {
            return response()->json(['message' => 'Este login já está em uso nesta banca!', 'errors' => ['username' => ['Login já existe.']]], 422);
        }

        // Resolver Gerente
        $gerente_id = ($request->gerente_id && $request->gerente_id != '0')
            ? $request->gerente_id
            : auth()->user()->id;

        // Se for gerente criando, checar saldo
        if (auth()->user()->nivel == 'gerente') {
            $totalCredito = ($request->saldo_simples ?? 0) + ($request->saldo_casadinha ?? 0);
            if (auth()->user()->saldo_gerente < $totalCredito) {
                return response()->json(['message' => 'Saldo insuficiente para creditar este cambista.'], 400);
            }
        }

        $user = User::create([
            'name'              => $request->name,
            'gerente_id'        => $gerente_id,
            'username'          => $request->username,
            'password'          => bcrypt($request->password),
            'nivel'             => 'cambista',
            'role'              => 'seller',
            'status'            => 1,
            'site_id'           => $siteId,
            'contato'           => $request->contato,
            'address'           => $request->endereco,

            'comissao1'         => $request->comissao1 ?? 0,
            'comissao2'         => $request->comissao2 ?? 0,
            'comissao3'         => $request->comissao3 ?? 0,
            'comissao4'         => $request->comissao4 ?? 0,
            'comissao5'         => $request->comissao5 ?? 0,
            'comissao6'         => $request->comissao6 ?? 0,
            'comissao7'         => $request->comissao7 ?? 0,
            'comissao8'         => $request->comissao8 ?? 0,
            'comissao9'         => $request->comissao9 ?? 0,
            'comissao10'        => $request->comissao10 ?? 0,
            'comissao_loto'     => $request->comissao_loto ?? 0,
            'saldo_simples'     => $request->saldo_simples ?? 0,
            'saldo_casadinha'   => $request->saldo_casadinha ?? 0,
            'saldo_loto'        => $request->saldo_loto ?? 0,
            'comissao_online'   => $request->comissao_online ?? 0,
            'balance'           => $request->saldo_simples ?? 0,
            'balance_bonus'     => $request->saldo_casadinha ?? 0,
        ]);

        if (auth()->user()->nivel == 'gerente' && $user) {
            $gerente = User::find(auth()->user()->id);
            $gerente->saldo_gerente = $gerente->saldo_gerente - (($request->saldo_simples ?? 0) + ($request->saldo_casadinha ?? 0));
            $gerente->save();
        }

        // Copia mercados do template (Admin ID 1) para o novo cambista
        // Buscamos o template do user 1 independente do site_id para garantir que sempre tenha algo
        $mercados = ConfigMercados::where('user_id', 1)->get();
        if ($mercados->isEmpty()) {
            // Se não achar do user 1, tenta pegar qualquer um do site atual para não ficar vazio
            $mercados = ConfigMercados::where('site_id', $siteId)->limit(50)->get();
        }
        foreach ($mercados as $mercado) {
            ConfigMercados::create([
                'name'          => $mercado->name,
                'porcentagem'   => $mercado->porcentagem,
                'status'        => $mercado->status,
                'site_id'       => $user->site_id,
                'user_id'       => $user->id,
            ]);
        }

        // Copia odds do admin para o novo cambista
        $odds = ConfigOdd::where('site_id', $siteId)->where('user_id', 1)->get();
        foreach ($odds as $odd) {
            ConfigOdd::create([
                'mercado_name'      => $odd->mercado_name,
                'name'              => $odd->name,
                'user_id'           => $user->id,
                'site_id'           => $user->site_id,
                'mercado_full_name' => $odd->mercado_full_name,
                'status'            => $odd->status,
                'porcentagem'       => $odd->porcentagem,
            ]);
        }

        return response()->json(['success' => true, 'user' => $user]);
    }


    public function edtView()
    {
        return view('admin.editar-cambista');
    }

    public function searchUser(Request $request)
    {
        $siteId = app('tenant.site_id');

        return $this->user->where('name', 'LIKE', "%{$request->name}%")
            ->where('nivel', 'cambista')
            ->where('gerente_id', auth()->user()->id)
            ->where('site_id', $siteId)
            ->get();
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        if ($request->password != null)
            $data['password'] = bcrypt($data['password']);
        else
            unset($data['password']);

        $cambista = User::find($id);
        
        // Trata estado ativo/bloqueado
        if (isset($data['situacao'])) {
            $data['status'] = ($data['situacao'] === 'ativo') ? 1 : 0;
            unset($data['situacao']);
        }

        if (isset($data['comissao_online'])) {
            $data['comissao_online'] = (float)$data['comissao_online'];
        }

        // Sincroniza campos Nexus Hub se vierem no request
        if (isset($data['saldo_simples'])) $data['balance'] = $data['saldo_simples'];
        if (isset($data['saldo_casadinha'])) $data['balance_bonus'] = $data['saldo_casadinha'];

        $update = $cambista->update($data);
        return response()->json(['success' => true]);
    }

    public function searchByName($name)
    {
        $siteId = app('tenant.site_id');

        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'super_admin' || auth()->user()->role == 'admin')
            return $this->user->where('name', 'LIKE', "%{$name}%")
                ->where('nivel', 'cambista')
                ->where('site_id', $siteId)
                ->get();
        else
            return $this->user->where('name', 'LIKE', "%{$name}%")
                ->where('nivel', 'cambista')
                ->where('gerente_id', auth()->user()->id)
                ->where('site_id', $siteId)
                ->get();
    }

    public function destroy($id)
    {
        $siteId = auth()->user()->site_id;
        $user = User::where('id', $id)
                     ->where('site_id', $siteId)
                     ->where('nivel', 'cambista')
                     ->first();

        if (!$user) {
            return response()->json(['message' => 'Cambista não encontrado.'], 404);
        }

        // Verifica se tem apostas abertas
        $temApostaAberta = \DB::table('bets')
            ->where('user_id', $id)
            ->where('status', 'open')
            ->exists();

        if ($temApostaAberta) {
            return response()->json(['message' => 'Erro: este cambista possui APOSTAS ABERTAS. Não é possível excluir, feche as apostas primeiro ou apenas bloqueie o usuário.'], 422);
        }

        try {
            \DB::beginTransaction();

            // Limpa todo o histórico financeiro e relacional do usuário
            \DB::table('bets')->where('user_id', $id)->delete();
            \DB::table('transactions')->where('user_id', $id)->delete();
            \DB::table('wallets')->where('user_id', $id)->delete();
            \DB::table('balance_adjustments')->where('user_id', $id)->delete();
            \DB::table('balance_adjustments')->where('performed_by', $id)->delete();
            \DB::table('affiliates')->where('user_id', $id)->delete();
            \DB::table('pix_deposits')->where('user_id', $id)->delete();
            \DB::table('withdrawal_requests')->where('user_id', $id)->delete();
            \DB::table('bonus_user')->where('user_id', $id)->delete();
            \DB::table('lancamentos')->where('user_id', $id)->delete();

            // Exclui o próprio usuário
            $user->delete();

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Cambista excluído com sucesso!']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Erro ao excluir: ' . $e->getMessage()], 422);
        }
    }
}
