<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterClientController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register_client');
    }

    public function register(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:master_users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:master_users'],
            'phone' => ['required', 'string', 'max:20'],
            'cpf' => ['required', 'string', 'max:14', 'unique:master_users'],
            'birth_date' => ['required', 'date', 'before:-18 years'], // Garante que é +18
            'password' => ['required', 'string', 'min:6', 'confirmed'], // Confirmação automática pelo campo password_confirmation
        ], [
            'birth_date.before' => 'Você precisa ter mais de 18 anos para se cadastrar.',
            'cpf.unique' => 'Este CPF já está cadastrado em nossa base.',
            'username.unique' => 'Este nome de usuário já está em uso.',
        ]);

        $user = User::create([
            'site_id' => $siteId,
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'cpf' => $request->cpf,
            'birth_date' => $request->birth_date,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'status' => 1,
            'balance' => 0,
            'balance_bonus' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Bem-vindo! Sua conta foi criada com sucesso.');
    }
}
