@extends('adminlte::page')

@section('title', 'Alterar Logo do Site | IHUB BETS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-paint-brush"></i> Alterar Logo do Site <small class="text-muted">Identidade visual da sua banca</small></h1>
        
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.settings.layout.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <!-- Card Principal da Logo -->
            <div class="col-md-12">
                <div class="card card-outline card-success shadow-sm border-0 rounded-lg">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            
                            <!-- Logo Atual -->
                            <div class="col-md-4 text-center border-right">
                                <h6 class="font-weight-bold text-muted mb-3 text-uppercase small">Logo Atual</h6>
                                <div class="p-4 bg-dark rounded d-flex align-items-center justify-content-center mx-auto" style="min-height: 150px; border: 1px solid #333;">
                                    @if($settings && $settings->logo_path)
                                        <img src="{{ asset($settings->logo_path) }}" class="img-fluid" style="max-height: 100px;" alt="Logo Atual">
                                    @else
                                        <img src="{{ asset('img/logo.png') }}" class="img-fluid" style="max-height: 100px; opacity: 0.5;" alt="Logo Padrão">
                                    @endif
                                </div>
                                <p class="mt-2 text-muted small">Esta é a imagem que aparece no topo do site e nos tickets.</p>
                            </div>

                            <!-- Upload de Nova Logo -->
                            <div class="col-md-8 px-md-5">
                                <h6 class="font-weight-bold text-muted mb-3 text-uppercase small">Adicionar nova imagem</h6>
                                
                                <div class="upload-zone p-5 text-center rounded border-dashed" style="border: 2px dashed #ddd; background: #fafafa; cursor: pointer;" onclick="$('#logoInput').click()">
                                    <div id="uploadPlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-success mb-3"></i>
                                        <p class="mb-0 font-weight-bold">Arraste ou clique para adicionar uma imagem.</p>
                                        <small class="text-muted">Formatos aceitos: PNG, JPG, JPEG (Máx 2MB)</small>
                                    </div>
                                    <div id="previewZone" style="display:none;">
                                        <img id="logoPreview" class="img-fluid rounded mb-2" style="max-height: 120px;">
                                        <p class="mb-0 text-success"><i class="fas fa-check-circle"></i> Imagem selecionada!</p>
                                    </div>
                                    <input type="file" name="logo_file" id="logoInput" class="d-none" accept="image/*" onchange="previewLogo(this)">
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success btn-lg px-5 font-weight-bold shadow-sm">
                                        <i class="fas fa-save mr-2"></i> SALVAR LOGO
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Adicional para Favicon (Ícone da Aba) -->
            <div class="col-md-6 mt-4">
                <div class="card h-100 shadow-sm border-0 rounded-lg">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-window-maximize mr-2 text-info"></i> Favicon (Ícone do Navegador)</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="p-3 bg-light rounded mr-3 text-center" style="width: 80px;">
                                @if($settings && $settings->favicon_path)
                                    <img src="{{ asset($settings->favicon_path) }}" style="width: 32px; height: 32px;" alt="Fav">
                                @else
                                    <i class="fas fa-globe-americas fa-2x text-muted"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="small text-muted mb-1">Escolher ícone (.png ou .ico)</label>
                                <div class="custom-file">
                                    <input type="file" name="favicon_file" class="custom-file-input" id="favInput" accept="image/png, image/x-icon">
                                    <label class="custom-file-label" for="favInput">Selecione o ícone...</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Dicas de Design -->
            <div class="col-md-6 mt-4">
                <div class="card h-100 bg-gradient-info text-white shadow-sm border-0 rounded-lg">
                    <div class="card-body d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-lightbulb fa-3x opacity-50"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold">Dica Profissional</h6>
                            <p class="mb-0 small">Use logos com fundo transparente (formato .PNG) para um acabamento perfeito sobre qualquer cor de fundo da sua banca.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .rounded-lg { border-radius: 12px !important; }
    .border-dashed { border: 2px dashed #ddd !important; transition: 0.3s; }
    .upload-zone:hover { border-color: #28a745 !important; background: #f0fff4 !important; }
    .btn-lg { border-radius: 8px !important; }
    .card-outline.card-success { border-top: 3px solid #28a745 !important; }
</style>
@stop

@section('js')
<script>
    // Suporte para Arrastar e Soltar (Drag and Drop)
    let dropZone = document.querySelector('.upload-zone');
    let fileInput = document.getElementById('logoInput');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.style.borderColor = "#28a745";
        dropZone.style.background = "#f0fff4";
    }

    function unhighlight(e) {
        dropZone.style.borderColor = "#ddd";
        dropZone.style.background = "#fafafa";
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;
        fileInput.files = files;
        previewLogo(fileInput);
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').attr('src', e.target.result);
                $('#uploadPlaceholder').hide();
                $('#previewZone').fadeIn();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Atualiza label do favicon
    $('#favInput').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
</script>
@stop
