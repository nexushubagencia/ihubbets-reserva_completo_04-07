@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-file-invoice-dollar text-primary"></i> Relatório de Gerentes</h1>
@stop
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white border-0">
        <h3 class="card-title font-weight-bold"><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
    </div>
    <div class="card-body bg-light">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label class="text-muted text-sm font-weight-bold mb-1">Gerente:</label>
                <select class="form-control font-weight-bold shadow-sm" id="rg-gerente">
                    <option value="Todos">Todos os Gerentes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="text-muted text-sm font-weight-bold mb-1">Data Inicial:</label>
                <input type="date" class="form-control shadow-sm font-weight-bold text-secondary" id="rg-date1">
            </div>
            <div class="col-md-3">
                <label class="text-muted text-sm font-weight-bold mb-1">Data Final:</label>
                <input type="date" class="form-control shadow-sm font-weight-bold text-secondary" id="rg-date2">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-block font-weight-bold shadow-sm" onclick="searchRelGer()">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-primary text-white">
                    <tr style="font-size: 13px; text-transform: uppercase;">
                        <th>Colaborador</th>
                        <th class="text-center">Qtd. Apostas</th>
                        <th class="text-center">Entradas</th>
                        <th class="text-center">Saídas (Prêmios)</th>
                        <th class="text-center">Comissão Rua</th>
                        <th class="text-center">Comissão Online</th>
                        <th class="text-center">Total Comissões</th>
                        <th class="text-center">Líquido da Casa</th>
                    </tr>
                </thead>
                <tbody id="rg-tbody">
                    <!-- Data here -->
                </tbody>
            </table>
            <div id="rg-loading" class="text-center p-5" style="display:none;">
                <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                <h5 class="text-muted">Gerando relatório...</h5>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function fm(n){ return "R$ " + parseFloat(n||0).toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+\,)/g, "$1."); }

$(document).ready(function(){
    var d = new Date();
    var h = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    $('#rg-date1').val(h); 
    $('#rg-date2').val(h);
    
    $.get('/admin/list-gerentes', function(d) {
        if(d) d.forEach(function(g){
            $('#rg-gerente').append('<option value="'+g.id+'">'+g.name+'</option>');
        });
    });

    searchRelGer();
});

function searchRelGer(){
    $('#rg-tbody').empty();
    $('#rg-loading').show();
    
    $.post('/admin/relatorio-gerente-list', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        gerente: $('#rg-gerente').val(),
        date1: $('#rg-date1').val(),
        date2: $('#rg-date2').val()
    }, function(d){
        renderRG(d);
    }).fail(function(){
        $('#rg-loading').hide();
        toastr.error('Erro ao buscar dados do relatório.');
    });
}

function renderRG(data){
    var tb = $('#rg-tbody');
    $('#rg-loading').hide();
    
    if(!data || data.length === 0){
        tb.append('<tr><td colspan="8" class="text-center text-muted p-4"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i> Nenhum registro encontrado para o período.</td></tr>');
        return;
    }
    
    data.forEach(function(r){
        var cls = parseFloat(r.total) >= 0 ? 'text-success' : 'text-danger';
        var badgeCls = parseFloat(r.total) >= 0 ? 'badge-success' : 'badge-danger';
        
        tb.append('<tr>' +
            '<td class="font-weight-bold text-dark"><i class="fas fa-user-circle text-muted mr-2"></i> ' + (r.colaborador || r.name || '') + '</td>' +
            '<td class="text-center align-middle"><span class="badge badge-secondary px-3 py-2">' + (r.quantidade || 0) + '</span></td>' +
            '<td class="text-center align-middle font-weight-bold text-success">' + fm(r.entradas) + '</td>' +
            '<td class="text-center align-middle font-weight-bold text-danger">' + fm(r.saidas) + '</td>' +
            '<td class="text-center align-middle">' + fm(r.comissao_rua) + '</td>' +
            '<td class="text-center align-middle">' + fm(r.comissao_online) + '</td>' +
            '<td class="text-center align-middle font-weight-bold text-info">' + fm(r.comissoes) + '</td>' +
            '<td class="text-center align-middle font-weight-bold ' + cls + '"><span class="badge ' + badgeCls + ' px-3 py-2" style="font-size: 14px;">' + fm(r.total) + '</span></td>' +
        '</tr>');
    });
}
</script>
@stop