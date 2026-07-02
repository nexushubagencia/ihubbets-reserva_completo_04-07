@extends('adminlte::page')

@section('title', 'Mapa de Apostas')

@section('content_header')
    <h1><i class="fas fa-map-marked-alt text-warning"></i> Mapa de Apostas — Exposição por Confronto</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('admin.risk.dashboard') }}" class="btn btn-outline-danger mb-3"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard de Riscos</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Exposição da Banca por Confronto / Mercado</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Confronto</th>
                            <th>Data</th>
                            <th>Mercado</th>
                            <th>Opção</th>
                            <th>Total Apostado</th>
                            <th>Exposição Máxima</th>
                            <th>Bilhetes</th>
                            <th>Risco</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapData as $item)
                        <tr>
                            <td class="font-weight-bold">{{ $item->confronto }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->data_evento)->format('d/m H:i') }}</td>
                            <td>{{ $item->mercado }}</td>
                            <td><span class="badge badge-primary">{{ $item->opcao }}</span></td>
                            <td class="text-success font-weight-bold">R$ {{ number_format($item->total_apostado, 2, ',', '.') }}</td>
                            <td class="text-danger font-weight-bold">R$ {{ number_format($item->exposicao_maxima, 2, ',', '.') }}</td>
                            <td>{{ $item->quantidade_bilhetes }}</td>
                            <td>
                                @php
                                    $ratio = $item->total_apostado > 0 ? $item->exposicao_maxima / $item->total_apostado : 0;
                                @endphp
                                @if($ratio > 10)
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> CRÍTICO</span>
                                @elseif($ratio > 5)
                                    <span class="badge badge-warning">ALTO</span>
                                @else
                                    <span class="badge badge-success">NORMAL</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                Nenhuma exposição de risco no momento.
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
    .table td, .table th { vertical-align: middle; }
    .badge-danger { animation: pulse 2s infinite; }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
</style>
@stop
