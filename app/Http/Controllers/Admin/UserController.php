<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Region;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function sellers()
    {
        $siteId = config('tenant.site_id', 1);
        $user = auth()->user();

        $query = User::where('site_id', $siteId)->where('role', 'cambista')->with(['region']);

        // Se for Gerente, filtra apenas os DELE
        if ($user->role === 'manager') {
            $query->where('gerente_id', $user->id);
        }

        $users = $query->get();
        $managers = User::where('site_id', $siteId)->where('role', 'manager')->get();
        $regions = Region::where('site_id', $siteId)->get();

        return view('admin.users.sellers', compact('users', 'managers', 'regions'));
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $currentUser = auth()->user();

        $data = $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:master_users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:manager,cambista,admin',
            'region_id' => 'nullable|exists:regions,id',
            'gerente_id' => 'nullable|exists:master_users,id',
            'commission_percent' => 'nullable|numeric|max:100',
            'can_cancel_tickets' => 'nullable|boolean'
        ]);

        $data['site_id'] = $siteId;
        $data['password'] = Hash::make($data['password']);

        // Se for gerente criando, ele é o pai automático
        if ($currentUser->role === 'manager') {
            $data['gerente_id'] = $currentUser->id;
            $data['role'] = 'cambista'; // Gerente só pode criar cambista
        }
        
        User::create($data);

        return redirect()->back()->with('success', 'Colaborador criado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        // Segurança: Gerente só edita o dele
        if ($currentUser->role === 'manager' && $user->gerente_id !== $currentUser->id) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        $data = $request->validate([
            'name' => 'sometimes|string',
            'balance' => 'sometimes|numeric',
            'commission_percent' => 'sometimes|numeric|max:100',
            'password' => 'nullable|string|min:6',
            'status' => 'sometimes|in:1,0',
            'can_cancel_tickets' => 'sometimes|boolean'
        ]);

        // SEGURANÇA: Somente Admin pode mudar SALDO diretamente na ficha.
        // Gerente não pode 'fabricar' saldo para o cambista via edição.
        if ($currentUser->role !== 'admin' && isset($data['balance'])) {
            unset($data['balance']);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Dados atualizados com sucesso!');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        // SEGURANÇA: Gerente só bloqueia o cambista DELE.
        if ($currentUser->role === 'manager' && $user->gerente_id !== $currentUser->id) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        // Admin não pode ser bloqueado por gerentes
        if ($user->role === 'admin' && $currentUser->role !== 'admin') {
            return redirect()->back()->with('error', 'Você não tem permissão para bloquear um Administrador.');
        }

        $user->status = ($user->status == 1) ? 0 : 1;
        $user->save();

        return redirect()->back()->with('success', 'Status do usuário alterado!');
    }

    public function clients()
    {
        $siteId = config('tenant.site_id', 1);
        $user = auth()->user();

        // No IHUB V2, Clientes são os 'punters' (apostadores online)
        // Se for gerente ou cambista, filtra pela hierarquia
        $query = User::where('site_id', $siteId)->where('role', 'player');

        if ($user->role === 'cambista') {
            $query->where('cambista_id', $user->id); // Cambista vê os clientes que ele cadastrou
        } elseif ($user->role === 'manager') {
            $query->whereIn('gerente_id', User::where('gerente_id', $user->id)->pluck('id'));
        }

        $users = $query->get();
        return view('admin.users.clients', compact('users'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Sanitiza: se o browser autofill injetou senha vazia ou placeholder, remove antes da validação
        if ($request->filled('password') && strlen($request->input('password')) < 6) {
            $request->merge(['password' => null, 'password_confirmation' => null]);
        }
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:master_users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'theme_preference' => 'nullable|string|in:light,dark',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'cpf' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'pix_key_type' => 'nullable|string|max:20',
            'pix_key' => 'nullable|string|max:255',
            'endereco' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'theme_preference' => $request->theme_preference ?? $user->theme_preference ?? 'light',
            'cpf' => $request->cpf,
            'contato' => $request->phone,
            'pix_key_type' => $request->pix_key_type,
            'pix_key' => $request->pix_key,
            'address' => $request->endereco,
        ];

        // Processamento do Avatar com tratamento robusto
        if ($request->hasFile('avatar')) {
            try {
                $avatarDir = public_path('uploads/avatars');
                if (!is_dir($avatarDir)) {
                    mkdir($avatarDir, 0755, true);
                }

                // Remove avatar antigo se existir
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    @unlink(public_path($user->avatar));
                }

                $image = $request->file('avatar');
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move($avatarDir, $filename);
                $updateData['avatar'] = 'uploads/avatars/' . $filename;
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Erro ao salvar a foto: ' . $e->getMessage());
            }
        }

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updateTheme(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->theme_preference = $request->theme;
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 401);
    }
}
