@extends('adminlte::page')

@section('title', 'Estatísticas Diárias')

@section('content_header')
    <h1>Estatísticas Diárias</h1>
@stop

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-success">
        <h3 class="card-title text-white fw-bold"><i class="fas fa-calendar-day me-1"></i> Desempenho dos Últimos 30 Dias</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Data</th>
                        <th class="text-center">Apostas</th>
                        <th class="text-right">Entradas (R$)</th>
                        <th class="text-right text-danger">Saídas (R$)</th>
                        <th class="text-right fw-bold">Lucro Líquido (R$)</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td class="align-middle fw-bold">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                        <td class="text-center align-middle"><span class="badge badge-info px-2">{{ $row->total_bets }}</span></td>
                        <td class="text-right align-middle text-success fw-bold">R$ {{ number_format($row->total_amount, 2, ',', '.') }}</td>
                        <td class="text-right align-middle text-danger">R$ {{ number_format($row->total_paid, 2, ',', '.') }}</td>
                        <td class="text-right align-middle {{ $row->lucro_dia >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            R$ {{ number_format($row->lucro_dia, 2, ',', '.') }}
                        </td>
                        <td class="text-center align-middle">
                            <a href="/admin/bilhetes?cupom=&date={{ $row->date }}" class="btn btn-xs btn-outline-primary" title="Ver Apostas">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3"></i><br>
                                Nenhum dado encontrado para o período.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top">
        <small class="text-muted">Os valores de "Saídas" referem-se a prêmios já ganhos e liquidados.</small>
    </div>
</div>
@stop

@section('css')
<style>
    .table th { border-top: none; }
    .text-right { text-align: right; }
</style>
@stop
