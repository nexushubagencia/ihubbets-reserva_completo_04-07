@extends('gerente.layouts.app')
@section('title', 'Meu Perfil')
@section('content_header')
    <h1><i class="fas fa-user-cog text-primary mr-2"></i> Meu Perfil</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="font-weight-bold mb-0"><i class="fas fa-id-card mr-2"></i> Editar Perfil</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('gerente.perfil.update') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Nome Completo</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Contato / Telefone</label>
                                <input type="text" name="contato" class="form-control" value="{{ old('contato', $user->contato) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Endereço</label>
                                <input type="text" name="endereco" class="form-control" value="{{ old('endereco', $user->endereco) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Chave PIX</label>
                                <input type="text" name="pix_key" class="form-control" value="{{ old('pix_key', $user->pix_key) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Nova Senha (deixe vazio para manter)</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Confirmar Senha</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm" style="font-size:0.82rem;">
                                <tr><td class="text-muted font-weight-bold">Login</td><td>{{ $user->username }}</td></tr>
                                <tr><td class="text-muted font-weight-bold">Função</td><td><span class="badge badge-primary">Gerente</span></td></tr>
                                <tr><td class="text-muted font-weight-bold">Saldo</td><td class="font-weight-bold text-success">R$ {{ number_format($user->balance ?? 0, 2, ',', '.') }}</td></tr>
                                <tr><td class="text-muted font-weight-bold">Comissão (%)</td><td>{{ $user->comissao_gerente ?? 0 }}%</td></tr>
                                <tr><td class="text-muted font-weight-bold">Membro desde</td><td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6 text-right d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-5">
                                <i class="fas fa-save mr-2"></i> SALVAR ALTERAÇÕES
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
