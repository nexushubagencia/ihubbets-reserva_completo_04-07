@extends('adminlte::page')

@section('title', 'Gerenciar Bancas/Sites')

@section('content_header')
    <h1><i class="fas fa-server"></i> Gerenciar Bancas <small>Multi-tenant</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Início</a></li>
        <li class="active">Gerenciador</li>
    </ol>
@stop

@section('content')
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Todas as Bancas</h3>
    </div>
    <div class="box-body no-padding">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Domínio</th>
                    <th>Status</th>
                    <th>Usuários</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="sites-list">
                <tr><td colspan="6" class="text-center">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    loadSites();
});

function loadSites() {
    $.get('/admin/master/bancas-list', function(data) {
        var html = '';
        if (!data || data.length === 0) {
            html = '<tr><td colspan="6" class="text-center">Nenhuma banca encontrada</td></tr>';
        } else {
            data.forEach(function(s) {
                var status = s.status == 'active'
                    ? '<span class="label label-success">Ativo</span>'
                    : '<span class="label label-danger">'+(s.status||'Inativo')+'</span>';
                html += '<tr>';
                html += '<td>'+s.id+'</td>';
                html += '<td>'+s.name+'</td>';
                html += '<td>'+(s.domain||'N/A')+'</td>';
                html += '<td>'+status+'</td>';
                html += '<td>'+(s.users_count||0)+'</td>';
                html += '<td>';
                html += '<a href="/admin/gerenciador/'+s.id+'/edit" class="btn btn-xs btn-info"><i class="fa fa-edit"></i></a> ';
                html += '<form method="POST" action="/admin/gerenciador/'+s.id+'/toggle" style="display:inline">';
                html += '@csrf <button class="btn btn-xs btn-warning" type="submit"><i class="fa fa-toggle-on"></i></button>';
                html += '</form>';
                html += '</td></tr>';
            });
        }
        $('#sites-list').html(html);
    });
}
</script>
@stop
