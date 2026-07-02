@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fa fa-trophy"></i> Ligas <small>Suas ligas principais</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Esporte:</label>
                <select class="form-control" id="filtro-esporte">
                    <option>Todos</option>
                    <option>Futebol</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <h4><b>Total: <span id="total-ligas">0</span></b></h4>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#1E282C;color:#FFF;">
                    <tr><th>SPORT</th><th>LIGA</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="ligas-tbody"></tbody>
            </table>
            <div id="ligas-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){ loadLigas(); });

function loadLigas(){
    $('#ligas-loading').show(); $('#ligas-tbody').empty();
    $.get('/admin/show-ligas-principais', function(data){
        $('#ligas-loading').hide();
        $('#total-ligas').text(data ? data.length : 0);
        if(data) data.forEach(function(l){
            $('#ligas-tbody').append('<tr><td><b>'+l.sport+'</b></td><td>'+l.league+'</td><td><button class="btn btn-danger btn-sm" onclick="deleteMain('+l.id+')"><i class="fas fa-trash"></i> Remover</button></td></tr>');
        });
    }).fail(function(){ $('#ligas-loading').hide(); });
}

function deleteMain(id){
    if(!confirm('Remover liga principal?')) return;
    $.ajax({
        url: '/admin/deletar-ligas-main/'+id,
        type: 'DELETE',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function(){ toastr.success('Liga removida!'); loadLigas(); },
        error: function(){ toastr.error('Erro ao remover!'); }
    });
}
</script>
@stop