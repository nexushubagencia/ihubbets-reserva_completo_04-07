<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiAuthController extends Controller
{
    /**
     * Autenticar usuário e retornar token
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $siteId = config('tenant.site_id', 1);

            $user = User::where('site_id', $siteId)
                ->where(function ($q) use ($request) {
                    $q->where('email', $request->login)
                      ->orWhere('username', $request->login);
                })
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciais inválidas.'
                ], 401);
            }

            if ($user->status == 0 || ($user->is_active !== null && !$user->is_active)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Conta desativada. Contate o suporte.'
                ], 403);
            }

            $token = $user->createToken('api-auth-token')->plainTextToken;

            DB::table('master_users')
                ->where('id', $user->id)
                ->update(['last_activity' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login realizado com sucesso!',
                'token' => $token,
                'user' => $user->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar autenticação.'
            ], 500);
        }
    }

    /**
     * Registrar novo usuário
     */
    public function register(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:master_users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:master_users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'cpf' => ['required', 'string', 'max:14', 'unique:master_users'],
            'birth_date' => ['required', 'date', 'before:-18 years'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'pix_key' => ['nullable', 'string'],
            'pix_key_type' => ['nullable', 'string'],
            'affiliate_code' => ['nullable', 'string'],
        ], [
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'username.unique' => 'Este nome de usuário já está em uso.',
            'birth_date.before' => 'Você precisa ter mais de 18 anos para se cadastrar.',
        ]);

        try {
            $gerenteId = null;
            $cambistaId = null;

            if ($request->affiliate_code) {
                $affUser = DB::table('master_users')
                    ->where('username', $request->affiliate_code)
                    ->where('site_id', $siteId)
                    ->first();

                if ($affUser) {
                    if ($affUser->nivel == 'gerente') {
                        $gerenteId = $affUser->id;
                    } elseif ($affUser->nivel == 'cambista') {
                        $cambistaId = $affUser->id;
                        $gerenteId = $affUser->gerente_id;
                    }
                }
            }

            $user = User::create([
                'site_id' => $siteId,
                'gerente_id' => $gerenteId,
                'cambista_id' => $cambistaId,
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'contato' => $request->phone,
                'cpf' => $request->cpf,
                'birth_date' => $request->birth_date,
                'password' => Hash::make($request->password),
                'role' => 'client',
                'nivel' => 'cliente',
                'status' => 1,
                'balance' => 0,
                'pix_key' => $request->pix_key ?? $request->cpf,
                'pix_key_type' => $request->pix_key_type ?? 'CPF',
            ]);

            if ($cambistaId) {
                DB::table('affiliates')
                    ->where('user_id', $cambistaId)
                    ->increment('registrations');
            }

            $token = $user->createToken('api-auth-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Conta criada com sucesso!',
                'token' => $token,
                'user' => $user->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao criar conta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revogar token (logout)
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout realizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar logout.'
            ], 500);
        }
    }

    /**
     * Retornar informações do usuário autenticado
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();

            $user->loadCount(['apostas' => function ($q) {
                $q->where('status', 'pending');
            }]);

            return response()->json([
                'status' => 'success',
                'user' => $user->makeHidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar dados do usuário.'
            ], 500);
        }
    }
}
