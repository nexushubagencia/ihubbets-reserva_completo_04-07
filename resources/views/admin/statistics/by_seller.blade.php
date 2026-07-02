@extends('adminlte::page')

@section('title', 'Relatório de Cambistas')

@section('content_header')
    <h1>Relatório de Cambistas</h1>
@stop

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-success">
        <h3 class="card-title text-white fw-bold"><i class="fas fa-users me-1"></i> Desempenho por Vendedor</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Cambista</th>
                        <th class="text-center">Apostas</th>
                        <th class="text-right">Entradas (R$)</th>
                        <th class="text-right">Prêmios (R$)</th>
                        <th class="text-right text-warning">Comissão (R$)</th>
                        <th class="text-right fw-bold">Saldo Banca (R$)</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                    <tr>
                        <td class="align-middle">
                            <div class="fw-bold">{{ $seller->name }}</div>
                            <small class="text-muted">{{ $seller->username }}</small>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-info">{{ $seller->total_apostas }}</span>
                        </td>
                        <td class="text-right align-middle fw-bold">R$ {{ number_format($seller->entradas, 2, ',', '.') }}</td>
                        <td class="text-right align-middle text-danger">R$ {{ number_format($seller->saidas, 2, ',', '.') }}</td>
                        <td class="text-right align-middle text-orange">R$ {{ number_format($seller->comissoes, 2, ',', '.') }}</td>
                        <td class="text-right align-middle {{ $seller->lucro >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            R$ {{ number_format($seller->lucro, 2, ',', '.') }}
                        </td>
                        <td class="text-center align-middle">
                            <div class="btn-group">
                                <a href="{{ route('admin.report.print', ['type' => 'cambista', 'id' => $seller->id]) }}" class="btn btn-xs btn-default" title="Recibo Completo">
                                    <i class="fas fa-print"></i>
                                </a>
                                <a href="{{ route('admin.caixa-cambista', ['user_id' => $seller->id]) }}" class="btn btn-xs btn-outline-success" title="Ver Detalhes">
                                    <i class="fas fa-search-dollar"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Nenhum cambista com movimentação no período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .text-orange { color: #f39c12 !important; }
    .text-right { text-align: right; }
</style>
@stop
