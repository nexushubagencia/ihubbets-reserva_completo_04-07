@extends('adminlte::page')

@section('title', 'Jogos do Cassino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-gamepad text-primary mr-2"></i> Jogos do Cassino
        <small class="text-muted text-sm font-weight-normal">Catálogo Casino V3</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-list mr-2 text-primary"></i> Todos os Jogos
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $games->total() }} jogos</span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless table-striped align-middle">
                            <thead class="bg-light text-muted text-uppercase" style="font-size: 0.75rem;">
                                <tr>
                                    <th>ID</th>
                                    <th>Capa</th>
                                    <th>Nome</th>
                                    <th>Provedor</th>
                                    <th>Distribuição</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Destaque</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary" style="font-size: 0.9rem;">
                                @forelse($games as $game)
                                <tr>
                                    <td>{{ $game->id }}</td>
                                    <td>
                                        <img src="{{ $game->cover ?? '/img/casino-placeholder.svg' }}" alt="" style="height: 40px; border-radius: 6px;" onerror="this.src='/img/casino-placeholder.svg'">
                                    </td>
                                    <td>{{ $game->game_name }}</td>
                                    <td>{{ $game->provider?->name ?? '-' }}</td>
                                    <td><span class="badge badge-info">{{ $game->distribution ?? 'N/A' }}</span></td>
                                    <td>{{ $game->game_type ?? '-' }}</td>
                                    <td>
                                        @if($game->status)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($game->is_featured)
                                            <span class="badge badge-warning">Sim</span>
                                        @else
                                            <span class="badge badge-light">Não</span>
                                        @endif
                                    </td>
                                    <td>{{ $game->views }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">Nenhum jogo cadastrado ainda.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $games->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
