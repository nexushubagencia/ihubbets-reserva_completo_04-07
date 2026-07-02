@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fa fa-area-chart"></i> Gerenciar Mercados <small>Organize seus mercados por cambistas</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Selecione um Cambista:</label>
                <select class="form-control" id="select-cambista" onchange="loadMercados()">
                    <option value="">Selecione...</option>
                </select>
            </div>
        </div>
        <div id="mercados-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div class="row" id="mercados-container"></div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){
    $.get('/admin/cambistas-list', function(data){
        if(data) data.forEach(function(c){
            $('#select-cambista').append('<option value="'+c.id+'">'+c.name+'</option>');
        });
    });
});

function loadMercados(){
    var id = $('#select-cambista').val();
    if(!id){ $('#mercados-container').empty(); return; }
    $('#mercados-loading').show(); $('#mercados-container').empty();
    $.get('/admin/mercados-user/'+id, function(data){
        $('#mercados-loading').hide();
        if(data) data.forEach(function(m){
            $('#mercados-container').append(`
                <div class="col-md-4 mb-3">
                    <label>${m.name}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="perc-${m.id}" value="${m.porcentagem}">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="button" onclick="alterarMercado(${m.id})">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
        });
    }).fail(function(){ $('#mercados-loading').hide(); toastr.error('Erro ao carregar mercados'); });
}

function alterarMercado(id){
    var perc = $('#perc-'+id).val();
    $.ajax({
        url: '/admin/mercados/'+id,
        type: 'PUT',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), porcentagem: perc },
        success: function(){ toastr.info('Mercado atualizado!'); loadMercados(); },
        error: function(){ toastr.error('Erro ao atualizar mercado!'); }
    });
}
</script>
@stop