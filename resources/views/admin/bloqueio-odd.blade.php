@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-sort-amount-up"></i> Odds <small>Gerenciar Odds</small></h1>
    
@stop
@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Selecione um mercado:</label>
                <select class="form-control" id="odd-mercado" onchange="loadOdds()"><option value="">Selecione</option></select>
            </div>
        </div>
        <div id="odds-loading" class="text-center p-3" style="display:none;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
        <div class="row" id="odds-container"></div>
    </div>
</div>
@stop
@section('js')
<script>
$(document).ready(function(){
    $.get('/admin/list-mercados',function(d){if(d)d.forEach(function(m){$('#odd-mercado').append('<option value="'+m.name+'">'+m.name+'</option>');});});
});
function loadOdds(){
    var m=$('#odd-mercado').val();if(!m)return;
    $('#odds-loading').show();
    $.post('/admin/list-odds',{_token:$('meta[name="csrf-token"]').attr('content'),mercado_name:m},function(data){
        var c=$('#odds-container');c.empty();$('#odds-loading').hide();
        if(!data)return;
        data.forEach(function(o){
            var statusHtml = o.status==1 ? '<input type="text" value="Ativo" readonly class="form-control"><span class="input-group-append"><button class="btn btn-danger" onclick="toggleOdd('+o.id+',0)"><i class="fas fa-times"></i></button></span>' : '<input type="text" value="Bloqueado" readonly class="form-control"><span class="input-group-append"><button class="btn btn-primary" onclick="toggleOdd('+o.id+',1)"><i class="fas fa-check"></i></button></span>';
            var h='<div class="col-md-4 mb-3"><label>'+o.name+'</label><div class="input-group mb-1"><input type="text" id="odd-perc-'+o.id+'" class="form-control" value="'+o.porcentagem+'"><span class="input-group-append"><button class="btn btn-success" onclick="updateOdd('+o.id+')"><i class="fas fa-check"></i></button></span></div><div class="input-group">'+statusHtml+'</div></div>';
            c.append(h);
        });
    }).fail(function(){$('#odds-loading').hide();});
}
function toggleOdd(id, st){$.ajax({url:'/admin/update-odd/'+id,type:'PUT',data:{_token:$('meta[name="csrf-token"]').attr('content'),status:st},success:function(){loadOdds();}});}
function updateOdd(id){var p=$('#odd-perc-'+id).val();$.ajax({url:'/admin/update-odd/'+id,type:'PUT',data:{_token:$('meta[name="csrf-token"]').attr('content'),porcentagem:p},success:function(){toastr.success('Alterado com sucesso!');loadOdds();}});}
</script>
@stop