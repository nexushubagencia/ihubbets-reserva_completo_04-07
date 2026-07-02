@extends('adminlte::page')

@section('title', 'Apostas Cassino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-dice text-danger mr-2"></i> Apostas Cassino
        <small class="text-muted text-sm font-weight-normal">Histórico de Apostas Playfiver</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-danger shadow">
                <div class="card-header bg-white">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i> Apostas Registradas</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped" id="betsTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Jogo</th>
                                <th>Aposta</th>
                                <th>Ganho</th>
                                <th>Lucro</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody id="betsBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Carregando apostas...
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
document.addEventListener('DOMContentLoaded', function() {
    // Load casino bets via admin API
    fetch('/api/admin/cassino/apostas', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('betsBody');
        const bets = data.result || [];
        if (bets.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Nenhuma aposta encontrada</td></tr>';
            return;
        }
        tbody.innerHTML = bets.map(b => {
            const lucro = (b.win || 0) - (b.bet || 0);
            return `
            <tr>
                <td>${b.id}</td>
                <td>${b.user_login}</td>
                <td>${b.game_id}</td>
                <td>R$ ${(b.bet || 0).toFixed(2)}</td>
                <td>R$ ${(b.win || 0).toFixed(2)}</td>
                <td class="${lucro >= 0 ? 'text-success' : 'text-danger'}">R$ ${lucro.toFixed(2)}</td>
                <td>${b.created_at || '-'}</td>
            </tr>`;
        }).join('');
    })
    .catch(() => {
        document.getElementById('betsBody').innerHTML = 
            '<tr><td colspan="7" class="text-center text-muted py-4">Nenhuma aposta encontrada</td></tr>';
    });
});
</script>
@endpush
