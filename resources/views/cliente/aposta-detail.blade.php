@extends('cliente.layouts.app')

@section('title', 'Detalhe da Aposta')

@section('content')
<div class="mb-3">
    <a href="{{ route('cliente.apostas') }}" style="color:var(--primary); text-decoration:none; font-size:13px;">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card-client">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 style="font-weight:700; margin:0;">{{ $aposta->codigo_bilhete ?? 'Bilhete #' . $aposta->id }}</h5>
            <small style="color:var(--text-muted);">{{ $aposta->created_at->format('d/m/Y \à\s H:i') }}</small>
        </div>
        @php
            $statusClass = 'aberto';
            if (in_array($aposta->status, ['Venceu', 'Ganhou', 'won'])) $statusClass = 'ganhou';
            elseif (in_array($aposta->status, ['Perdeu', 'lost'])) $statusClass = 'perdeu';
            elseif (in_array($aposta->status, ['Cancelado', 'cancelled'])) $statusClass = 'cancelado';
        @endphp
        <span class="badge-status badge-{{ $statusClass }}" style="font-size:13px; padding:6px 14px;">{{ $aposta->status }}</span>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
            <small style="color:var(--text-muted); font-size:11px; text-transform:uppercase;">Valor Apostado</small>
            <div style="font-weight:700; font-size:18px;">R$ {{ number_format($aposta->valor_apostado, 2, ',', '.') }}</div>
        </div>
        <div>
            <small style="color:var(--text-muted); font-size:11px; text-transform:uppercase;">Retorno Possível</small>
            <div style="font-weight:700; font-size:18px; color:var(--success);">R$ {{ number_format($aposta->retorno_possivel, 2, ',', '.') }}</div>
        </div>
    </div>

    @if($aposta->cotacao)
    <div class="mt-2">
        <small style="color:var(--text-muted); font-size:11px; text-transform:uppercase;">Cotação Total</small>
        <div style="font-weight:700;">{{ number_format($aposta->cotacao, 2) }}</div>
    </div>
    @endif
</div>

<div class="card-client">
    <div class="card-title mb-3">Palpites ({{ $aposta->palpites->count() }})</div>

    @if($aposta->palpites->isEmpty())
        <div class="empty-state" style="padding:20px;">
            <p style="margin:0;">Nenhum palpite registrado</p>
        </div>
    @else
        @foreach($aposta->palpites as $palpite)
            <div style="padding:12px 0; {{ !$loop->last ? 'border-bottom:1px solid #f1f3f4;' : '' }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="font-weight:600; font-size:13px;">
                            {{ $palpite->home_team ?? 'Time Casa' }} vs {{ $palpite->away_team ?? 'Time Fora' }}
                        </div>
                        <small style="color:var(--text-muted);">
                            {{ $palpite->market_name ?? 'Mercado' }} · {{ $palpite->selection_label ?? 'Seleção' }}
                        </small>
                    </div>
                    <div class="text-end">
                        <div style="font-weight:700; color:var(--primary);">x{{ number_format($palpite->selection_odd, 2) }}</div>
                        @if($palpite->status)
                            @php
                                $pStatus = 'aberto';
                                if (in_array($palpite->status, ['won', 'ganhou'])) $pStatus = 'ganhou';
                                elseif (in_array($palpite->status, ['lost', 'perdeu'])) $pStatus = 'perdeu';
                            @endphp
                            <span class="badge-status badge-{{ $pStatus }}" style="font-size:10px;">{{ $palpite->status }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

@if($canCancel)
    <form method="POST" action="{{ route('cliente.aposta.cancel', $aposta->id) }}" onsubmit="return confirm('Tem certeza que deseja cancelar esta aposta?');">
        @csrf
        <button type="submit" class="btn btn-danger w-100" style="border-radius:8px; font-weight:600;">
            <i class="fas fa-times-circle me-1"></i> Cancelar Aposta
        </button>
    </form>
@else
    @if(in_array($aposta->status, ['Aberto', 'open', 'pending']))
        <div class="text-center mt-2">
            <small style="color:var(--text-muted); font-size:12px;">
                <i class="fas fa-info-circle me-1"></i>
                Cancelamento disponível apenas nos primeiros 5 minutos.
            </small>
        </div>
    @endif
@endif
@endsection
