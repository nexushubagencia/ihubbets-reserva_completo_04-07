<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FrontendAuthController extends Controller
{
    public function register(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:master_users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:master_users'],
            'telefone' => ['required', 'string', 'max:20'],
            'cpf' => ['required', 'string', 'max:14', 'unique:master_users'],
            'dia' => ['required', 'numeric', 'min:1', 'max:31'],
            'mes' => ['required', 'numeric', 'min:1', 'max:12'],
            'ano' => ['required', 'numeric', 'min:1900', 'max:2008'], // +18 anos
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'promocode' => ['nullable', 'string', 'exists:promocodes,code'],
            'affiliate_code' => ['nullable', 'string'],
        ], [
            'cpf.unique' => 'Este CPF já está cadastrado em nossa base.',
            'username.unique' => 'Este nome de usuário já está em uso.',
            'ano.max' => 'Você precisa ter mais de 18 anos para se cadastrar.',
            'promocode.exists' => 'O código promocional informado é inválido ou expirou.',
        ]);

        $birthDate = $request->ano . '-' . str_pad($request->mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($request->dia, 2, '0', STR_PAD_LEFT);

        // Verifica se tem promocode e busca o gerente/bônus
        $gerenteId = null;
        $bonusValue = 0;
        $promocode = null;

        if ($request->promocode) {
            $promocode = DB::table('promocodes')
                ->where('code', strtoupper($request->promocode))
                ->where('site_id', $siteId)
                ->where('status', 1)
                ->first();
            
            if ($promocode) {
                $gerenteId = $promocode->manager_id;
                $bonusValue = $promocode->value;
            }
        }

        // 🚀 Sistema de Afiliados (Link ?ref=XXX)
        $cambistaId = null;
        $affCode = $request->affiliate_code ?? $request->promocode; // Tenta usar o promocode como ref se não houver ref direta

        if ($affCode) {
            // Busca o dono do código (pode estar na tabela promocodes ou master_users diretamente)
            // Prioridade: Promocodes (Cupons do Admin/Gerente)
            $pc = DB::table('promocodes')->where('code', strtoupper($affCode))->first();
            if ($pc) {
                $gerenteId = $pc->manager_id;
            } else {
                // Se não é cupom, pode ser o USERNAME de um gerente ou cambista (Link de Afiliado)
                $affUser = DB::table('master_users')->where('username', $affCode)->first();
                if ($affUser) {
                    if ($affUser->nivel == 'gerente') {
                        $gerenteId = $affUser->id;
                    } elseif ($affUser->nivel == 'cambista') {
                        $cambistaId = $affUser->id;
                        $gerenteId = $affUser->gerente_id; // Pega o chefe do cambista
                    }
                }
            }
        }

        $user = User::create([
            'site_id' => $siteId,
            'gerente_id' => $gerenteId, 
            'cambista_id' => $cambistaId, // Novo vínculo direto com cambista
            'name' => $request->nome,
            'username' => $request->username,
            'email' => $request->email,
            'contato' => $request->telefone,
            'cpf' => $request->cpf,
            'birth_date' => $birthDate,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'nivel' => 'cliente',
            'status' => 1,
            'balance' => 0,
            'balance_bonus' => $bonusValue,
            'pix_key' => $request->pix_key ?? $request->cpf,
            'pix_key_type' => $request->pix_key_type ?? 'CPF',
        ]);

        // Se teve bônus, registra na tabela bonus_user e transações
        if ($promocode && $bonusValue > 0) {
            DB::table('bonus_user')->insert([
                'user_id' => $user->id,
                'bonus_id' => $promocode->id,
                'initial_value' => $bonusValue,
                'current_balance' => $bonusValue,
                'target_rollover' => $bonusValue * ($promocode->rollover ?? 1),
                'current_rollover' => 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('transactions')->insert([
                'site_id' => $siteId,
                'user_id' => $user->id,
                'type' => 'manual_credit',
                'amount' => $bonusValue,
                'status' => 'completed',
                'description' => "Bônus de Cadastro: Cupom {$promocode->code}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Se o usuário foi convidado por um afiliado (cambista_id), incrementa o contador de registros
        if ($cambistaId) {
            DB::table('affiliates')
                ->where('user_id', $cambistaId)
                ->increment('registrations');
        }

        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Conta criada com sucesso!',
            'token' => 'session_auth_token_' . bin2hex(random_bytes(16)),
            'user' => $user->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
        ]);
    }

    /**
     * 🔐 Alterar senha do usuário logado
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Senha alterada com sucesso!'
        ]);
    }
}
