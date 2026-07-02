<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TransactionReportController extends Controller
{
    /**
     * Exibe a view do relatório de transações.
     */
    public function indexView()
    {
        $siteId = config('tenant.site_id', 1);
        $users = User::where('site_id', $siteId)->orderBy('name', 'asc')->get();
        return view('admin.relatorio-transacoes', compact('users'));
    }

    /**
     * Retorna a lista de transações filtrada (JSON para DataTables).
     */
    public function index(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        $dateStart = ($request->date1 ?? date('Y-m-d')) . ' 00:00:00';
        $dateEnd = ($request->date2 ?? date('Y-m-d')) . ' 23:59:59';

        $query = DB::table('transactions')
            ->join('master_users', 'transactions.user_id', '=', 'master_users.id')
            ->select(
                'transactions.*',
                'master_users.name as user_name',
                'master_users.role as user_role'
            )
            ->where('transactions.site_id', $siteId)
            ->whereBetween('transactions.created_at', [$dateStart, $dateEnd]);

        if ($request->user_id && $request->user_id !== 'Todos') {
            $query->where('transactions.user_id', $request->user_id);
        }

        if ($request->type && $request->type !== 'Todos') {
            $query->where('transactions.type', $request->type);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')->get();

        // Tradução de tipos para exibição premium
        $typesMap = [
            'bet_placed'        => 'Aposta Realizada',
            'bet_placed_bonus'  => 'Aposta (Bônus)',
            'bet_payout'        => 'Prêmio Pago',
            'bet_cancelled'     => 'Bilhete Cancelado',
            'commission'        => 'Comissão',
            'deposit'           => 'Depósito PIX',
            'withdrawal'        => 'Saque solicitado',
            'withdrawal_refund' => 'Saque Rejeitado (Estorno)',
            'manual_credit'     => 'Crédito Manual',
            'manual_debit'      => 'Débito Manual',
            'cashier_settlement'=> 'Fechamento de Caixa',
        ];

        $transactions->transform(function ($t) use ($typesMap) {
            $t->type_label = $typesMap[$t->type] ?? $t->type;
            $t->amount_formatted = 'R$ ' . number_format($t->amount, 2, ',', '.');
            $t->date_formatted = date('d/m/Y H:i:s', strtotime($t->created_at));
            return $t;
        });

        return response()->json($transactions);
    }
}
