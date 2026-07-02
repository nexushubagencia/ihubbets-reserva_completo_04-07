@extends('adminlte::page')

@section('title', 'Regulamento')

@section('content_header')
    <h1><i class="fas fa-file-alt text-primary mr-2"></i> Gerenciar Regulamento</h1>
@stop

@section('content')
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Texto do Regulamento (Exibido no Frontend)</h3>
    </div>
    <div class="card-body">
        <form id="form-regulamento">
            @csrf
            <input type="hidden" id="reg-id">
            <div class="form-group">
                <textarea id="reg-texto" name="regulamento" class="form-control" rows="15"></textarea>
            </div>
            <div class="text-right mt-3">
                <button type="button" class="btn btn-lg btn-success shadow-sm" id="btn-save-reg">
                    <i class="fas fa-save mr-1"></i> SALVAR REGULAMENTO
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<!-- CKEditor 4 -->
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
$(document).ready(function(){
    // Inicializa o CKEditor
    if ($('#reg-texto').length) {
        CKEDITOR.replace('reg-texto', {
            height: 500,
            language: 'pt-br',
            toolbar: [
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'Table', 'HorizontalRule', 'SpecialChar' ] },
                { name: 'styles', items: [ 'Format', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'tools', items: [ 'Maximize' ] }
            ]
        });
    }

    // Carrega o regulamento atual
    loadRegulamento();

    function loadRegulamento(){
        $.get("{{ route('admin.regulamento.list') }}", function(data){
            if(data && data.length > 0) {
                $('#reg-id').val(data[0].id);
                if (CKEDITOR.instances['reg-texto']) {
                    CKEDITOR.instances['reg-texto'].setData(data[0].regulamento || '');
                }
            } else {
                // Fallback se não vier array (alguns controllers retornam objeto direto)
                if(data.id) {
                    $('#reg-id').val(data.id);
                    if (CKEDITOR.instances['reg-texto']) {
                        CKEDITOR.instances['reg-texto'].setData(data.regulamento || '');
                    }
                }
            }
        }).fail(function() {
            toastr.error('Erro ao carregar regulamento atual.');
        });
    }

    // Ação de Salvar
    $('#btn-save-reg').on('click', function() {
        var id = $('#reg-id').val();
        if(!id) {
            toastr.error('ID do site não encontrado. Recarregue a página.');
            return;
        }

        var txt = CKEDITOR.instances['reg-texto'].getData();
        var btn = $(this);
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> SALVANDO...');

        $.ajax({
            url: '/admin/regulamento/update/' + id,
            type: 'POST',
            data: { 
                _token: "{{ csrf_token() }}", 
                regulamento: txt 
            },
            success: function(response) {
                toastr.success('Regulamento atualizado com sucesso!');
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> SALVAR REGULAMENTO');
            },
            error: function() {
                toastr.error('Erro ao salvar regulamento!');
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> SALVAR REGULAMENTO');
            }
        });
    });
});
</script>
@stop