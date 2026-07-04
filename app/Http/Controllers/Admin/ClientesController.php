<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    public function index()
    {
        return view('admin.clientes');
    }

    public function list()
    {
        try {
            $siteId = config('tenant.site_id', 1);
            $user = auth()->user();

            $query = User::where('nivel', 'cliente')
                ->where('site_id', $siteId);

            // Gerente só vê seus cambistas' clientes
            if (($user->nivel ?? '') === 'gerente' || ($user->role ?? '') === 'manager') {
                $cambistaIds = User::where('gerente_id', $user->id)
                    ->where('site_id', $siteId)
                    ->pluck('id')
                    ->toArray();
                $query->whereIn('cambista_id', $cambistaIds);
            }

            return $query->orderBy('name', 'asc')->get();
        } catch (\Exception $e) {
            \Log::error('ClientesController::list error: ' . $e->getMessage());
            return response()->json(['result' => []], 500);
        }
    }

    public function searchUser(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        return User::where('name', 'LIKE', "%{$request->name}%")
            ->where('nivel', 'cliente')
            ->where('site_id', $siteId)
            ->get();
    }

    public function update(Request $request, $id)
    {
        try {
            $cliente = User::find($id);
            if (!$cliente) {
                return response()->json(['error' => 'Cliente não encontrado.'], 404);
            }

            $data = $request->only(['name', 'username', 'email', 'cpf', 'phone', 'situacao', 'pix_key', 'pix_key_type']);

            if (!empty($request->password)) {
                $data['password'] = bcrypt($request->password);
            }

            $cliente->update($data);

            return response()->json(['success' => true, 'message' => 'Cliente atualizado com sucesso!']);
        } catch (\Exception $e) {
            \Log::error('ClientesController::update error: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao atualizar cliente.'], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $cliente = User::find($id);
            if (!$cliente) {
                return response()->json(['error' => 'Cliente não encontrado.'], 404);
            }

            // Verificar se tem apostas associadas
            $hasBets = DB::table('apostas')->where('user_id', $id)->exists()
                || DB::table('bets')->where('user_id', $id)->exists();

            if ($hasBets) {
                return response()->json(['error' => 'Cliente possui apostas associadas e não pode ser excluído. Bloqueie-o ao invés de excluir.'], 400);
            }

            $cliente->delete();
            return response()->json(['success' => true, 'message' => 'Cliente excluído com sucesso!']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('ClientesController::destroy QueryException: ' . $e->getMessage());
            return response()->json(['error' => 'Cliente possui registros associados (apostas, financeiro) e não pode ser excluído. Bloqueie-o ao invés de excluir.'], 400);
        } catch (\Exception $e) {
            \Log::error('ClientesController::destroy error: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao excluir cliente.'], 400);
        }
    }

    public function depositos($userId)
    {
        try {
            $siteId = config('tenant.site_id', 1);
            $user = auth()->user();

            if (($user->nivel ?? '') !== 'adm' && ($user->role ?? '') !== 'admin' && ($user->role ?? '') !== 'super_admin') {
                return response()->json(['error' => 'Sem permissão.'], 403);
            }

            $depositos = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('site_id', $siteId)
                ->where('type', 'deposit')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($depositos);
        } catch (\Exception $e) {
            \Log::error('ClientesController::depositos error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function saques($userId)
    {
        try {
            $siteId = config('tenant.site_id', 1);
            $user = auth()->user();

            if (($user->nivel ?? '') !== 'adm' && ($user->role ?? '') !== 'admin' && ($user->role ?? '') !== 'super_admin') {
                return response()->json(['error' => 'Sem permissão.'], 403);
            }

            $saques = DB::table('withdrawal_requests')
                ->where('user_id', $userId)
                ->where('site_id', $siteId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($saques);
        } catch (\Exception $e) {
            \Log::error('ClientesController::saques error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
