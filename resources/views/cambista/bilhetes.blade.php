@extends('cambista.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Bilhetes</h5>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cambista.bilhetes') }}" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label small">Data Início</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ request('data_inicio') }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small">Data Fim</label>
                <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="Todos" {{ request('status') == 'Todos' || !request('status') ? 'selected' : '' }}>Todos</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aberto</option>
                    <option value="Ganhou" {{ request('status') == 'Ganhou' ? 'selected' : '' }}>Ganhou</option>
                    <option value="Perdeu" {{ request('status') == 'Perdeu' ? 'selected' : '' }}>Perdeu</option>
                    <option value="Cancelado" {{ request('status') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small">Código</label>
                <input type="text" name="codigo" class="form-control form-control-sm" placeholder="Buscar código..." value="{{ request('codigo') }}">
            </div>
            <div class="col-md-1 col-sm-12">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Palpites</th>
                        <th>Cotação</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bilhetes as $b)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $b->codigo_bilhete ?? '-' }}</span></td>
                        <td class="text-muted small">{{ \Carbon\Carbon::parse($b->created_at)->format('d/m/Y H:i') }}</td>
                        <td>R$ {{ number_format($b->valor_apostado, 2, ',', '.') }}</td>
                        <td>{{ $b->total_palpites ?? 1 }}</td>
                        <td>{{ number_format($b->cotacao ?? 1, 2, ',', '.') }}x</td>
                        <td>
                            @if($b->status == 'Ganhou')
                                <span class="badge bg-success">Ganhou</span>
                            @elseif($b->status == 'Perdeu')
                                <span class="badge bg-danger">Perdeu</span>
                            @elseif($b->status == 'Cancelado')
                                <span class="badge bg-dark">Cancelado</span>
                            @else
                                <span class="badge bg-warning text-dark">Aberto</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('cambista.bilhete-detail', $b->id) }}" class="btn btn-outline-primary btn-sm" title="Detalhes">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nenhum bilhete encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($bilhetes->hasPages())
    <div class="card-footer bg-white">
        {{ $bilhetes->links() }}
    </div>
    @endif
</div>
@endsection
