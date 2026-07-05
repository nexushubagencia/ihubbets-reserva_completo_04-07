@extends('adminlte::page')

@section('title', 'Categorias do Cassino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-tags text-primary mr-2"></i> Categorias do Cassino
        <small class="text-muted text-sm font-weight-normal">Grupos de jogos</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-list mr-2 text-primary"></i> Categorias
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless table-striped align-middle">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Slug</th>
                                    <th>Jogos</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary" style="font-size: 0.9rem;">
                                @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td>{{ $category->games()->count() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">Nenhuma categoria cadastrada.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
