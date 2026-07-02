@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-exclamation-triangle"></i> Gerenciamento de Risco</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Filtrar por Risco:</label>
                <select class="form-control" id="risco-opt" onchange="loadRisco()">
                    <option value="Possível Retorno">Possível Retorno</option>
                    <option value="Quantida de Bilhetes">Quantidade de Bilhetes</option>
                    <option value="Valor Apostado">Valor Apostado</option>
                    <option value="Quantidade de Apostas em Aberto">Quantidade de Apostas em Aberto</option>
                    <option value="Quntidade de Apostas no Bilhete">Quantidade de Apostas no Bilhete</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#00466A;color:#fff;font-size:13px;text-align:center;">
                    <tr><th>CUPOM</th><th>VALOR APOSTADO</th><th>POSSÍVEL RETORNO</th><th>APOSTAS EM ABERTO</th><th>CONFERIR</th></tr>
                </thead>
                <tbody id="risco-tbody" style="text-align:center;font-size:13px;"></tbody>
            </table>
            <div id="risco-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>
<!-- Modal details similar to bilhetes can be added here or reuse the same logic if needed -->
@stop
@section('js')
<script>
function fm(n){return "R$ "+parseFloat(n||0).toFixed(2).replace('.',',').replace(/(\d)(?=(\d{3})+\,)/g,"$1.");}
$(document).ready(function(){loadRisco();});
function loadRisco(){
    $('#risco-loading').show();
    $.post('/admin/list-bilhete-risco',{_token:$('meta[name="csrf-token"]').attr('content'),opcao:$('#risco-opt').val()},function(data){
        var tb=$('#risco-tbody');tb.empty();$('#risco-loading').hide();
        if(!data||!data.length){tb.append('<tr><td colspan="5">Sem dados de risco</td></tr>');return;}
        data.forEach(function(b){
            tb.append('<tr><td class="text-danger"><b>'+b.cupom+'</b></td><td><b>'+fm(b.valor_apostado)+'</b></td><td><b>'+fm(b.retorno_possivel)+'</b></td><td><b>'+(b.andamento_palpites||0)+'/'+(b.total_palpites||0)+'</b></td><td><a href="/admin/bilhetes?cupom='+b.cupom+'" class="btn btn-success btn-sm"><i class="fas fa-tags"></i></a></td></tr>');
        });
    }).fail(function(){$('#risco-loading').hide();});
}
</script>
@stop