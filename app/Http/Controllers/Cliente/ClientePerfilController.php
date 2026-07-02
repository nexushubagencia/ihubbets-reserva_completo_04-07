<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientePerfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('cliente.perfil', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:master_users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14',
            'pix_key_type' => 'nullable|in:cpf,cnpj,phone,email,random',
            'pix_key' => 'nullable|string|max:144',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('error', 'Senha atual incorreta.');
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->cpf = $request->cpf;
        $user->pix_key_type = $request->pix_key_type;
        $user->pix_key = $request->pix_key;
        $user->save();

        return redirect()->route('cliente.perfil')->with('success', 'Perfil atualizado com sucesso!');
    }
}
