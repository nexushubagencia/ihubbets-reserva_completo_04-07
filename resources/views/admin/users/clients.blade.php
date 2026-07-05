@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1><i class="fas fa-users me-2 text-info"></i> Clientes Cadastrados</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-search me-1"></i> Buscar Cliente</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Username</th>
                    <th>Saldo</th>
                    <th>Status</th>
                    <th>Cadastro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->username }}</td>
                    <td>R$ {{ number_format($u->balance ?? 0, 2, ',', '.') }}</td>
                    <td>
                        @if(($u->status ?? 1) == 1)
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-danger">Inativo</span>
                        @endif
                    </td>
                    <td>{{ $u->created_at ? $u->created_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Nenhum cliente encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
