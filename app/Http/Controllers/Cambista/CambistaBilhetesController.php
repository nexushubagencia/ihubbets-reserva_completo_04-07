<?php

namespace App\Http\Controllers\Cambista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Aposta;
use App\Models\Configuracao;
use Carbon\Carbon;

class CambistaBilhetesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $siteId = app('tenant.site_id');

        $query = DB::table('bets')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId);

        if ($request->filled('status') && $request->status !== 'Todos') {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('created_at', [
                $request->data_inicio . ' 00:00:00',
                $request->data_fim . ' 23:59:59'
            ]);
        } elseif ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('codigo')) {
            $query->where('codigo_bilhete', 'like', '%' . $request->codigo . '%');
        }

        $bilhetes = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('cambista.bilhetes', compact('user', 'bilhetes'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $siteId = app('tenant.site_id');

        $bilhete = DB::table('bets')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->first();

        if (!$bilhete) {
            return redirect()->route('cambista.bilhetes')->with('error', 'Bilhete não encontrado.');
        }

        $palpites = DB::table('bet_items')->where('bet_id', $bilhete->id)->get();
        $bilhete->palpites = $palpites;

        $config = DB::table('configuracaos')->where('site_id', $siteId)->first();
        $podeCancelar = $config && $config->cambista_pode_cancelar;

        return view('cambista.bilhete-detail', compact('user', 'bilhete', 'podeCancelar'));
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $siteId = app('tenant.site_id');

        $config = DB::table('configuracaos')->where('site_id', $siteId)->first();
        if (!$config || !$config->cambista_pode_cancelar) {
            return back()->with('error', 'Você não tem permissão para cancelar apostas.');
        }

        $bilhete = DB::table('bets')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->first();

        if (!$bilhete) {
            return back()->with('error', 'Bilhete não encontrado.');
        }

        if ($bilhete->status !== 'open') {
            return back()->with('error', 'Apenas apostas abertas podem ser canceladas.');
        }

        if ($config->tempo_limite_camb_cancela_aposta) {
            $limite = Carbon::parse($bilhete->created_at)->addMinutes($config->tempo_limite_camb_cancela_aposta);
            if (Carbon::now()->gt($limite)) {
                return back()->with('error', 'Tempo limite para cancelamento expirado.');
            }
        }

        DB::beginTransaction();
        try {
            $cambista = \App\Models\User::find($user->id);

            $cambista->quantidade_aposta = max(0, $cambista->quantidade_aposta - 1);
            $cambista->entradas = max(0, $cambista->entradas - $bilhete->amount);
            $cambista->comissoes = max(0, $cambista->comissoes - $bilhete->commission_amount);

            $cambista->balance += $bilhete->amount;
            $cambista->saldo_simples += $bilhete->amount;

            $cambista->save();

            DB::table('bets')->where('id', $bilhete->id)->update(['status' => 'cancelled']);

            DB::table('bet_items')->where('bet_id', $bilhete->id)->update(['status' => 'cancelled']);

            DB::table('transactions')->insert([
                'site_id'     => $siteId,
                'user_id'     => $user->id,
                'type'        => 'bet_cancelled',
                'amount'      => $bilhete->amount,
                'gateway_ref' => "aposta_{$bilhete->id}_cambista_cancel",
                'status'      => 'completed',
                'description' => "Bilhete #{$bilhete->ticket_code} cancelado pelo cambista",
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Bilhete cancelado com sucesso. Valor estornado ao saldo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao cancelar: ' . $e->getMessage());
        }
    }
}
