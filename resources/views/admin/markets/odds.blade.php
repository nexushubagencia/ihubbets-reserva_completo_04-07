@extends('adminlte::page')

@section('title', 'Gerenciar Odds & Mercados')

@section('content_header')
    <h1><i class="fas fa-chart-line"></i> Gerenciar Odds & Mercados <small>Ajustes em massa</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Início</a></li>
        <li class="active">Odds & Mercados</li>
    </ol>
@stop

@section('content')
<div class="row">
    <!-- Ajuste por Liga -->
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fas fa-percentage"></i> Ajuste em Massa por Liga</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>Selecione a Liga:</label>
                    <select class="form-control" id="league-select">
                        <option value="">Carregando ligas...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ajuste (%):</label>
                    <input type="number" step="0.01" class="form-control" id="adjustment-percent" placeholder="Ex: 5 para +5%, -10 para -10%">
                    <small class="text-muted">Valores positivos aumentam as odds, negativos diminuem.</small>
                </div>
            </div>
            <div class="box-footer">
                <button class="btn btn-primary" onclick="applyAdjustment()"><i class="fas fa-save"></i> Aplicar Ajuste</button>
            </div>
        </div>
    </div>

    <!-- Toggle Mercado Global -->
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fas fa-toggle-on"></i> Bloquear/Desbloquear Mercado</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>Mercado:</label>
                    <select class="form-control" id="market-select">
                        <option value="1x2">1X2 (Vencedor)</option>
                        <option value="dupla_chance">Dupla Chance</option>
                        <option value="over_under">Over/Under</option>
                        <option value="ambas_marcam">Ambas Marcam</option>
                        <option value="handicap">Handicap</option>
                        <option value="resultado_exato">Resultado Exato</option>
                        <option value="intervalo_final">Intervalo/Final</option>
                        <option value="gols">Gols</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status:</label>
                    <select class="form-control" id="market-status">
                        <option value="active">Ativo</option>
                        <option value="blocked">Bloqueado</option>
                    </select>
                </div>
            </div>
            <div class="box-footer">
                <button class="btn btn-warning" onclick="toggleMarket()"><i class="fas fa-toggle-on"></i> Aplicar</button>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Odds Atuais -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fas fa-list"></i> Odds Atuais por Mercado</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <label>Filtrar por Mercado:</label>
            <select class="form-control" id="filter-market" onchange="loadCurrentOdds()">
                <option value="">Todos</option>
                <option value="1x2">1X2</option>
                <option value="dupla_chance">Dupla Chance</option>
                <option value="over_under">Over/Under</option>
            </select>
        </div>
        <div id="odds-table-container">
            <p class="text-muted text-center">Selecione um mercado para ver as odds.</p>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    loadLeagues();
});

function loadLeagues() {
    $.get('/admin/show-ligas-principais', function(data) {
        var sel = $('#league-select');
        sel.empty().append('<option value="">Selecione uma liga...</option>');
        if (data) {
            data.forEach(function(l) {
                sel.append('<option value="'+l.id+'">'+l.league+'</option>');
            });
        }
    });
}

function applyAdjustment() {
    var leagueId = $('#league-select').val();
    var percent = $('#adjustment-percent').val();
    if (!leagueId || !percent) {
        toastr.error('Selecione uma liga e informe a porcentagem.');
        return;
    }
    $.ajax({
        url: '/admin/markets/adjust-league',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', league_id: leagueId, percentage: percent },
        success: function(r) { toastr.success(r.message); },
        error: function() { toastr.error('Erro ao aplicar ajuste.'); }
    });
}

function toggleMarket() {
    var market = $('#market-select').val();
    var status = $('#market-status').val();
    $.ajax({
        url: '/admin/markets/toggle-market',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', market_name: market, status: status },
        success: function(r) { toastr.success(r.message); },
        error: function() { toastr.error('Erro ao atualizar mercado.'); }
    });
}

function loadCurrentOdds() {
    var market = $('#filter-market').val();
    if (!market) {
        $('#odds-table-container').html('<p class="text-muted text-center">Selecione um mercado.</p>');
        return;
    }
    $.post('/admin/odds-list', { _token: '{{ csrf_token() }}', mercado_name: market }, function(data) {
        if (!data || data.length === 0) {
            $('#odds-table-container').html('<p class="text-warning text-center">Nenhuma odd encontrada.</p>');
            return;
        }
        var html = '<table class="table table-sm table-striped"><thead><tr><th>ID</th><th>Evento</th><th>Label</th><th>Valor</th><th>Status</th></tr></thead><tbody>';
        data.forEach(function(o) {
            var st = o.status == 1 ? '<span class="label label-success">Ativo</span>' : '<span class="label label-danger">Bloqueado</span>';
            html += '<tr><td>'+o.id+'</td><td>'+o.event_id+'</td><td>'+o.label+'</td><td>'+o.value+'</td><td>'+st+'</td></tr>';
        });
        html += '</tbody></table>';
        $('#odds-table-container').html(html);
    });
}
</script>
@stop
