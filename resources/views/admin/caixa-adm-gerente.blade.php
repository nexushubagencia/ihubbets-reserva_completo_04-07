@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-university"></i> Caixa (Gerentes) <small>Relatórios</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Gerente:</label>
                <select id="select-gerente" class="form-control" onchange="searchGerente()">
                    <option value="Todos">Todos</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr style="background:#00466A;color:#fff;font-size:13px;text-align:center;">
                        <th>GERENTE</th><th>QUANTIDADE</th><th>COMISSÕES (GERENTE)</th><th>ENTRADAS</th><th>ENTRADAS EM ABERTO</th><th>SAÍDAS</th><th>COMISSÕES (CAMBISTAS)</th><th>TOTAL</th><th>FECHAR</th>
                    </tr>
                </thead>
                <tbody id="caixa-ger-tbody" style="text-align:center;"></tbody>
                <tfoot id="caixa-ger-tfoot">
                    <tr style="background:#00466A;color:#fff;font-size:13px;text-align:center;">
                        <td>TOTAL</td><td id="tg-qtd">0</td><td id="tg-cg">R$ 0,00</td><td id="tg-ent" style="background:#009688;color:#fff;">R$ 0,00</td><td id="tg-ab" style="background:#2C3B41;color:#fff;">R$ 0,00</td><td id="tg-sa" style="background:#FF0000;color:#fff;">R$ 0,00</td><td id="tg-cc">R$ 0,00</td><td id="tg-to">R$ 0,00</td><td></td>
                    </tr>
                </tfoot>
            </table>
            <div id="cg-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>

<!-- Modal Prestar Contas -->
<div class="modal fade" id="modal-fechamento">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fas fa-university"></i> <b id="fech-colaborador"></b></h4>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body box box-primary text-center">
                <input type="hidden" id="fech-id">
                <div class="p-2 mb-1" style="background:#009688;color:#fff;font-size:20px;">Entradas: <span id="fech-ent"></span></div>
                <div class="p-2 mb-1" style="background:#2C3B41;color:#fff;font-size:20px;">Entradas (Em Aberto): <span id="fech-ab"></span></div>
                <div class="p-2 mb-1" style="background:#D73925;color:#fff;font-size:20px;">Saídas: <span id="fech-sa"></span></div>
                <div class="p-2 mb-1" style="background:#00466A;color:#fff;font-size:20px;">Comissão Gerente: <span id="fech-cg"></span></div>
                <div class="p-2 mb-1" style="background:#00466A;color:#fff;font-size:20px;">Comissão Cambistas: <span id="fech-cc"></span></div>
                <div class="p-2 mb-3" id="fech-total-div" style="font-size:20px;">Total: <span id="fech-to"></span></div>
                <button class="btn btn-primary btn-lg btn-block" id="btn-prestar-conta" onclick="prestarConta()">Prestar Conta</button>
            </div>
        </div>
    </div>
</div>
@stop
@section('css')
<style>.positivo{background:#009688!important;color:#FFF!important;}.negativo{background:#FF0000!important;color:#FFF!important;}</style>
@stop
@section('js')
<script>
function fm(n){return "R$ "+parseFloat(n||0).toFixed(2).replace('.',',').replace(/(\d)(?=(\d{3})+\,)/g,"$1.");}
$(document).ready(function(){
    $.get('/admin/list-gerentes',function(d){if(d)d.forEach(function(g){$('#select-gerente').append('<option value="'+g.id+'">'+g.name+'</option>');});});
    loadCaixa();
});
function loadCaixa(){
    $('#cg-loading').show();
    $.get('/admin/list-caixa-adm-gerente',function(data){ renderCaixa(data); }).fail(function(){$('#cg-loading').hide();});
}
function searchGerente(){
    var g = $('#select-gerente').val();
    if(g === 'Todos') { $('#caixa-ger-tfoot').show(); loadCaixa(); return; }
    $('#caixa-ger-tfoot').hide(); $('#cg-loading').show();
    $.post('/admin/search-caixa-adm-gerente',{_token:$('meta[name="csrf-token"]').attr('content'),gerente:g},function(data){ renderCaixa(data); });
}
function renderCaixa(data){
    var tb=$('#caixa-ger-tbody');tb.empty();$('#cg-loading').hide();
    var tQ=0,tE=0,tA=0,tS=0,tCG=0,tCC=0,tG=0;
    if(data)data.forEach(function(c){
        var cls=parseFloat(c.total)>=0?'positivo':'negativo';
        var colab = c.colaborador || c.name || '';
        tb.append('<tr><td>'+colab+'</td><td>'+(c.quantidade||0)+'</td><td>'+fm(c.comissao_gerente)+'</td><td>'+fm(c.entradas)+'</td><td>'+fm(c.entradas_abertas)+'</td><td>'+fm(c.saidas)+'</td><td>'+fm(c.comissoes)+'</td><td class="'+cls+'">'+fm(c.total)+'</td><td><button class="btn btn-success btn-sm" onclick=\'openFechamento('+JSON.stringify(c).replace(/'/g,"&#39;")+')\'><i class="fas fa-paper-plane"></i></button></td></tr>');
        tQ+=parseFloat(c.quantidade||0);tE+=parseFloat(c.entradas||0);tA+=parseFloat(c.entradas_abertas||0);tS+=parseFloat(c.saidas||0);tCG+=parseFloat(c.comissao_gerente||0);tCC+=parseFloat(c.comissoes||0);tG+=parseFloat(c.total||0);
    });
    $('#tg-qtd').text(tQ);$('#tg-cg').text(fm(tCG));$('#tg-ent').text(fm(tE));$('#tg-ab').text(fm(tA));$('#tg-sa').text(fm(tS));$('#tg-cc').text(fm(tCC));$('#tg-to').text(fm(tG)).attr('class',tG>=0?'positivo':'negativo');
}
function openFechamento(c){
    $('#fech-id').val(c.id); $('#fech-colaborador').text(c.colaborador||c.name);
    $('#fech-ent').text(fm(c.entradas)); $('#fech-ab').text(fm(c.entradas_abertas));
    $('#fech-sa').text(fm(c.saidas)); $('#fech-cg').text(fm(c.comissao_gerente));
    $('#fech-cc').text(fm(c.comissoes)); $('#fech-to').text(fm(c.total));
    $('#fech-total-div').css('background', parseFloat(c.total)>=0 ? '#11721D':'#D73925').css('color','#fff');
    if(parseFloat(c.entradas_abertas) < 1){ $('#btn-prestar-conta').show(); }else{ $('#btn-prestar-conta').hide(); }
    $('#modal-fechamento').modal('show');
}
function prestarConta(){
    var id=$('#fech-id').val(); if(!confirm('Prestar conta?'))return;
    $.ajax({url:'/admin/encerrar-caixa/'+id,type:'PUT',data:{_token:$('meta[name="csrf-token"]').attr('content')},success:function(){$('#modal-fechamento').modal('hide');loadCaixa();},error:function(){toastr.error('Erro');}});
}
</script>
@stop