@extends('cambista.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Meu Perfil</h5>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-user-edit me-2"></i>Editar Dados</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('cambista.perfil.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefone / Contato</label>
                        <input type="text" name="contato" class="form-control" value="{{ old('contato', $user->contato) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email ?? $user->username }}" disabled>
                        <small class="text-muted">Email não pode ser alterado.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nova Senha</label>
                        <input type="password" name="password" class="form-control" placeholder="Deixe vazio para manter">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repita a nova senha">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar Alterações</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Meus Dados</h6></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted" style="width:40%">Nome</td><td class="fw-bold">{{ $user->name }}</td></tr>
                    <tr><td class="text-muted">Login</td><td>{{ $user->username }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $user->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Telefone</td><td>{{ $user->contato ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Função</td><td><span class="badge bg-primary">Cambista</span></td></tr>
                    <tr><td class="text-muted">Status</td><td><span class="badge {{ $user->status ? 'bg-success' : 'bg-danger' }}">{{ $user->status ? 'Ativo' : 'Bloqueado' }}</span></td></tr>
                    <tr><td class="text-muted">Cadastro</td><td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-wallet me-2"></i>Meus Saldos</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Saldo Simples</span>
                    <strong class="text-primary">R$ {{ number_format($user->saldo_simples ?? $user->balance ?? 0, 2, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Saldo Casadinha</span>
                    <strong style="color:#8b5cf6;">R$ {{ number_format($user->saldo_casadinha ?? $user->balance_bonus ?? 0, 2, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Saldo Loto</span>
                    <strong style="color:#f59e0b;">R$ {{ number_format($user->saldo_loto ?? 0, 2, ',', '.') }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total</span>
                    <strong class="text-success fs-5">
                        R$ {{ number_format(
                            ($user->saldo_simples ?? $user->balance ?? 0) +
                            ($user->saldo_casadinha ?? $user->balance_bonus ?? 0) +
                            ($user->saldo_loto ?? 0),
                        2, ',', '.') }}
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
