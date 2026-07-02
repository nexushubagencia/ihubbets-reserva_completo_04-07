@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-futbol"></i> Confrontos</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white" style="font-size:12px;text-align:center;">
                    <tr><th>LIGA</th><th>MANDANTE</th><th>VISITANTE</th><th>DATA</th><th>STATUS</th><th>SCORE</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="confrontos-tbody" style="text-align:center;font-size:13px;"></tbody>
            </table>
            <div id="confrontos-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
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
$(document).ready(function(){
    $('#confrontos-loading').show();
    $.get('/admin/list-confrontos',function(data){
        var tb=$('#confrontos-tbody');tb.empty();$('#confrontos-loading').hide();
        if(!data||!data.length){tb.append('<tr><td colspan="7">Nenhum confronto encontrado</td></tr>');return;}
        data.forEach(function(c){
            var statusBadge='<span class="badge badge-info">'+(c.status||'Aguardando')+'</span>';
            tb.append('<tr><td>'+(c.league||c.liga||'-')+'</td><td>'+(c.home||c.mandante||'-')+'</td><td>'+(c.away||c.visitante||'-')+'</td><td>'+fd(c.match_date||c.data)+'</td><td>'+statusBadge+'</td><td>'+(c.score||'- x -')+'</td><td><button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button></td></tr>');
        });
    }).fail(function(){$('#confrontos-loading').hide();$('#confrontos-tbody').append('<tr><td colspan="7">Erro ao carregar confrontos</td></tr>');});
});
</script>
@stop