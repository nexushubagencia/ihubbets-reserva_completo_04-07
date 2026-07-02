<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Aposta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClienteApostasController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Aposta::where('user_id', $userId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data')) {
            $query->whereDate('created_at', $request->data);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_bilhete', 'like', "%{$busca}%")
                  ->orWhere('cliente', 'like', "%{$busca}%");
            });
        }

        $apostas = $query->orderByDesc('created_at')->paginate(15);

        return view('cliente.apostas', compact('apostas'));
    }

    public function show($id)
    {
        $userId = Auth::id();
        $aposta = Aposta::where('id', $id)
            ->where('user_id', $userId)
            ->with('palpites')
            ->firstOrFail();

        $canCancel = in_array($aposta->status, ['Aberto', 'open', 'pending'])
            && $aposta->created_at->diffInMinutes(now()) < 5;

        return view('cliente.aposta-detail', compact('aposta', 'canCancel'));
    }

    public function cancel($id)
    {
        $userId = Auth::id();
        $aposta = Aposta::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if (!in_array($aposta->status, ['Aberto', 'open', 'pending'])) {
            return redirect()->back()->with('error', 'Esta aposta não pode ser cancelada.');
        }

        if ($aposta->created_at->diffInMinutes(now()) >= 5) {
            return redirect()->back()->with('error', 'Prazo para cancelamento expirado (5 minutos).');
        }

        $user = Auth::user();
        $creditoField = $user->credito !== null ? 'credito' : 'balance';
        $currentBalance = (float) ($user->{$creditoField} ?? 0);
        $user->{$creditoField} = $currentBalance + (float) $aposta->valor_apostado;
        $user->save();

        $aposta->status = 'Cancelado';
        $aposta->save();

        return redirect()->route('cliente.aposta-detail', $id)
            ->with('success', 'Aposta cancelada com sucesso. Valor creditado na sua conta.');
    }
}
