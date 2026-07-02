@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-percent"></i> Gerenciar Odds (Cambistas) <small>Organize as odds dos seus cambistas</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Selecione um Cambista:</label>
                <select class="form-control" id="select-cambista" onchange="loadOdds()">
                    <option value="">Selecione...</option>
                </select>
            </div>
        </div>
        <div id="odds-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div id="odds-container"></div>
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

function loadOdds(){
    var id = $('#select-cambista').val();
    if(!id){ $('#odds-container').empty(); return; }
    $('#odds-loading').show(); $('#odds-container').empty();
    $.get('/admin/odds-user/'+id, function(data){
        $('#odds-loading').hide();
        if(data) data.forEach(function(m){
            var html = `<div class="card mb-3"><div class="card-header bg-primary"><h3 class="card-title">${m.mercado}</h3></div><div class="card-body"><div class="row">`;
            if(m.odds) m.odds.forEach(function(o){
                html += `
                    <div class="col-md-3 mb-2">
                        <label>${o.name}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="perc-odd-${o.id}" value="${o.porcentagem}">
                            <div class="input-group-append">
                                <button class="btn btn-success" type="button" onclick="alterarOdd(${o.id})"><i class="fas fa-check"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += `</div></div></div>`;
            $('#odds-container').append(html);
        });
    }).fail(function(){ $('#odds-loading').hide(); toastr.error('Erro ao carregar odds'); });
}

function alterarOdd(id){
    var perc = $('#perc-odd-'+id).val();
    $.ajax({
        url: '/admin/odds/'+id,
        type: 'PUT',
        data: { _token: $('meta[name="csrf-token"]').attr('content'), porcentagem: perc },
        success: function(){ toastr.info('Odd atualizada!'); },
        error: function(){ toastr.error('Erro ao atualizar odd!'); }
    });
}
</script>
@stop