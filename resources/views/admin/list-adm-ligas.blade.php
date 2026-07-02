@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fa fa-trophy"></i> Ligas <small>Gerencie aqui as suas ligas</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Adicionar Liga:</label>
                <div class="input-group">
                    <select class="form-control" id="add-liga-select"><option value="">Selecione uma liga...</option></select>
                    <div class="input-group-append">
                        <button class="btn btn-success" onclick="addLiga()"><i class="fas fa-plus"></i> Adicionar</button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <h4><b>Total bloqueadas: <span id="total-block">0</span></b></h4>
            </div>
        </div>
        <h5>Ligas Bloqueadas:</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#1E282C;color:#FFF;">
                    <tr><th>LIGA</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="ligas-block-tbody"></tbody>
            </table>
            <div id="ligas-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){
    loadBlockedLigas();
    $.get('/admin/show-ligas', function(data){
        if(data) data.forEach(function(l){
            $('#add-liga-select').append('<option value="'+l.league+'">'+l.league+'</option>');
        });
    });
});

function loadBlockedLigas(){
    $('#ligas-loading').show(); $('#ligas-block-tbody').empty();
    $.get('/admin/list-ligas', function(data){
        $('#ligas-loading').hide();
        $('#total-block').text(data ? data.length : 0);
        if(data) data.forEach(function(l){
            $('#ligas-block-tbody').append('<tr><td>'+l.league+'</td><td><button class="btn btn-success btn-sm" onclick="unblockLiga('+l.id+')"><i class="fas fa-unlock"></i> Desbloquear</button></td></tr>');
        });
    }).fail(function(){ $('#ligas-loading').hide(); });
}

function addLiga(){
    var liga = $('#add-liga-select').val();
    if(!liga){ toastr.error('Selecione uma liga!'); return; }
    $.post('/admin/bloquear-ligas', {_token: $('meta[name="csrf-token"]').attr('content'), league: liga}, function(){
        toastr.info('Liga bloqueada!'); loadBlockedLigas();
    }).fail(function(){ toastr.error('Erro!'); });
}

function unblockLiga(id){
    if(!confirm('Desbloquear esta liga?')) return;
    $.ajax({
        url: '/admin/deletar-ligas-main/'+id,
        type: 'DELETE',
        data: {_token: $('meta[name="csrf-token"]').attr('content')},
        success: function(){ loadBlockedLigas(); },
        error: function(){ toastr.error('Erro!'); }
    });
}
</script>
@stop