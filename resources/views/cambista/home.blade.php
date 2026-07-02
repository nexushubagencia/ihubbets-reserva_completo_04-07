@extends('cambista.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="fas fa-home me-2"></i>Dashboard</h5>
    <span class="text-muted">{{ now()->format('d/m/Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #3b82f6;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Saldo Simples</div>
                        <div class="fs-5 fw-bold text-primary">R$ {{ number_format($user->saldo_simples ?? $user->balance ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-2 rounded"><i class="fas fa-wallet text-primary"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #8b5cf6;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Saldo Casadinha</div>
                        <div class="fs-5 fw-bold" style="color:#8b5cf6;">R$ {{ number_format($user->saldo_casadinha ?? $user->balance_bonus ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="p-2 rounded" style="background:rgba(139,92,246,.1);"><i class="fas fa-layer-group" style="color:#8b5cf6;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Saldo Loto</div>
                        <div class="fs-5 fw-bold" style="color:#f59e0b;">R$ {{ number_format($user->saldo_loto ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="p-2 rounded" style="background:rgba(245,158,11,.1);"><i class="fas fa-dice" style="color:#f59e0b;"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Entradas Hoje</div>
                <div class="fw-bold text-success">R$ {{ number_format($entradasHoje, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Saídas Hoje</div>
                <div class="fw-bold text-danger">R$ {{ number_format($saidasHoje, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Comissões Hoje</div>
                <div class="fw-bold" style="color:#8b5cf6;">R$ {{ number_format($comissoesHoje, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Apostas Hoje</div>
                <div class="fw-bold text-primary">{{ $apostasHoje }}</div>
            </div>
        </div>
    </div>
</div>

@if($bilhetesAbertos > 0)
<div class="alert alert-info d-flex align-items-center mb-4" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    Você tem <strong class="mx-1">{{ $bilhetesAbertos }}</strong> bilhete(s) aberto(s) aguardando resultado.
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Últimas Apostas</h6>
        <a href="{{ route('cambista.bilhetes') }}" class="text-decoration-none small">Ver todas &rarr;</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Valor</th>
                        <th>Retorno</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasApostas as $aposta)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $aposta->codigo_bilhete ?? '-' }}</span></td>
                        <td>R$ {{ number_format($aposta->valor_apostado, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($aposta->retorno_possivel, 2, ',', '.') }}</td>
                        <td>
                            @if($aposta->status == 'Ganhou')
                                <span class="badge bg-success">Ganhou</span>
                            @elseif($aposta->status == 'Perdeu')
                                <span class="badge bg-danger">Perdeu</span>
                            @elseif($aposta->status == 'Cancelado')
                                <span class="badge bg-dark">Cancelado</span>
                            @else
                                <span class="badge bg-warning text-dark">Aberto</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($aposta->created_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Nenhuma aposta registrada ainda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
