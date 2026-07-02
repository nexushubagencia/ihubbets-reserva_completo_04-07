<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Aposta;
use App\Models\PixDeposit;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClienteHomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $saldoDisponivel = (float) ($user->credito ?? $user->balance ?? 0);
        $saldoBonus = (float) ($user->saldo_bonus ?? $user->balance_bonus ?? 0);

        $apostasAtivas = Aposta::where('user_id', $userId)
            ->whereIn('status', ['Aberto', 'open', 'pending'])
            ->count();

        $ultimasApostas = Aposta::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('cliente.home', compact(
            'user',
            'saldoDisponivel',
            'saldoBonus',
            'apostasAtivas',
            'ultimasApostas'
        ));
    }
}
