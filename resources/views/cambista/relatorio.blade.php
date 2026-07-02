@extends('cambista.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Relatório</h5>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('cambista.relatorio.filtrar') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-3 col-sm-6">
                <label class="form-label small">Data Início</label>
                <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ $dataInicio }}">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label small">Data Fim</label>
                <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ $dataFim }}">
            </div>
            <div class="col-md-2 col-sm-12">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total Apostado</div>
                <div class="fs-5 fw-bold text-primary">R$ {{ number_format($totalApostado, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Total Pago (Ganhos)</div>
                <div class="fs-5 fw-bold text-danger">R$ {{ number_format($totalRetorno, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Lucro Banca</div>
                <div class="fs-5 fw-bold {{ $lucro >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($lucro, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Comissão</div>
                <div class="fs-5 fw-bold" style="color:#8b5cf6;">R$ {{ number_format($totalComissao, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Detalhamento por Dia</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data</th>
                        <th class="text-center">Qtd Apostas</th>
                        <th>Total Apostado</th>
                        <th>Total Pago</th>
                        <th>Comissão</th>
                        <th>Lucro</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porDia as $dia)
                    <tr>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($dia['data'])->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $dia['qtd_apostas'] }}</td>
                        <td>R$ {{ number_format($dia['total_apostado'], 2, ',', '.') }}</td>
                        <td class="text-danger">R$ {{ number_format($dia['totalretorno'], 2, ',', '.') }}</td>
                        <td style="color:#8b5cf6;">R$ {{ number_format($dia['comissao'], 2, ',', '.') }}</td>
                        <td class="{{ $dia['lucro'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            R$ {{ number_format($dia['lucro'], 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum dado no período selecionado.</td></tr>
                    @endforelse
                </tbody>
                @if($porDia->count() > 0)
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td>TOTAL</td>
                        <td class="text-center">{{ $totalApostas }}</td>
                        <td>R$ {{ number_format($totalApostado, 2, ',', '.') }}</td>
                        <td class="text-danger">R$ {{ number_format($totalRetorno, 2, ',', '.') }}</td>
                        <td style="color:#8b5cf6;">R$ {{ number_format($totalComissao, 2, ',', '.') }}</td>
                        <td class="{{ $lucro >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($lucro, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
