@extends('cambista.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('cambista.bilhetes') }}" class="text-decoration-none text-muted me-2"><i class="fas fa-arrow-left"></i> Voltar</a>
        <h5 class="d-inline mb-0">Bilhete #{{ $bilhete->codigo_bilhete ?? $bilhete->id }}</h5>
    </div>
    <div>
        @if($bilhete->status == 'Ganhou')
            <span class="badge bg-success fs-6">Ganhou</span>
        @elseif($bilhete->status == 'Perdeu')
            <span class="badge bg-danger fs-6">Perdeu</span>
        @elseif($bilhete->status == 'Cancelado')
            <span class="badge bg-dark fs-6">Cancelado</span>
        @else
            <span class="badge bg-warning text-dark fs-6">Aberto</span>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list-ul me-2"></i>Palpites</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Confronto</th>
                                <th>Mercado</th>
                                <th>Seleção</th>
                                <th>Odd</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bilhete->palpites as $palpite)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $palpite->home_team ?? '-' }}</div>
                                    <div class="text-muted small">vs {{ $palpite->away_team ?? '-' }}</div>
                                </td>
                                <td>{{ $palpite->market_name ?? '-' }}</td>
                                <td>{{ $palpite->selection_label ?? '-' }}</td>
                                <td><strong>{{ number_format($palpite->selection_odd ?? 1, 2, ',', '.') }}x</strong></td>
                                <td>
                                    @if(($palpite->status ?? '') == 'Ganhou')
                                        <span class="badge bg-success">✓</span>
                                    @elseif(($palpite->status ?? '') == 'Perdeu')
                                        <span class="badge bg-danger">✗</span>
                                    @elseif(($palpite->status ?? '') == 'Cancelado')
                                        <span class="badge bg-dark">—</span>
                                    @else
                                        <span class="badge bg-secondary">Aguardando</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Sem palpites</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0 small">
                    <tr><td class="text-muted">Código</td><td class="fw-bold">{{ $bilhete->codigo_bilhete ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Tipo</td><td>{{ $bilhete->tipo ?? 'Simples' }}</td></tr>
                    <tr><td class="text-muted">Modalidade</td><td>{{ $bilhete->modalidade ?? 'Futebol' }}</td></tr>
                    <tr><td class="text-muted">Valor Apostado</td><td class="fw-bold text-primary">R$ {{ number_format($bilhete->valor_apostado, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted">Retorno Possível</td><td class="fw-bold text-success">R$ {{ number_format($bilhete->retorno_possivel, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted">Cotação</td><td>{{ number_format($bilhete->cotacao ?? 1, 2, ',', '.') }}x</td></tr>
                    <tr><td class="text-muted">Comissão</td><td>R$ {{ number_format($bilhete->comicao ?? 0, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted">Data</td><td>{{ \Carbon\Carbon::parse($bilhete->created_at)->format('d/m/Y H:i:s') }}</td></tr>
                    <tr><td class="text-muted">Cliente</td><td>{{ $bilhete->cliente ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        @if($bilhete->status == 'open' && $podeCancelar)
        <div class="card border-0 shadow-sm border-top border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Cancelar Bilhete</h6>
                <p class="small text-muted mb-2">O valor será estornado ao seu saldo.</p>
                <form method="POST" action="{{ route('cambista.bilhete.cancel', $bilhete->id) }}" onsubmit="return confirm('Tem certeza que deseja cancelar este bilhete?')">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-times-circle me-1"></i>Cancelar Bilhete
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
