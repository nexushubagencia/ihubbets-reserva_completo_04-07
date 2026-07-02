@extends('cliente.layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div class="mb-3">
    <h5 style="font-weight:700; margin:0;">Meu Perfil</h5>
    <small style="color:var(--text-muted);">Gerencie seus dados pessoais</small>
</div>

<div class="card-client" style="text-align:center; padding:24px;">
    <div style="width:64px; height:64px; border-radius:50%; background:var(--primary); color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; margin-bottom:8px;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <h6 style="font-weight:700; margin:0;">{{ $user->name }}</h6>
    <small style="color:var(--text-muted);">{{ $user->email }}</small>
</div>

<form method="POST" action="{{ route('cliente.perfil.update') }}">
    @csrf

    <div class="card-client">
        <h6 style="font-weight:700; margin-bottom:12px;">Dados Pessoais</h6>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Nome Completo</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required
                   style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">E-mail</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required
                   style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Telefone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"
                   placeholder="(00) 00000-0000" style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">CPF</label>
            <input type="text" name="cpf" class="form-control" value="{{ old('cpf', $user->cpf) }}"
                   placeholder="000.000.000-00" style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>
    </div>

    <div class="card-client">
        <h6 style="font-weight:700; margin-bottom:12px;">Chave PIX</h6>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Tipo da Chave</label>
            <select name="pix_key_type" class="form-select" style="border-radius:8px; font-size:13px; padding:10px 12px;">
                <option value="">Selecione...</option>
                <option value="cpf" {{ old('pix_key_type', $user->pix_key_type) == 'cpf' ? 'selected' : '' }}>CPF</option>
                <option value="cnpj" {{ old('pix_key_type', $user->pix_key_type) == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                <option value="email" {{ old('pix_key_type', $user->pix_key_type) == 'email' ? 'selected' : '' }}>E-mail</option>
                <option value="phone" {{ old('pix_key_type', $user->pix_key_type) == 'phone' ? 'selected' : '' }}>Telefone</option>
                <option value="random" {{ old('pix_key_type', $user->pix_key_type) == 'random' ? 'selected' : '' }}>Chave Aleatória</option>
            </select>
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Chave PIX</label>
            <input type="text" name="pix_key" class="form-control" value="{{ old('pix_key', $user->pix_key) }}"
                   placeholder="Sua chave PIX" style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>
    </div>

    <div class="card-client">
        <h6 style="font-weight:700; margin-bottom:12px;">Alterar Senha</h6>
        <small style="color:var(--text-muted); font-size:12px; display:block; margin-bottom:12px;">Deixe em branco para manter a senha atual.</small>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Senha Atual</label>
            <input type="password" name="current_password" class="form-control"
                   style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>

        <div class="mb-3">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Nova Senha</label>
            <input type="password" name="new_password" class="form-control" minlength="6"
                   style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>

        <div class="mb-0">
            <label style="font-size:13px; font-weight:600; margin-bottom:4px; display:block;">Confirmar Nova Senha</label>
            <input type="password" name="new_password_confirmation" class="form-control" minlength="6"
                   style="border-radius:8px; font-size:13px; padding:10px 12px;">
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3" style="padding:12px;">
        <i class="fas fa-save me-1"></i> Salvar Alterações
    </button>
</form>
@endsection
