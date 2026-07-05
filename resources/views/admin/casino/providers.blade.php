@extends('adminlte::page')

@section('title', 'Provedores do Cassino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-cubes text-primary mr-2"></i> Provedores do Cassino
        <small class="text-muted text-sm font-weight-normal">Distribuidores Casino V3</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-list mr-2 text-primary"></i> Provedores
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless table-striped align-middle">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nome</th>
                                    <th>Distribuição</th>
                                    <th>RTP</th>
                                    <th>Status</th>
                                    <th>Jogos</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary" style="font-size: 0.9rem;">
                                @forelse($providers as $provider)
                                <tr>
                                    <td>{{ $provider->id }}</td>
                                    <td><code>{{ $provider->code }}</code></td>
                                    <td>{{ $provider->name }}</td>
                                    <td><span class="badge badge-info">{{ $provider->distribution ?? 'N/A' }}</span></td>
                                    <td>{{ $provider->rtp }}</td>
                                    <td>
                                        @if($provider->status)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td>{{ $provider->games_count ?? $provider->games()->count() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">Nenhum provedor cadastrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $providers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
