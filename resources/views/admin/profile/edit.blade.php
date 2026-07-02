@extends('adminlte::page')

@section('title', 'Editar Perfil')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Perfil</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-navy card-outline shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-user-edit mr-1"></i> Seus Dados</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name">Nome Completo</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ auth()->user()->name }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="username">Usuário (Username)</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->username }}" disabled>
                        <small class="text-muted">O nome de usuário não pode ser alterado por segurança.</small>
                    </div>

                    <hr>

                    <h5 class="text-navy font-weight-bold mb-3"><i class="fas fa-lock mr-1"></i> Alterar Senha</h5>
                    
                    <div class="form-group">
                        <label for="password">Nova Senha</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Deixe em branco para não alterar">
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Nova Senha</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repita a nova senha">
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle mr-1"></i> Se você preencher o campo de senha, sua senha atual será substituída pela nova após clicar em Salvar.
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-navy btn-block font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info">
                <h3 class="card-title font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Segurança da Conta</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    O IHUB V2 utiliza criptografia de ponta a ponta (Bcrypt) para proteger suas credenciais. 
                    Ninguém, nem mesmo os administradores do sistema, conseguem visualizar sua senha atual.
                </p>
                
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Cargo (Role)</b> <a class="float-right badge badge-primary">{{ strtoupper(auth()->user()->role) }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Última Atualização</b> <a class="float-right text-muted">{{ auth()->user()->updated_at->format('d/m/Y H:i') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>ID do Sistema</b> <a class="float-right text-muted">#{{ str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) }}</a>
                    </li>
                </ul>

                <div class="callout callout-warning">
                    <h5>Dica de Segurança</h5>
                    <p class="small">Use senhas que misturem letras maiúsculas, minúsculas, números e símbolos (@, #, $).</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .btn-navy { background-color: #001f3f; color: #fff; }
    .btn-navy:hover { background-color: #001a35; color: #fff; }
    .text-navy { color: #001f3f; }
</style>
@stop
