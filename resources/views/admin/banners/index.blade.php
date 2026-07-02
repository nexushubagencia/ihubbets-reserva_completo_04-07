@extends('adminlte::page')

@section('title', 'Gerenciar Banners | IHUB BETS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-images"></i> Banners Home <small class="text-muted">Banners da página inicial</small></h1>
        
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Botão de Adicionar (Estilo Card) -->
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm border-0 d-flex align-items-center justify-content-center" style="min-height: 200px; cursor: pointer; border: 2px dashed #ddd !important;" data-toggle="modal" data-target="#modalAddBanner">
                <div class="text-center p-4">
                    <i class="fas fa-plus-circle fa-3x text-success mb-2"></i>
                    <div class="h5 font-weight-bold text-dark">Novo Banner</div>
                    <small class="text-muted">Clique para adicionar</small>
                </div>
            </div>
        </div>

        @foreach($banners as $banner)
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 rounded-lg overflow-hidden">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <span class="font-weight-bold text-truncate" style="max-width: 150px;">{{ $banner->title ?? 'banner01' }}</span>
                    <div class="d-flex align-items-center">
                        <span class="badge {{ $banner->status ? 'badge-success' : 'badge-danger' }} mr-2 py-1 px-2">
                            {{ $banner->status ? 'Ativo' : 'Inativo' }}
                        </span>
                        <button class="btn btn-sm btn-outline-primary mr-1" onclick="editBanner({{ json_encode($banner) }})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Excluir este banner permanentemente?')" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="height: 150px; overflow: hidden;">
                    <img src="{{ asset($banner->image_path) }}" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="Banner">
                </div>
                <div class="card-footer bg-white py-2">
                    <div class="d-flex justify-content-between small text-muted">
                        <span><i class="fas fa-link"></i> {{ Str::limit($banner->link ?? '#', 20) }}</span>
                        <span><i class="fas fa-map-marker-alt"></i> 
                            {{ $banner->position == 'below_ticket' ? 'Abaixo do Bilhete' : ($banner->position == 'sidebar' ? 'Sidebar' : 'Home') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal Adicionar Banner -->
<div class="modal fade" id="modalAddBanner" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold">Adicionar Novo Banner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group text-center mb-4">
                        <div class="image-preview mx-auto rounded shadow-sm d-flex align-items-center justify-content-center bg-light" style="width: 100%; height: 140px; border: 1px dashed #ccc; overflow: hidden; position: relative;">
                            <span id="previewText">Pré-visualização da Imagem</span>
                            <img id="imgPreview" style="display:none; width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <input type="file" name="image_file" id="fileInput" class="d-none" accept="image/*" required onchange="previewImage(this)">
                        <button type="button" class="btn btn-sm btn-info mt-2" onclick="$('#fileInput').click()"><i class="fas fa-upload"></i> Escolher Arte do Banner</button>
                    </div>

                    <div class="alert alert-light border small text-muted py-2 mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Sugerimos 1000x300 e tamanho de até 500 KB
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Titulo do Banner</label>
                        <input type="text" name="title" class="form-control" placeholder="banner01" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Link</label>
                                <input type="text" name="link_url" class="form-control" placeholder="#teste">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Exibição</label>
                        <select name="display_to" class="form-control">
                            <option value="all">Todos</option>
                            <option value="logged">Logados</option>
                            <option value="visitors">Visitantes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Posição do Banner</label>
                        <select name="position" class="form-control">
                            <option value="home_main">Carrossel Principal (Home)</option>
                            <option value="sidebar">Sidebar Lateral (Geral)</option>
                            <option value="below_ticket">Abaixo do Bilhete</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-5 font-weight-bold">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Banner -->
<div class="modal fade" id="modalEditBanner" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-white border-bottom-0 pb-0 shadow-sm">
                <h5 class="modal-title font-weight-bold">Atualizar Banner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditBanner" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="editImageContainer" class="mb-3 rounded overflow-hidden shadow-sm" style="width: 100%; height: 140px; background: #eee; border: 1px solid #ddd;">
                        <img id="editImgPreview" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted"><i class="fas fa-info-circle mr-1"></i> Sugerimos 1000x300 e tamanho de até 500 KB</label>
                        <div class="custom-file">
                            <input type="file" name="image_file" class="custom-file-input" id="editFileInput" accept="image/*" onchange="previewEditImage(this)">
                            <label class="custom-file-label" id="editFileLabel" for="editFileInput">Escolher arquivo...</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Titulo do Banner</label>
                        <input type="text" name="title" id="editTitle" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Link</label>
                                <input type="text" name="link_url" id="editLink" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Status</label>
                                <select name="status" id="editStatus" class="form-control">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Exibição</label>
                        <select name="display_to" id="editDisplay" class="form-control">
                            <option value="all">Todos</option>
                            <option value="logged">Logados</option>
                            <option value="visitors">Visitantes</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Posição do Banner</label>
                        <select name="position" id="editPosition" class="form-control">
                            <option value="home_main">Carrossel Principal (Home)</option>
                            <option value="sidebar">Sidebar Lateral (Geral)</option>
                            <option value="below_ticket">Abaixo do Bilhete</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-danger px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card { transition: all 0.2s; border-radius: 8px !important; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .rounded-lg { border-radius: 8px !important; }
    .modal-content { border-radius: 12px !important; }
    .btn { border-radius: 6px !important; }
    .form-control { border-radius: 6px !important; background-color: #f8f9fa; border-color: #e9ecef; }
    .form-control:focus { background-color: #fff; box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.1); }
</style>
@stop

@section('js')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imgPreview').attr('src', e.target.result).fadeIn();
                $('#previewText').hide();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewEditImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#editImgPreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
            $('#editFileLabel').html(input.files[0].name);
        }
    }

    function editBanner(banner) {
        let route = "{{ route('admin.banners.update', ':id') }}";
        route = route.replace(':id', banner.id);
        
        $('#formEditBanner').attr('action', route);
        $('#editTitle').val(banner.title);
        $('#editLink').val(banner.link);
        $('#editStatus').val(banner.status);
        $('#editDisplay').val(banner.display_to || 'all');
        $('#editPosition').val(banner.position || 'home_main');
        
        // Caminho da imagem (ajuste se necessário para sua estrutura)
        let imagePath = banner.image_path;
        if (!imagePath.startsWith('/')) imagePath = '/' + imagePath;
        $('#editImgPreview').attr('src', imagePath);
        
        $('#modalEditBanner').modal('show');
    }
</script>
@stop
