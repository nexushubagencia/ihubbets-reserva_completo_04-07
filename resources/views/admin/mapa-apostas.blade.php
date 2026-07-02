@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-map"></i> Mapa de Apostas</h1>
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3"><label>De:</label><input type="date" class="form-control" id="mapa-date1"></div>
            <div class="col-md-3"><label>Até:</label><input type="date" class="form-control" id="mapa-date2"></div>
            <div class="col-md-2"><label>Pesquisar</label><button class="btn btn-success form-control" onclick="searchMapa()"><i class="fas fa-search"></i></button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white" style="font-size:12px;text-align:center;">
                    <tr><th>JOGO</th><th>LIGA</th><th>MERCADO</th><th>QTD APOSTAS</th><th>TOTAL APOSTADO</th><th>RISCO</th></tr>
                </thead>
                <tbody id="mapa-tbody" style="text-align:center;font-size:13px;"></tbody>
            </table>
            <div id="mapa-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
function fm(n){return "R$ "+parseFloat(n||0).toFixed(2).replace('.',',').replace(/(\d)(?=(\d{3})+\,)/g,"$1.");}
$(document).ready(function(){var h=(function(){var d=new Date();return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');})();$('#mapa-date1').val(h);$('#mapa-date2').val(h);searchMapa();});
function searchMapa(){$('#mapa-loading').show();$.post('/admin/mapa-apostas',{_token:$('meta[name="csrf-token"]').attr('content'),date1:$('#mapa-date1').val(),date2:$('#mapa-date2').val()},function(data){var tb=$('#mapa-tbody');tb.empty();$('#mapa-loading').hide();if(!data||!data.length){tb.append('<tr><td colspan="6">Nenhum dado</td></tr>');return;}data.forEach(function(m){tb.append('<tr><td>'+(m.jogo||'-')+'</td><td>'+(m.liga||'-')+'</td><td>'+(m.mercado||'-')+'</td><td>'+(m.qtd||0)+'</td><td>'+fm(m.total_apostado)+'</td><td>'+fm(m.risco)+'</td></tr>');});}).fail(function(){$('#mapa-loading').hide();$('#mapa-tbody').append('<tr><td colspan="6">Erro ao carregar</td></tr>');});}
</script>
@stop