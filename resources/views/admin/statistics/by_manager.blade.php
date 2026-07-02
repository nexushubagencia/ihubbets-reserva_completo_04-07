@extends('adminlte::page')

@section('title', 'Relatório de Gerentes')

@section('content_header')
    <h1>Relatório de Gerentes</h1>
@stop

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-success">
        <h3 class="card-title text-white fw-bold"><i class="fas fa-briefcase me-1"></i> Desempenho por Gerente</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Gerente</th>
                        <th class="text-center">Apostas</th>
                        <th class="text-right">Entradas (R$)</th>
                        <th class="text-right">Prêmios (R$)</th>
                        <th class="text-right">Comissões Pagas (R$)</th>
                        <th class="text-right fw-bold">Lucro da Banca (R$)</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $manager)
                    <tr>
                        <td class="align-middle">
                            <div class="fw-bold">{{ $manager->name }}</div>
                            <small class="text-muted">{{ $manager->username }}</small>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-info">{{ $manager->total_apostas }}</span>
                        </td>
                        <td class="text-right align-middle fw-bold">R$ {{ number_format($manager->entradas, 2, ',', '.') }}</td>
                        <td class="text-right align-middle text-danger">R$ {{ number_format($manager->saidas, 2, ',', '.') }}</td>
                        <td class="text-right align-middle">R$ {{ number_format($manager->comissoes_cambistas, 2, ',', '.') }}</td>
                        <td class="text-right align-middle {{ $manager->lucro_banca >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            R$ {{ number_format($manager->lucro_banca, 2, ',', '.') }}
                        </td>
                        <td class="text-center align-middle">
                            <a href="{{ route('admin.caixa-gerente', ['user_id' => $manager->id]) }}" class="btn btn-xs btn-outline-success">
                                <i class="fas fa-search-dollar"></i> Detalhes
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Nenhum gerente com movimentação no período.</td>
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
    .text-right { text-align: right; }
</style>
@stop
