@extends('gerente.layouts.app')
@section('title', 'Cambistas')
@section('content_header')
    <h1><i class="fas fa-users text-primary mr-2"></i> Meus Cambistas</h1>
@stop

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white border-0 d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold"><i class="fas fa-list mr-2"></i> Lista de Cambistas</h3>
        <span class="badge badge-light px-3 py-2">{{ $cambistas->count() }} registro(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-primary text-white">
                    <tr style="font-size: 0.78rem; text-transform: uppercase;">
                        <th class="px-3 py-3">Nome</th>
                        <th class="py-3">Username</th>
                        <th class="py-3 text-right">Saldo Simples</th>
                        <th class="py-3 text-right">Saldo Casadinha</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cambistas as $c)
                        <tr style="font-size: 0.85rem;">
                            <td class="px-3 font-weight-bold">{{ $c['name'] }}</td>
                            <td class="text-muted">{{ $c['username'] }}</td>
                            <td class="text-right font-weight-bold text-success">R$ {{ number_format($c['saldo_simples'], 2, ',', '.') }}</td>
                            <td class="text-right font-weight-bold text-info">R$ {{ number_format($c['saldo_casadinha'], 2, ',', '.') }}</td>
                            <td class="text-center">
                                @if($c['status'])
                                    <span class="badge badge-success px-2 py-1">Ativo</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1">Inativo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('gerente.cambista-detail', $c['id']) }}" class="btn btn-sm btn-primary px-3" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('gerente.caixa.detail', $c['id']) }}" class="btn btn-sm btn-success px-3" title="Caixa">
                                    <i class="fas fa-university"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                Nenhum cambista encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr style="font-size: 0.85rem;">
                        <td class="px-3" colspan="2">TOTAIS ({{ $cambistas->count() }})</td>
                        <td class="text-right text-success">R$ {{ number_format($cambistas->sum('saldo_simples'), 2, ',', '.') }}</td>
                        <td class="text-right text-info">R$ {{ number_format($cambistas->sum('saldo_casadinha'), 2, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop
