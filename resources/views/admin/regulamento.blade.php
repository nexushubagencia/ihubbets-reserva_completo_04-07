@extends('adminlte::page')

@section('title', 'Regulamento')

@section('content_header')
    <h1><i class="fas fa-file-alt text-primary mr-2"></i> Gerenciar Regulamento</h1>
@stop

@section('content')
<link rel="stylesheet" href="{{ asset('vendor/summernote/summernote-bs4.min.css') }}">
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Texto do Regulamento (Exibido no Frontend)</h3>
    </div>
    <div class="card-body">
        <form id="form-regulamento">
            @csrf
            <input type="hidden" id="reg-id">
            <div class="form-group">
                <textarea id="reg-texto" name="regulamento" class="form-control"></textarea>
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
<script src="{{ asset('vendor/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('vendor/summernote/lang/summernote-pt-BR.min.js') }}"></script>
<script>
$(document).ready(function(){
    $('#reg-texto').summernote({
        height: 400,
        lang: 'pt-BR',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                for (var i = 0; i < files.length; i++) {
                    uploadImage(files[i], this);
                }
            }
        }
    });

    loadRegulamento();

    function uploadImage(file, editor) {
        var data = new FormData();
        data.append('image', file);
        data.append('_token', '{{ csrf_token() }}');
        $.ajax({
            url: '/admin/regulamento/upload-image',
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function(url) {
                $(editor).summernote('insertImage', url);
            },
            error: function() {
                toastr.error('Erro ao enviar imagem.');
            }
        });
    }

    function loadRegulamento(){
        $.get("{{ route('admin.regulamento.list') }}", function(data){
            var id = null;
            var texto = '';

            if(data && data.length > 0 && data[0]) {
                id = data[0].id;
                texto = data[0].regulamento || '';
            } else if(data && data.id) {
                id = data.id;
                texto = data.regulamento || '';
            }

            if(id) {
                $('#reg-id').val(id);
                $('#reg-texto').summernote('code', texto);
            } else {
                toastr.warning('Nenhum regulamento encontrado.');
            }
        }).fail(function() {
            toastr.error('Erro ao carregar regulamento atual.');
        });
    }

    $('#btn-save-reg').on('click', function() {
        var id = $('#reg-id').val();
        if(!id) {
            toastr.error('ID do site não encontrado. Recarregue a página.');
            return;
        }

        var txt = $('#reg-texto').summernote('code');
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
