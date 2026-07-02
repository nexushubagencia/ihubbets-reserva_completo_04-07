@extends('cliente.layouts.app')

@section('title', 'Depósitos')

@section('content')
<div class="mb-3">
    <a href="{{ route('cliente.financeiro') }}" style="color:var(--primary); text-decoration:none; font-size:13px;">
        <i class="fas fa-arrow-left me-1"></i> Financeiro
    </a>
</div>

<div class="card-client">
    <h5 style="font-weight:700; margin:0 0 4px 0;">Depósitos</h5>
    <small style="color:var(--text-muted);">Histórico de todas as suas transações de depósito</small>
</div>

@if($depositos->isEmpty())
    <div class="empty-state">
        <i class="fas fa-qrcode"></i>
        <p>Nenhum depósito realizado</p>
    </div>
@else
    @foreach($depositos as $deposito)
        <div class="card-client" style="padding:12px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-weight:600; font-size:13px;">Depósito PIX</div>
                    <small style="color:var(--text-muted);">{{ $deposito->created_at->format('d/m/Y H:i') }}</small>
                </div>
                <div class="text-end">
                    <div style="font-weight:700; color:var(--success);">+R$ {{ number_format($deposito->amount, 2, ',', '.') }}</div>
                    @php
                        $statusLabel = $deposito->status;
                        $badgeClass = 'badge-aberto';
                        if ($deposito->status === 'approved') { $badgeClass = 'badge-ganhou'; $statusLabel = 'Aprovado'; }
                        elseif ($deposito->status === 'rejected') { $badgeClass = 'badge-perdeu'; $statusLabel = 'Recusado'; }
                        elseif ($deposito->status === 'pending') { $badgeClass = 'badge-aberto'; $statusLabel = 'Pendente'; }
                    @endphp
                    <span class="badge-status badge-{{ $badgeClass == 'badge-ganhou' ? 'ganhou' : ($badgeClass == 'badge-perdeu' ? 'perdeu' : 'aberto') }}">{{ $statusLabel }}</span>
                </div>
            </div>
        </div>
    @endforeach

    <div class="mt-3">
        {{ $depositos->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
