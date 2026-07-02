<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\PixDeposit;
use App\Models\WithdrawalRequest;
use App\Models\Saque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteFinanceiroController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $saldoDisponivel = (float) ($user->credito ?? $user->balance ?? 0);
        $saldoBonus = (float) ($user->saldo_bonus ?? $user->balance_bonus ?? 0);

        $depositos = PixDeposit::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $saques = WithdrawalRequest::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('cliente.financeiro', compact(
            'user',
            'saldoDisponivel',
            'saldoBonus',
            'depositos',
            'saques'
        ));
    }

    public function depositos()
    {
        $user = Auth::user();
        $userId = $user->id;

        $depositos = PixDeposit::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('cliente.depositos', compact('user', 'depositos'));
    }

    public function saques()
    {
        $user = Auth::user();
        $userId = $user->id;

        $saques = WithdrawalRequest::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(15);

        $saldoDisponivel = (float) ($user->credito ?? $user->balance ?? 0);

        return view('cliente.saques', compact('user', 'saques', 'saldoDisponivel'));
    }

    public function requestSaque(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric|min:10',
            'pix_key_type' => 'required|in:cpf,cnpj,phone,email,random',
            'pix_key' => 'required|string|max:144',
        ]);

        $user = Auth::user();
        $saldoDisponivel = (float) ($user->credito ?? $user->balance ?? 0);

        if ($request->valor > $saldoDisponivel) {
            return redirect()->back()->with('error', 'Saldo insuficiente para esta solicitação.');
        }

        $saquePendente = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($saquePendente) {
            return redirect()->back()->with('error', 'Você já possui um saque pendente. Aguarde a conclusão antes de solicitar outro.');
        }

        $creditoField = $user->credito !== null ? 'credito' : 'balance';
        $user->{$creditoField} = $saldoDisponivel - $request->valor;
        $user->save();

        WithdrawalRequest::create([
            'site_id' => app('tenant.site_id'),
            'user_id' => $user->id,
            'amount' => $request->valor,
            'pix_key' => $request->pix_key,
            'pix_key_type' => $request->pix_key_type,
            'status' => 'pending',
            'type' => 'pix',
        ]);

        return redirect()->route('cliente.saques')
            ->with('success', 'Solicitação de saque enviada com sucesso! Aguarde a aprovação.');
    }
}
