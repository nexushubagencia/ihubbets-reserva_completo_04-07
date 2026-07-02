@extends('adminlte::page')

@section('title', 'Gerenciamento de Riscos')

@section('content_header')
    <h1><i class="fas fa-shield-alt text-danger"></i> Gerenciamento de Riscos</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Filtros de Risco</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.risk.dashboard') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label class="mr-2">Ordenar por:</label>
                        <select name="filter" class="form-control">
                            <option value="potential_return" {{ request('filter', 'potential_return') == 'potential_return' ? 'selected' : '' }}>Maior Retorno Possível</option>
                            <option value="amount" {{ request('filter') == 'amount' ? 'selected' : '' }}>Maior Valor Apostado</option>
                            <option value="selections" {{ request('filter') == 'selections' ? 'selected' : '' }}>Mais Seleções</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="{{ route('admin.risk.map') }}" class="btn btn-outline-warning ml-2"><i class="fas fa-map-marked-alt"></i> Ver Mapa de Apostas</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Top 50 Bilhetes de Maior Risco</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor Apostado</th>
                            <th>Retorno Possível</th>
                            <th>Seleções</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topRisks as $bet)
                        <tr>
                            <td>{{ $bet->id }}</td>
                            <td><span class="badge badge-info">{{ $bet->external_code ?? '-' }}</span></td>
                            <td>{{ ucfirst($bet->type ?? '-') }}</td>
                            <td class="text-success font-weight-bold">R$ {{ number_format($bet->amount, 2, ',', '.') }}</td>
                            <td class="text-danger font-weight-bold">R$ {{ number_format($bet->potential_payout ?? $bet->potential_return ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $bet->total_selections ?? '-' }}</td>
                            <td>
                                @if($bet->status == 'pending' || $bet->status == 'open')
                                    <span class="badge badge-warning">Em Aberto</span>
                                @elseif($bet->status == 'won')
                                    <span class="badge badge-success">Ganhou</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($bet->status) }}</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($bet->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                Nenhum bilhete em aberto no momento.
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
</style>
@stop
