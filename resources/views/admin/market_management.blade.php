@extends('adminlte::page')
@section('title', 'Gerenciar Mercados')
@section('content_header')
    <h1><i class="fas fa-chart-bar"></i> Gerenciar Mercados Geral</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div id="merc-loading" class="text-center p-3"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#1E282C;color:#FFF;">
                    <tr><th>MERCADO</th><th>PORCENTAGEM</th><th>STATUS</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="merc-tbody"></tbody>
            </table>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){ loadMercados(); });
function loadMercados(){
    $('#merc-loading').show(); $('#merc-tbody').empty();
    $.get('/admin/list-mercados', function(data){
        $('#merc-loading').hide();
        if(data) data.forEach(function(m){
            var st = m.status == 1 ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Bloqueado</span>';
            var btn = m.status == 1
                ? '<button class="btn btn-danger btn-sm" onclick="toggleMerc('+m.id+',0)"><i class="fas fa-ban"></i></button>'
                : '<button class="btn btn-success btn-sm" onclick="toggleMerc('+m.id+',1)"><i class="fas fa-check"></i></button>';
            $('#merc-tbody').append('<tr><td>'+m.name+'</td><td><div class="input-group" style="width:200px"><input type="text" class="form-control" id="merc-perc-'+m.id+'" value="'+(m.porcentagem||'')+'"><div class="input-group-append"><button class="btn btn-primary" onclick="updateMerc('+m.id+')"><i class="fas fa-save"></i></button></div></div></td><td>'+st+'</td><td>'+btn+'</td></tr>');
        });
    }).fail(function(){ $('#merc-loading').hide(); });
}
function toggleMerc(id,st){$.ajax({url:'/admin/update-mercado/'+id,type:'PUT',data:{_token:'{{csrf_token()}}',status:st},success:function(){loadMercados();}});}
function updateMerc(id){var p=$('#merc-perc-'+id).val();$.ajax({url:'/admin/update-mercado/'+id,type:'PUT',data:{_token:'{{csrf_token()}}',porcentagem:p},success:function(){toastr.success('Alterado com sucesso!');loadMercados();}});}
</script>
@stop
