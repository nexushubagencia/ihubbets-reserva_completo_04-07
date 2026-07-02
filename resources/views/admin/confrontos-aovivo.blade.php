@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-satellite-dish"></i> Confrontos Ao Vivo</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div id="live-container"></div>
        <div id="live-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
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
    loadLive();
    // Simulate real-time updates every 30 seconds
    setInterval(loadLive, 30000);
});
function loadLive(){
    $('#live-loading').show();
    $.get('/admin/confrontos-aovivo-show',function(data){
        $('#live-loading').hide();
        var c=$('#live-container');c.empty();
        if(!data||data.length===0){c.html('<p class="text-center">Nenhum confronto ao vivo no momento.</p>');return;}
        data.forEach(function(league){
            var h='<div class="card card-info"><div class="card-header"><h3 class="card-title"><i class="fas fa-trophy"></i> '+league.league+'</h3><div class="card-tools"><button class="btn btn-tool" onclick="blockL(\''+league.league+'\')"><i class="fas fa-ban text-danger"></i> Bloquear Liga</button></div></div><div class="card-body p-0"><table class="table table-striped text-center"><thead><tr><th>SPORT</th><th>CONFRONTO</th><th>DATA</th><th>AÇÃO</th></tr></thead><tbody>';
            if(league.match) league.match.forEach(function(m){
                h+='<tr><td><b><i class="fas fa-futbol"></i> '+(m.sport||'Futebol')+'</b></td><td>'+m.home+' <b>X</b> '+m.away+'</td><td><b>'+(m.time||'-')+' <span class="text-danger blink_me">\'</span></b></td><td><button class="btn btn-danger btn-sm" onclick="blockM('+m.event_id+',\''+m.sport+'\',\''+m.home+'\',\''+m.away+'\',\''+m.date+'\')"><i class="fas fa-ban"></i> Bloquear</button></td></tr>';
            });
            h+='</tbody></table></div></div>';
            c.append(h);
        });
    }).fail(function(){$('#live-loading').hide();});
}
function blockL(l){if(!confirm('Bloquear liga '+l+'?'))return;$.post('/admin/bloquear-ligas',{_token:$('meta[name="csrf-token"]').attr('content'),league:l},function(){toastr.info('Bloqueada!');loadLive();});}
function blockM(id,s,h,a,d){if(!confirm('Bloquear confronto '+h+' x '+a+'?'))return;$.post('/admin/update-match',{_token:$('meta[name="csrf-token"]').attr('content'),event_id:id,sport:s,confronto:h+' X '+a,date:d},function(){toastr.success('Bloqueado!');loadLive();});}
</script>
<style>
.blink_me { animation: blinker 1s linear infinite; }
@keyframes blinker { 50% { opacity: 0; } }
</style>
@stop