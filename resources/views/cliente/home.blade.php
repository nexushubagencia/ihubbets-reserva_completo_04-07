@extends('cliente.layouts.app')

@section('title', 'Painel')

@section('content')
<div class="mb-3">
    <h5 style="font-weight:700; margin-bottom:2px;">Bem-vindo, {{ $user->name }}!</h5>
    <small style="color:var(--text-muted);">Gerencie suas apostas e finanças</small>
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

<div class="card-client">
    <div class="card-title">Apostas Ativas</div>
    <div class="card-value">{{ $apostasAtivas }}</div>
</div>

<div class="quick-actions">
    <a href="{{ route('cliente.apostas') }}" class="quick-action">
        <i class="fas fa-ticket-alt"></i>
        Apostas
    </a>
    <a href="{{ route('cliente.depositos') }}" class="quick-action">
        <i class="fas fa-qrcode"></i>
        Depositar
    </a>
    <a href="{{ route('cliente.saques') }}" class="quick-action">
        <i class="fas fa-money-bill-wave"></i>
        Saque
    </a>
</div>

<div class="card-client">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="card-title mb-0">Últimas Apostas</div>
        <a href="{{ route('cliente.apostas') }}" style="font-size:12px; color:var(--primary); text-decoration:none;">Ver todas</a>
    </div>

    @if($ultimasApostas->isEmpty())
        <div class="empty-state" style="padding:24px;">
            <i class="fas fa-ticket-alt" style="font-size:32px;"></i>
            <p style="margin-top:8px;">Nenhuma aposta encontrada</p>
        </div>
    @else
        @foreach($ultimasApostas as $aposta)
            <a href="{{ route('cliente.aposta-detail', $aposta->id) }}" class="list-item">
                <div>
                    <div style="font-weight:600; font-size:13px;">
                        {{ $aposta->codigo_bilhete ?? 'Bilhete #' . $aposta->id }}
                    </div>
                    <small style="color:var(--text-muted);">
                        {{ $aposta->created_at->format('d/m/Y H:i') }} · {{ $aposta->total_palpites ?? 0 }} palpites
                    </small>
                </div>
                <div class="text-end">
                    <div style="font-weight:600; font-size:13px;">R$ {{ number_format($aposta->valor_apostado, 2, ',', '.') }}</div>
                    @php
                        $statusClass = 'aberto';
                        if (in_array($aposta->status, ['Venceu', 'Ganhou', 'won'])) $statusClass = 'ganhou';
                        elseif (in_array($aposta->status, ['Perdeu', 'lost'])) $statusClass = 'perdeu';
                        elseif (in_array($aposta->status, ['Cancelado', 'cancelled'])) $statusClass = 'cancelado';
                    @endphp
                    <span class="badge-status badge-{{ $statusClass }}">{{ $aposta->status }}</span>
                </div>
            </a>
        @endforeach
    @endif
</div>
@endsection
