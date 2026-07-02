<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Core\Unified\FinanceEngine;
use Illuminate\Support\Facades\DB;

/**
 * 💸 FinancialAdjustmentController - Gestão de Lançamentos Manuais
 */
class FinancialAdjustmentController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $currentUser = auth()->user();

        // Lista de colaboradores que podem receber lançamentos
        $query = User::where('site_id', $siteId)
            ->whereIn('role', ['seller', 'manager']);
            
        if ($currentUser->role === 'manager') {
            $query->where('gerente_id', $currentUser->id);
        }

        $sellers = $query->get();
        
        $recentTransactions = DB::table('transactions')
            ->join('master_users', 'transactions.user_id', '=', 'master_users.id')
            ->where('transactions.site_id', $siteId)
            ->where('transactions.type', 'adjustment')
            ->select('transactions.*', 'master_users.username')
            ->orderBy('transactions.created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.finance.adjustments', compact('sellers', 'recentTransactions'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        if (!in_array($currentUser->role, ['admin', 'manager'])) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:master_users,id',
            'type' => 'required|in:Crédito,Débito', // Mantendo labels legados para o front
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string'
        ]);

        try {
            FinanceEngine::addAdjustment(
                $request->user_id, 
                $request->type, 
                $request->amount, 
                $request->description
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
