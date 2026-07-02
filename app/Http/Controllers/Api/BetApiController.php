<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\User;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BetApiController extends Controller
{
    /**
     * Registra uma aposta (Cambista autenticado)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $siteId = config('tenant.site_id', 1);

        // 1. Validação Básica
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'selections' => 'required|array',
            'potential_return' => 'required|numeric'
        ]);

        // 2. Verifica Saldo do Cambista
        if ($user->balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Saldo insuficiente para realizar esta aposta.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 3. Calcula Comissão do Cambista
            // Busca a porcentagem vinda das configurações do usuário ou do site
            $commissionPercent = $user->commission_percent ?? 10; // Default 10% se não configurado
            $commissionAmount = ($request->amount * $commissionPercent) / 100;

            // 4. Cria o Bilhete
            $bet = Bet::create([
                'site_id' => $siteId,
                'user_id' => $user->id,
                'manager_id' => $user->manager_id, // Vincula ao gerente para o relatório de lucro
                'amount' => $request->amount,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
                'potential_return' => $request->potential_return,
                'selections' => json_encode($request->selections),
                'status' => 'pending',
                'ticket_code' => 'IHUB-' . strtoupper(bin2hex(random_bytes(3)))
            ]);

            // 5. Desconta do Saldo do Cambista
            $user->balance -= $request->amount;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Aposta realizada com sucesso!',
                'bet_id' => $bet->id,
                'ticket_code' => $bet->ticket_code,
                'new_balance' => $user->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar aposta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valida um Pré-Bilhete e transforma em Aposta Real
     */
    public function validatePreBet(Request $request)
    {
        // Lógica para o cambista digitar o código e imprimir
        // Implementaremos após o store estar estável
    }
}
