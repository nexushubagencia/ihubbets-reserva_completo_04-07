<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GerenteController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function indexView()
    {
        return view('admin.gerentes');
    }

    public function index()
    {
        $siteId = app('tenant.site_id');

        if (auth()->user()->nivel == 'adm' || auth()->user()->role == 'super_admin' || auth()->user()->role == 'admin')
            return $this->user->where('nivel', 'gerente')
                ->orderBy('name', 'asc')
                ->where('site_id', $siteId)
                ->get();
        else
            return '';
    }

    public function storeView()
    {
        return view('admin.cadastrar-gerente');
    }

    public function store(Request $request)
    {
        $siteId = app('tenant.site_id');

        // Verifica se login já existe nesta banca
        $exists = User::where('username', $request->username)->where('site_id', $siteId)->exists();
        if ($exists) {
            return response()->json(['message' => 'Este login já está em uso nesta banca!', 'errors' => ['username' => ['Login já existe.']]], 422);
        }

        $user = User::create([
            'name'               => $request->name,
            'adm_id'             => auth()->user()->id,
            'gerente_id'         => auth()->user()->id,
            'username'           => $request->username,
            'password'           => bcrypt($request->password),
            'nivel'              => 'gerente',
            'role'               => 'manager',
            'status'             => 1,
            'situacao'           => 'ativo',
            'site_id'            => $siteId,
            'contato'            => $request->contato,
            'commission_rate'    => $request->comissao_gerente,
            'comissao_gerente'   => $request->comissao_gerente,
            'endereco'           => $request->endereco,
            'comissao_gerente_online' => $request->comissao_gerente_online ?? 0,
            'saldo_gerente'      => $request->saldo_gerente ?? 0,
            'can_create_coupons' => $request->can_create_coupons ?? 0,
            'comissao_cambistas' => $request->comissao_cambistas ?? 0,
            'balance'            => $request->saldo_gerente ?? 0,
        ]);

        return response()->json($user);
    }

    public function searchUser(Request $request)
    {
        $siteId = app('tenant.site_id');

        return $this->user->where('name', 'LIKE', "%{$request->name}%")
            ->where('nivel', 'gerente')
            ->where('site_id', $siteId)
            ->get();
    }

    public function edtView()
    {
        return view('admin.editar-gerente');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        if ($request->password != null)
            $data['password'] = bcrypt($data['password']);
        else
            unset($data['password']);

        // Sincroniza campos Nexus Hub
        if (isset($data['saldo_gerente'])) {
            $data['balance'] = $data['saldo_gerente'];
        } elseif (isset($data['balance'])) {
            $data['saldo_gerente'] = $data['balance'];
        }

        $gerente = User::findOrFail($id);
        $update = $gerente->update($data);
        return response()->json(['success' => true]);
    }

    public function searchByName($name)
    {
        $siteId = app('tenant.site_id');

        return $this->user->where('name', 'LIKE', "%{$name}%")
            ->where('nivel', 'gerente')
            ->where('site_id', $siteId)
            ->get();
    }



    public function storeLancamento(Request $request)
    {
        $siteId = app('tenant.site_id');
        $gerente = User::find($request->id);

        if (!$gerente) return response()->json(['message' => 'Gerente não encontrado'], 404);

        $valor = (float)$request->valor;
        $tipo = $request->tipo; // 'credito' ou 'debito'

        DB::beginTransaction();
        try {
            if ($tipo == 'credito') {
                $gerente->increment('saldo_gerente', $valor);
                $gerente->increment('balance', $valor);
                $gerente->increment('lancamentos', $valor);
                $tipoLabel = 'Crédito';
            } else {
                $gerente->decrement('saldo_gerente', $valor);
                $gerente->decrement('balance', $valor);
                $gerente->decrement('lancamentos', $valor);
                $tipoLabel = 'Débito';
            }

            // REGISTRA NO HISTÓRICO DE LANÇAMENTOS (Para aparecer na tela de Lançamentos)
            \App\Models\Lancamento::create([
                'user_id'   => $gerente->id,
                'tipo'      => $tipoLabel,
                'descricao' => ($request->descricao ?? "Lançamento Manual") . " (Gerente)",
                'valor'     => $valor,
                'site_id'   => $siteId,
            ]);

            // LOG DE TRANSAÇÃO (EXTRATO)
            DB::table('transactions')->insert([
                'site_id'    => $siteId,
                'user_id'    => $gerente->id,
                'type'       => ($tipo == 'credito' ? 'manual_credit' : 'manual_debit'),
                'amount'     => $valor,
                'gateway_ref'=> "manual_ger_" . time() . "_" . $gerente->id,
                'status'     => 'completed',
                'description'=> ($tipo == 'credito' ? 'Crédito manual via Admin' : 'Débito manual via Admin') . " (Gerente)",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'sucesso', 'new_balance' => $gerente->saldo_gerente]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'error', 'detail' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $siteId = auth()->user()->site_id;
        $user = User::where('id', $id)
                     ->where('site_id', $siteId)
                     ->where('nivel', 'gerente')
                     ->first();

        if (!$user) {
            return response()->json(['message' => 'Gerente não encontrado.'], 404);
        }

        // Verifica se o gerente ou seus cambistas tem apostas abertas?
        // Mas como as apostas estão atreladas ao user_id, vamos checar diretamente
        $temApostaAberta = \DB::table('bets')
            ->where('user_id', $id)
            ->where('status', 'open')
            ->exists();

        if ($temApostaAberta) {
            return response()->json(['message' => 'Erro: este gerente possui APOSTAS ABERTAS. Não é possível excluir, feche as apostas primeiro.'], 422);
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

            $user->delete();

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Gerente excluído com sucesso!']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Erro ao excluir: ' . $e->getMessage()], 422);
        }
    }
}
