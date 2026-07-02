@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-futbol"></i> Confrontos Bloqueados</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white" style="font-size:12px;text-align:center;">
                    <tr><th>ESPORTE</th><th>CONFRONTO</th><th>DATA</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="matchs-tbody" style="text-align:center;font-size:13px;"></tbody>
            </table>
            <div id="matchs-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
function fd(d){
    if(!d) return '';
    var dt = new Date(d);
    var dd = String(dt.getDate()).padStart(2,'0');
    var mm = String(dt.getMonth()+1).padStart(2,'0');
    var yy = dt.getFullYear();
    var hh = String(dt.getHours()).padStart(2,'0');
    var mi = String(dt.getMinutes()).padStart(2,'0');
    return dd+'/'+mm+'/'+yy+' '+hh+':'+mi;
}
$(document).ready(function(){loadMatchs();});
function loadMatchs(){
    $('#matchs-loading').show();
    $.get('/admin/list-matchs',function(data){
        var tb=$('#matchs-tbody');tb.empty();$('#matchs-loading').hide();
        if(!data||!data.length){tb.append('<tr><td colspan="4">Nenhum confronto bloqueado</td></tr>');return;}
        data.forEach(function(m){
            tb.append('<tr><td>'+m.sport+'</td><td>'+m.confronto+'</td><td>'+fd(m.date)+'</td><td><button class="btn btn-success btn-sm" onclick="liberar('+m.id+')"><i class="fas fa-check"></i> Desbloquear</button></td></tr>');
        });
    }).fail(function(){$('#matchs-loading').hide();});
}
function liberar(id){if(!confirm('Desbloquear confronto?'))return;$.ajax({url:'/admin/update-match/'+id,type:'PUT',data:{_token:$('meta[name="csrf-token"]').attr('content'),visible:'Sim'},success:function(){loadMatchs();}});}
</script>
@stop