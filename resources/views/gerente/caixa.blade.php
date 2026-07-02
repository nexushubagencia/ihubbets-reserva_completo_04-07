@extends('gerente.layouts.app')
@section('title', 'Caixa Financeiro')
@section('content_header')
    <h1><i class="fas fa-university text-primary mr-2"></i> Caixa dos Cambistas</h1>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Entradas</span>
                <span class="info-box-number font-weight-bold">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Saídas</span>
                <span class="info-box-number font-weight-bold">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-handshake"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Comissões</span>
                <span class="info-box-number font-weight-bold">R$ {{ number_format($totalComissoes, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Saldos</span>
                <span class="info-box-number font-weight-bold">R$ {{ number_format($totalSaldo, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-table mr-2"></i> Caixa por Cambista</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-primary text-white">
                    <tr style="font-size: 0.78rem; text-transform: uppercase;">
                        <th class="px-3 py-3">Cambista</th>
                        <th class="py-3 text-right">Entradas</th>
                        <th class="py-3 text-right">Saídas</th>
                        <th class="py-3 text-right">Comissões</th>
                        <th class="py-3 text-right">Saldo Total</th>
                        <th class="py-3 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cambistas as $c)
                        <tr style="font-size: 0.85rem;">
                            <td class="px-3 font-weight-bold">{{ $c['name'] }}</td>
                            <td class="text-right text-success font-weight-bold">R$ {{ number_format($c['entradas'], 2, ',', '.') }}</td>
                            <td class="text-right text-danger font-weight-bold">R$ {{ number_format($c['saidas'], 2, ',', '.') }}</td>
                            <td class="text-right text-primary font-weight-bold">R$ {{ number_format($c['comissoes'], 2, ',', '.') }}</td>
                            <td class="text-right font-weight-bold" style="font-size: 0.95rem;">R$ {{ number_format($c['saldo_total'], 2, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('gerente.caixa.detail', $c['id']) }}" class="btn btn-sm btn-outline-primary px-3">
                                    <i class="fas fa-eye mr-1"></i> Detalhes
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum cambista encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-dark text-white font-weight-bold">
                    <tr style="font-size: 0.85rem;">
                        <td class="px-3">TOTAL</td>
                        <td class="text-right">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($totalComissoes, 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($totalSaldo, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop
