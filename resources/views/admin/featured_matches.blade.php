@extends('adminlte::page')
@section('title', 'Partidas em Destaque')
@section('content_header')
    <h1><i class="fas fa-star text-warning"></i> Partidas em Destaque</h1>
    
@stop
@section('content')
<div class="card card-warning card-outline">
    <div class="card-body">
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> Selecione as partidas que deseja destacar no sistema de apostas.</div>
        <div id="featured-loading" class="text-center p-3"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#1E282C;color:#FFF;">
                    <tr><th>LIGA</th><th>CASA</th><th>FORA</th><th>DATA</th><th>DESTAQUE</th></tr>
                </thead>
                <tbody id="featured-tbody"></tbody>
            </table>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){
    $.get('/admin/list-confrontos', function(data){
        $('#featured-loading').hide();
        if(data) data.forEach(function(liga){
            if(liga.match) liga.match.forEach(function(m){
                $('#featured-tbody').append('<tr><td>'+liga.league+'</td><td>'+m.home+'</td><td>'+m.away+'</td><td>'+m.date+'</td><td><button class="btn btn-warning btn-sm"><i class="fas fa-star"></i></button></td></tr>');
            });
        });
    }).fail(function(){ $('#featured-loading').hide(); });
});
</script>
@stop
