@extends('adminlte::page')
@section('title', 'Gerenciar Odds Geral')
@section('content_header')
    <h1><i class="fas fa-percent"></i> Gerenciar Odds Geral</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Selecione o Mercado:</label>
                <select class="form-control" id="odd-mercado" onchange="loadOdds()">
                    <option value="">Selecione...</option>
                </select>
            </div>
        </div>
        <div id="odds-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead style="background:#1E282C;color:#FFF;">
                    <tr><th>ODD</th><th>PORCENTAGEM</th><th>STATUS</th><th>AÇÃO</th></tr>
                </thead>
                <tbody id="odds-tbody"></tbody>
            </table>
        </div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){
    $.get('/admin/list-mercados',function(d){if(d)d.forEach(function(m){$('#odd-mercado').append('<option value="'+m.name+'">'+m.name+'</option>');});});
});
function loadOdds(){
    var m = $('#odd-mercado').val(); if(!m){$('#odds-tbody').empty();return;}
    $('#odds-loading').show(); $('#odds-tbody').empty();
    $.post('/admin/list-odds',{_token:'{{csrf_token()}}',mercado_name:m},function(data){
        $('#odds-loading').hide();
        if(data) data.forEach(function(o){
            var st = o.status==1 ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Bloqueado</span>';
            var btn = o.status==1
                ? '<button class="btn btn-danger btn-sm" onclick="toggleOdd('+o.id+',0)"><i class="fas fa-ban"></i></button>'
                : '<button class="btn btn-success btn-sm" onclick="toggleOdd('+o.id+',1)"><i class="fas fa-check"></i></button>';
            $('#odds-tbody').append('<tr><td>'+o.name+'</td><td><div class="input-group" style="width:200px"><input type="text" class="form-control" id="odd-perc-'+o.id+'" value="'+(o.porcentagem||'')+'"><div class="input-group-append"><button class="btn btn-primary" onclick="updateOdd('+o.id+')"><i class="fas fa-save"></i></button></div></div></td><td>'+st+'</td><td>'+btn+'</td></tr>');
        });
    }).fail(function(){ $('#odds-loading').hide(); });
}
function toggleOdd(id,st){$.ajax({url:'/admin/update-odd/'+id,type:'PUT',data:{_token:'{{csrf_token()}}',status:st},success:function(){loadOdds();}});}
function updateOdd(id){var p=$('#odd-perc-'+id).val();$.ajax({url:'/admin/update-odd/'+id,type:'PUT',data:{_token:'{{csrf_token()}}',porcentagem:p},success:function(){toastr.success('Alterado com sucesso!');loadOdds();}});}
</script>
@stop
