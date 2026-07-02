@extends('gerente.layouts.app')
@section('title', 'Detalhe do Cambista')
@section('content_header')
    <h1>
        <i class="fas fa-user text-primary mr-2"></i> {{ $cambista->name }}
        <small class="text-muted">Detalhe do Cambista</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="font-weight-bold mb-0"><i class="fas fa-id-card mr-2"></i> Informações</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size: 0.85rem;">
                    <tr><td class="text-muted font-weight-bold">Nome</td><td>{{ $cambista->name }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Username</td><td>{{ $cambista->username }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Contato</td><td>{{ $cambista->contato ?? '-' }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Status</td>
                        <td>
                            @if($cambista->status)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-danger">Inativo</span>
                            @endif
                        </td>
                    </tr>
                    <tr><td class="text-muted font-weight-bold">Criado em</td><td>{{ $cambista->created_at ? $cambista->created_at->format('d/m/Y H:i') : '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="font-weight-bold mb-0"><i class="fas fa-wallet mr-2"></i> Saldos</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size: 0.85rem;">
                    <tr><td class="text-muted font-weight-bold">Saldo Simples</td><td class="font-weight-bold text-success">R$ {{ number_format($cambista->balance, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Saldo Casadinha</td><td class="font-weight-bold text-info">R$ {{ number_format($cambista->balance_bonus ?? 0, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Saldo Loto</td><td class="font-weight-bold text-warning">R$ {{ number_format($cambista->saldo_loto ?? 0, 2, ',', '.') }}</td></tr>
                    <tr style="border-top: 2px solid #dee2e6;"><td class="font-weight-bold">Saldo Total</td><td class="font-weight-bold text-dark" style="font-size: 1.1rem;">R$ {{ number_format($cambista->balance + ($cambista->balance_bonus ?? 0) + ($cambista->saldo_loto ?? 0), 2, ',', '.') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-white">
                <h5 class="font-weight-bold mb-0"><i class="fas fa-chart-line mr-2"></i> Financeiro</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0" style="font-size: 0.85rem;">
                    <tr><td class="text-muted font-weight-bold">Entradas</td><td class="font-weight-bold">R$ {{ number_format($cambista->entradas, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Saídas</td><td class="font-weight-bold text-danger">R$ {{ number_format($cambista->saidas, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Comissões</td><td class="font-weight-bold text-primary">R$ {{ number_format($cambista->comissoes, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Lançamentos</td><td class="font-weight-bold">R$ {{ number_format($cambista->lancamentos ?? 0, 2, ',', '.') }}</td></tr>
                    <tr><td class="text-muted font-weight-bold">Qtd. Apostas</td><td class="font-weight-bold">{{ $cambista->quantidade_aposta ?? 0 }}</td></tr>
                    <tr style="border-top: 2px solid #dee2e6;"><td class="font-weight-bold">Total Apostado</td><td class="font-weight-bold">R$ {{ number_format($totalApostado, 2, ',', '.') }}</td></tr>
                    <tr><td class="font-weight-bold">Total Prêmios</td><td class="font-weight-bold text-danger">R$ {{ number_format($totalPremios, 2, ',', '.') }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="font-weight-bold mb-0"><i class="fas fa-receipt mr-2"></i> Últimas 20 Apostas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="bg-light">
                    <tr style="font-size: 0.78rem; text-transform: uppercase;">
                        <th class="px-3 py-2">Cupom</th>
                        <th class="py-2">Data</th>
                        <th class="py-2">Modalidade</th>
                        <th class="py-2 text-right">Apostado</th>
                        <th class="py-2 text-right">Retorno</th>
                        <th class="py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasApostas as $a)
                        <tr style="font-size: 0.82rem;">
                            <td class="px-3 font-weight-bold">{{ $a->codigo_bilhete ?? $a->cupom ?? '-' }}</td>
                            <td class="text-muted">{{ $a->created_at->format('d/m H:i') }}</td>
                            <td>{{ $a->modalidade ?? '-' }}</td>
                            <td class="text-right">R$ {{ number_format($a->valor_apostado, 2, ',', '.') }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format($a->retorno_possivel, 2, ',', '.') }}</td>
                            <td class="text-center">
                                @php $st = strtolower($a->status); @endphp
                                @if($st === 'ganhou' || $st === 'venceu')
                                    <span class="badge badge-success">{{ $a->status }}</span>
                                @elseif($st === 'perdeu')
                                    <span class="badge badge-danger">{{ $a->status }}</span>
                                @elseif($st === 'cancelado')
                                    <span class="badge badge-warning">{{ $a->status }}</span>
                                @else
                                    <span class="badge badge-info">{{ $a->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Nenhuma aposta encontrada</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
