@extends('cliente.layouts.app')

@section('title', 'Financeiro')

@section('content')
<div class="mb-3">
    <h5 style="font-weight:700; margin:0;">Financeiro</h5>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
    <div class="card-client">
        <div class="card-title">Saldo Disponível</div>
        <div class="card-value text-primary">R$ {{ number_format($saldoDisponivel, 2, ',', '.') }}</div>
    </div>
    <div class="card-client">
        <div class="card-title">Saldo Bônus</div>
        <div class="card-value text-warning">R$ {{ number_format($saldoBonus, 2, ',', '.') }}</div>
    </div>
</div>

<div class="quick-actions" style="grid-template-columns:1fr 1fr; margin-bottom:16px;">
    <a href="{{ route('cliente.depositos') }}" class="quick-action">
        <i class="fas fa-qrcode"></i>
        Depósitos
    </a>
    <a href="{{ route('cliente.saques') }}" class="quick-action">
        <i class="fas fa-money-bill-wave"></i>
        Solicitar Saque
    </a>
</div>

<ul class="nav nav-tabs mb-3" style="border-bottom:2px solid var(--border);">
    <li class="nav-item">
        <a class="nav-link active" href="#">Últimos Movimentos</a>
    </li>
</ul>

@if($depositos->isEmpty() && $saques->isEmpty())
    <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <p>Nenhum movimento financeiro encontrado</p>
    </div>
@else
    @php
        $movimentos = collect();
        foreach($depositos as $dep) {
            $movimentos->push((object)[
                'tipo' => 'Depósito',
                'valor' => $dep->amount,
                'status' => $dep->status,
                'data' => $dep->created_at,
                'icone' => 'fa-arrow-down',
                'cor' => 'var(--success)',
            ]);
        }
        foreach($saques as $saq) {
            $movimentos->push((object)[
                'tipo' => 'Saque',
                'valor' => $saq->amount,
                'status' => $saq->status,
                'data' => $saq->created_at,
                'icone' => 'fa-arrow-up',
                'cor' => 'var(--danger)',
            ]);
        }
        $movimentos = $movimentos->sortByDesc('data')->take(10);
    @endphp

    @foreach($movimentos as $mov)
        <div class="card-client" style="padding:12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px; height:36px; border-radius:50%; background:{{ $mov->cor }}15; display:flex; align-items:center; justify-content:center;">
                        <i class="fas {{ $mov->icone }}" style="color:{{ $mov->cor }}; font-size:14px;"></i>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:13px;">{{ $mov->tipo }}</div>
                        <small style="color:var(--text-muted);">{{ $mov->data->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
                <div class="text-end">
                    <div style="font-weight:700; color:{{ $mov->cor }};">
                        {{ $mov->tipo == 'Depósito' ? '+' : '-' }}R$ {{ number_format($mov->valor, 2, ',', '.') }}
                    </div>
                    <small style="color:var(--text-muted); text-transform:capitalize;">{{ $mov->status }}</small>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
