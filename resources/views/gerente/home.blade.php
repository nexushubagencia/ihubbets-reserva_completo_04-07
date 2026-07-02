@extends('gerente.layouts.app')
@section('title', 'Dashboard - Gerente')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0" style="font-weight: 700; font-size: 1.4rem; color: #1e293b;">
            <i class="fas fa-tachometer-alt text-primary mr-2"></i> Dashboard
        </h1>
    </div>
@stop

@section('content')
<style>
    .dash-card {
        border-radius: 12px;
        padding: 22px 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        min-height: 120px;
        box-shadow: 0 4px 15px rgba(0,0,0,.15);
    }
    .dash-card .label-top {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 4px;
    }
    .dash-card .value {
        font-size: 2rem;
        font-weight: 900;
        line-height: 1.1;
    }
    .dash-card .sub {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 10px;
    }
    .dash-card .bg-icon {
        position: absolute;
        top: -15px;
        right: -15px;
        width: 90px;
        height: 90px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .section-box {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #fff;
        margin-bottom: 20px;
    }
    .section-box .sec-header {
        border-radius: 10px 10px 0 0;
        padding: 12px 18px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #fff;
    }
    .cambista-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
    }
    .cambista-row:last-child { border-bottom: none; }
    .cambista-name { font-weight: 600; color: #1e293b; }
    .cambista-saldo { font-weight: 700; color: #10b981; }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="dash-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="bg-icon"></div>
                <div class="label-top">Meus Cambistas</div>
                <div class="value">{{ $totalCambistas }}</div>
                <div class="sub"><i class="fas fa-users mr-1"></i> Vinculados</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="dash-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="bg-icon"></div>
                <div class="label-top">Apostas Hoje</div>
                <div class="value">{{ $totalApostasHoje }}</div>
                <div class="sub"><i class="fas fa-ticket-alt mr-1"></i> Processadas</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="dash-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="bg-icon"></div>
                <div class="label-top">Volume Hoje</div>
                <div class="value">R$ {{ number_format($volumeHoje, 2, ',', '.') }}</div>
                <div class="sub"><i class="fas fa-coins mr-1"></i> Total apostado</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="dash-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="bg-icon"></div>
                <div class="label-top">Minha Comissão</div>
                <div class="value">R$ {{ number_format($minhaComissao, 2, ',', '.') }}</div>
                <div class="sub"><i class="fas fa-handshake mr-1"></i> Acumulada</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="section-box">
                <div class="sec-header" style="background: #10b981;">
                    <i class="fas fa-users mr-2"></i> Cambistas e Saldos
                </div>
                <div class="p-3">
                    @forelse($cambistas as $c)
                        <div class="cambista-row">
                            <div>
                                <span class="cambista-name">{{ $c->name }}</span>
                                <br>
                                <small class="text-muted">{{ $c->username }}</small>
                            </div>
                            <div class="text-right">
                                <span class="cambista-saldo">R$ {{ number_format($c->balance + ($c->balance_bonus ?? 0), 2, ',', '.') }}</span>
                                <br>
                                <small class="text-muted">{{ $c->quantidade_aposta ?? 0 }} apostas</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <p>Nenhum cambista vinculado</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="section-box">
                <div class="sec-header" style="background: #3b82f6;">
                    <i class="fas fa-receipt mr-2"></i> Últimas 10 Apostas
                </div>
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="bg-light">
                                <tr style="font-size: 0.75rem; text-transform: uppercase;">
                                    <th class="px-3 py-2">Cupom</th>
                                    <th class="py-2">Vendedor</th>
                                    <th class="py-2 text-right">Valor</th>
                                    <th class="py-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasApostas as $a)
                                    <tr style="font-size: 0.82rem;">
                                        <td class="px-3 font-weight-bold">{{ $a->codigo_bilhete ?? $a->cupom ?? '-' }}</td>
                                        <td>{{ $a->user ? $a->user->name : '-' }}</td>
                                        <td class="text-right font-weight-bold">R$ {{ number_format($a->valor_apostado, 2, ',', '.') }}</td>
                                        <td class="text-center">
                                            @php $st = strtolower($a->status); @endphp
                                            @if($st === 'ganhou' || $st === 'venceu')
                                                <span class="badge badge-success px-2 py-1">{{ $a->status }}</span>
                                            @elseif($st === 'perdeu')
                                                <span class="badge badge-danger px-2 py-1">{{ $a->status }}</span>
                                            @elseif($st === 'cancelado')
                                                <span class="badge badge-warning px-2 py-1">{{ $a->status }}</span>
                                            @else
                                                <span class="badge badge-info px-2 py-1">{{ $a->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Nenhuma aposta encontrada</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
