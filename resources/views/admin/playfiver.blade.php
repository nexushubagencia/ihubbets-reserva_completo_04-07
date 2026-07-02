@extends('adminlte::page')

@section('title', 'Playfiver Casino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-dice text-danger mr-2"></i> Playfiver Casino
        <small class="text-muted text-sm font-weight-normal">Gerenciamento de Jogos de Cassino</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-danger shadow">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title"><i class="fas fa-cog mr-2"></i> Configurações</h3>
                </div>
                <div class="card-body">
                    <form id="configForm">
                        <div class="form-group">
                            <label>API Token</label>
                            <input type="text" class="form-control" id="apiToken" value="{{ env('API_PLAYFIVER_TOKEN', '') }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Secret Key</label>
                            <input type="password" class="form-control" id="secretKey" value="{{ env('API_PLAYFIVER_SECRET', '') }}" readonly>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            Configure as credenciais no arquivo <code>.env</code>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary shadow">
                <div class="card-header bg-white">
                    <h3 class="card-title"><i class="fas fa-gamepad mr-2"></i> Jogos Sincronizados</h3>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-primary" onclick="syncGames()">
                            <i class="fas fa-sync mr-1"></i> Sincronizar Jogos
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped" id="gamesTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Provedor</th>
                                <th>Código</th>
                                <th>Status</th>
                                <th>Popular</th>
                            </tr>
                        </thead>
                        <tbody id="gamesBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Carregando jogos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
function loadGames() {
    fetch('/api/playfiver/games', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('gamesBody');
        const games = data.games || [];
        if (games.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Nenhum jogo encontrado</td></tr>';
            return;
        }
        tbody.innerHTML = games.map(g => `
            <tr>
                <td>${g.id}</td>
                <td>${g.name || '-'}</td>
                <td><span class="badge badge-info">${g.provider || '-'}</span></td>
                <td><code>${g.game_code || '-'}</code></td>
                <td>${g.status ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>'}</td>
                <td>${g.is_popular ? '<i class="fas fa-star text-warning"></i>' : '-'}</td>
            </tr>
        `).join('');
    })
    .catch(err => {
        document.getElementById('gamesBody').innerHTML = 
            '<tr><td colspan="6" class="text-center text-danger">Erro ao carregar jogos</td></tr>';
    });
}

function syncGames() {
    alert('Sincronização via API Playfiver. Execute: php artisan playfiver:sync');
}

document.addEventListener('DOMContentLoaded', loadGames);
</script>
@endpush
