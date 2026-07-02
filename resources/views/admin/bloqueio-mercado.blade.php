@extends('adminlte::page')
@section('title', config('adminlte.title_adm_geral'))
@section('content_header')
    <h1><i class="fas fa-shopping-cart"></i> Mercados</h1>
    
@stop
@section('content')
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header border-0">
        <h3 class="card-title text-bold"><i class="fas fa-shopping-cart text-primary"></i> Gerenciamento de Mercados</h3>
    </div>
    <div class="card-body">
        <div id="mercados-loading" class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Carregando...</span>
            </div>
            <p class="mt-2 text-muted">Buscando mercados ativos...</p>
        </div>
        <div class="row" id="mercados-container"></div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function(){
    loadMercados();
});

function loadMercados(){
    $('#mercados-loading').show();
    $('#mercados-container').empty();
    
    $.get('/admin/list-mercados', function(data){
        $('#mercados-loading').hide();
        if(!data || data.length === 0){
            $('#mercados-container').html('<div class="col-12 alert alert-warning text-center">Nenhum mercado encontrado para o seu usuário.</div>');
            return;
        }
        
        data.forEach(function(m){
            var statusBadge = m.status == 1 
                ? '<span class="badge badge-success px-2 py-1">ATIVO</span>' 
                : '<span class="badge badge-danger px-2 py-1">BLOQUEADO</span>';
            
            var toggleBtn = m.status == 1
                ? '<button class="btn btn-outline-danger btn-sm" onclick="toggleMerc('+m.id+',0)" title="Bloquear"><i class="fas fa-ban"></i></button>'
                : '<button class="btn btn-outline-success btn-sm" onclick="toggleMerc('+m.id+',1)" title="Ativar"><i class="fas fa-check"></i></button>';
            
            var card = '<div class="col-md-4 mb-4">' +
                '<div class="card card-widget h-100 shadow-sm border">' +
                    '<div class="card-body p-3">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<h6 class="text-bold mb-0">'+m.name+'</h6>' +
                            statusBadge +
                        '</div>' +
                        '<div class="input-group input-group-sm mb-2">' +
                            '<div class="input-group-prepend"><span class="input-group-text bg-light text-xs">%</span></div>' +
                            '<input type="number" id="merc-perc-'+m.id+'" class="form-control" value="'+(m.porcentagem||0)+'" step="0.01">' +
                            '<div class="input-group-append">' +
                                '<button class="btn btn-primary" onclick="updateMerc('+m.id+')"><i class="fas fa-save"></i></button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="text-center">' + toggleBtn + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
            $('#mercados-container').append(card);
        });
    }).fail(function(){
        $('#mercados-loading').hide();
        toastr.error('Erro ao carregar mercados.');
    });
}

function toggleMerc(id, st){
    var msg = st == 1 ? 'Ativando mercado...' : 'Bloqueando mercado...';
    toastr.info(msg);
    
    $.ajax({
        url: '/admin/update-mercado/' + id,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            status: st
        },
        success: function(){
            toastr.success('Status atualizado com sucesso!');
            loadMercados();
        },
        error: function(){
            toastr.error('Falha ao atualizar status.');
        }
    });
}

function updateMerc(id){
    var p = $('#merc-perc-' + id).val();
    toastr.info('Salvando porcentagem...');
    
    $.ajax({
        url: '/admin/update-mercado/' + id,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            porcentagem: p
        },
        success: function(){
            toastr.success('Porcentagem salva com sucesso!');
            loadMercados();
        },
        error: function(){
            toastr.error('Falha ao salvar porcentagem.');
        }
    });
}
</script>
@stop