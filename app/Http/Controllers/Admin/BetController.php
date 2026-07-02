<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\User;
use App\Core\Unified\BettingLogic;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    /**
     * Cancela um bilhete e estorna os valores
     */
    public function cancel($id)
    {
        try {
            BettingLogic::cancel($id, auth()->user());
            return response()->json(['success' => true, 'message' => 'Bilhete cancelado e saldo estornado!']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Gera um PIN de 4 dígitos para permitir o Cash Out
     */
    public function generateCashoutPin($betId)
    {
        $bet = Bet::findOrFail($betId);
        
        $isManager = (auth()->user()->role === 'manager' && $bet->manager_id === auth()->id());
        $isAdmin = in_array(auth()->user()->role, ['admin', 'super_admin']);

        if (!$isAdmin && !$isManager && $bet->user_id !== auth()->id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $pin = rand(1000, 9999);
        $bet->update(['cashout_pin' => $pin]);

        return response()->json(['pin' => $pin]);
    }

    public function validarPinView()
    {
        return view('admin.validar-pin');
    }

    /**
     * Valida o PIN de Cash-Out
     */
    public function validatePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4',
        ]);

        $siteId = config('tenant.site_id', 1);
        $bet = Bet::where('site_id', $siteId)
            ->where('cashout_pin', $request->pin)
            ->first();

        if (!$bet) {
            return redirect()->back()->withErrors(['pin' => 'PIN inválido. Bilhete não encontrado.']);
        }

        if ($bet->status !== 'won') {
            return redirect()->back()->withErrors([
                'pin' => "Bilhete #{$bet->id} não está apto para pagamento (status: {$bet->status})."
            ]);
        }

        if ($bet->paid_at) {
            return redirect()->back()->withErrors([
                'pin' => "Este bilhete já foi pago em " . \Carbon\Carbon::parse($bet->paid_at)->format('d/m/Y H:i') . "."
            ]);
        }

        DB::beginTransaction();
        try {
            $bet->update([
                'paid_at'     => now(),
                'paid_by'     => auth()->id(),
                'cashout_pin' => null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', "✅ Pagamento de R$ " . number_format($bet->potential_payout, 2, ',', '.') . " registrado!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['pin' => 'Erro ao registrar pagamento: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista bilhetes com filtros
     */
    public function index(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $user = auth()->user();

        $query = Bet::where('site_id', $siteId)->with('user')->latest();

        if ($user->role === 'manager') {
            $query->where('manager_id', $user->id);
        } elseif ($user->role === 'seller') {
            $query->where('user_id', $user->id);
        }

        // Filtros de busca (demo parity)
        if ($request->filled('cupom')) {
            $query->where('external_code', 'LIKE', "%{$request->cupom}%");
        }
        if ($request->filled('status') && $request->status !== 'Todos') {
            $query->where('status', $request->status);
        }

        $bets = $query->paginate(20);
        return view('admin.bilhetes', compact('bets'));
    }
}
