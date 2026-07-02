@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-trophy"></i> Ligas Bloqueadas</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white" style="font-size:12px;text-align:center;">
                    <tr><th>NOME DA LIGA</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="ligas-tbody" style="text-align:center;font-size:13px;"></tbody>
            </table>
            <div id="ligas-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){loadLigas();});
function loadLigas(){
    $('#ligas-loading').show();
    $.get('/admin/list-ligas',function(data){
        var tb=$('#ligas-tbody');tb.empty();$('#ligas-loading').hide();
        if(!data||!data.length){tb.append('<tr><td colspan="2">Nenhuma liga bloqueada</td></tr>');return;}
        data.forEach(function(l){
            tb.append('<tr><td>'+l.league+'</td><td><button class="btn btn-success btn-sm" onclick="liberar(\''+l.league+'\')"><i class="fas fa-check"></i> Desbloquear</button></td></tr>');
        });
    }).fail(function(){$('#ligas-loading').hide();});
}
function liberar(l){if(!confirm('Desbloquear liga?'))return;$.post('/admin/liberar-ligas',{_token:$('meta[name="csrf-token"]').attr('content'),league:l},function(){loadLigas();});}
</script>
@stop