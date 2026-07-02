@extends('adminlte::page')

@section('title', 'Lançamentos e Ajustes Financeiros')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-wallet text-success"></i> Gestão Financeira <span class="badge badge-success">V2 Pro</span></h1>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Novo Lançamento -->
    <div class="col-md-5">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title fw-bold"><i class="fas fa-plus-circle me-1"></i> Novo Ajuste de Saldo</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.finance.adjustments.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Usuário Destino (Cambista/Gerente)</label>
                        <select name="user_id" class="form-control select2" required>
                            <option value="">Selecione um usuário...</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}">{{ $seller->username }} ({{ ucfirst($seller->role) }}) - Saldo: R$ {{ number_format($seller->balance, 2, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Tipo de Lançamento</label>
                        <select name="type" class="form-control" required>
                            <option value="deposit">Crédito (Adicionar Saldo)</option>
                            <option value="withdraw">Débito (Remover Saldo)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Valor (R$)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-success border-success text-white">R$</span>
                            </div>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label>Observação / Justificativa</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Ex: Acerto da semana..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 btn-lg shadow-sm">
                        <i class="fas fa-check-circle"></i> Confirmar Lançamento
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Histórico Recente -->
    <div class="col-md-7">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title fw-bold"><i class="fas fa-history me-1"></i> Transações Recentes</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                <td><span class="badge badge-light border">{{ $transaction->username }}</span></td>
                                <td>
                                    <span class="badge badge-{{ $transaction->type == 'deposit' ? 'success' : 'danger' }}">
                                        {{ $transaction->type == 'deposit' ? 'Crédito' : 'Débito' }}
                                    </span>
                                </td>
                                <td class="font-weight-bold {{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                </td>
                                <td class="small text-muted">{{ $transaction->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-light"></i><br>
                                    Nenhum lançamento recente encontrado nesta banca.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card-header { padding: 15px; border-bottom: 1px solid rgba(0,0,0,.125); }
    .btn { border-radius: 8px; font-weight: 600; }
    .select2-container .select2-selection--single { height: 38px !important; }
</style>
@stop
