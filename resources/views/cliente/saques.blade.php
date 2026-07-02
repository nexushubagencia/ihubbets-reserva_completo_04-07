@extends('cliente.layouts.app')

@section('title', 'Saques')

@section('content')
<div class="mb-3">
    <a href="{{ route('cliente.financeiro') }}" style="color:var(--primary); text-decoration:none; font-size:13px;">
        <i class="fas fa-arrow-left me-1"></i> Financeiro
    </a>
</div>

<div class="card-client">
    <div class="card-title">Saldo Disponível</div>
    <div class="card-value text-primary">R$ {{ number_format($saldoDisponivel, 2, ',', '.') }}</div>
</div>

<div class="card-client">
    <h6 style="font-weight:700; margin-bottom:12px;">Solicitar Saque</h6>
    <form method="POST" action="{{ route('cliente.saque.request') }}">
        @csrf
        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Valor (mínimo R$ 10,00)</label>
            <input type="number" name="valor" class="form-control" min="10" step="0.01" required
                   placeholder="0,00" style="border-radius:8px; font-size:15px; padding:10px 12px;"
                   value="{{ old('valor') }}">
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Tipo da Chave PIX</label>
            <select name="pix_key_type" class="form-select" required style="border-radius:8px; font-size:13px; padding:10px 12px;">
                <option value="">Selecione...</option>
                <option value="cpf" {{ old('pix_key_type') == 'cpf' ? 'selected' : '' }}>CPF</option>
                <option value="cnpj" {{ old('pix_key_type') == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                <option value="email" {{ old('pix_key_type') == 'email' ? 'selected' : '' }}>E-mail</option>
                <option value="phone" {{ old('pix_key_type') == 'phone' ? 'selected' : '' }}>Telefone</option>
                <option value="random" {{ old('pix_key_type') == 'random' ? 'selected' : '' }}>Chave Aleatória</option>
            </select>
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Chave PIX</label>
            <input type="text" name="pix_key" class="form-control" required
                   placeholder="Sua chave PIX" style="border-radius:8px; font-size:13px; padding:10px 12px;"
                   value="{{ old('pix_key') }}">
        </div>

        <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Confirmar solicitação de saque?');">
            <i class="fas fa-paper-plane me-1"></i> Solicitar Saque
        </button>
    </form>
</div>

<div class="card-client">
    <div class="card-title mb-3">Histórico de Saques</div>

    @if($saques->isEmpty())
        <div class="empty-state" style="padding:20px;">
            <p style="margin:0;">Nenhum saque solicitado</p>
        </div>
    @else
        @foreach($saques as $saque)
            <div style="padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid #f1f3f4;' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-weight:600; font-size:13px;">R$ {{ number_format($saque->amount, 2, ',', '.') }}</div>
                        <small style="color:var(--text-muted);">{{ $saque->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="text-end">
                        @php
                            $sClass = 'aberto';
                            if ($saque->status === 'approved') $sClass = 'ganhou';
                            elseif ($saque->status === 'rejected') $sClass = 'perdeu';
                        @endphp
                        <span class="badge-status badge-{{ $sClass }}">{{ ucfirst($saque->status) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
