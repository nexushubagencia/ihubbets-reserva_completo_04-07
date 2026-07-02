@extends('cliente.layouts.app')

@section('title', 'Minhas Apostas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 style="font-weight:700; margin:0;">Minhas Apostas</h5>
</div>

<form method="GET" action="{{ route('cliente.apostas') }}" class="mb-3">
    <div class="input-group" style="border-radius:8px; overflow:hidden; border:1px solid var(--border);">
        <input type="text" name="busca" class="form-control" placeholder="Buscar por código..." value="{{ request('busca') }}" style="border:none; font-size:13px;">
        <button class="btn btn-outline-primary" type="submit" style="border:none;"><i class="fas fa-search"></i></button>
    </div>
</form>

<ul class="nav nav-tabs mb-3" style="border-bottom:2px solid var(--border);">
    <li class="nav-item">
        <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('cliente.apostas') }}">Todas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') == 'Aberto' ? 'active' : '' }}" href="{{ route('cliente.apostas', ['status' => 'Aberto']) }}">Abertas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') == 'Venceu' ? 'active' : '' }}" href="{{ route('cliente.apostas', ['status' => 'Venceu']) }}">Ganhas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('status') == 'Perdeu' ? 'active' : '' }}" href="{{ route('cliente.apostas', ['status' => 'Perdeu']) }}">Perdidas</a>
    </li>
</ul>

@if($apostas->isEmpty())
    <div class="empty-state">
        <i class="fas fa-ticket-alt"></i>
        <p>Nenhuma aposta encontrada</p>
    </div>
@else
    @foreach($apostas as $aposta)
        <a href="{{ route('cliente.aposta-detail', $aposta->id) }}" class="card-client" style="display:block; text-decoration:none; color:inherit; cursor:pointer;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-weight:600; font-size:14px;">
                        {{ $aposta->codigo_bilhete ?? 'Bilhete #' . $aposta->id }}
                    </div>
                    <small style="color:var(--text-muted);">
                        {{ $aposta->created_at->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="text-end">
                    @php
                        $statusClass = 'aberto';
                        if (in_array($aposta->status, ['Venceu', 'Ganhou', 'won'])) $statusClass = 'ganhou';
                        elseif (in_array($aposta->status, ['Perdeu', 'lost'])) $statusClass = 'perdeu';
                        elseif (in_array($aposta->status, ['Cancelado', 'cancelled'])) $statusClass = 'cancelado';
                    @endphp
                    <span class="badge-status badge-{{ $statusClass }}">{{ $aposta->status }}</span>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2" style="padding-top:8px; border-top:1px solid #f1f3f4;">
                <div>
                    <small style="color:var(--text-muted);">Palpites: <strong>{{ $aposta->total_palpites ?? 0 }}</strong></small>
                </div>
                <div>
                    <span style="font-weight:700; font-size:15px;">R$ {{ number_format($aposta->valor_apostado, 2, ',', '.') }}</span>
                </div>
            </div>
        </a>
    @endforeach

    <div class="mt-3">
        {{ $apostas->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
