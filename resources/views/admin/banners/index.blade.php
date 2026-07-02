@extends('adminlte::page')

@section('title', 'Gerenciar Banners | IHUB BETS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-images"></i> Gerenciar Banners</h1>
        <button class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalAddBanner">
            <i class="fas fa-plus mr-1"></i> Novo Banner
        </button>
    </div>
@endsection

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

@php
    $homeMain = $banners->where('position', 'home_main');
    $belowTicket = $banners->where('position', 'below_ticket');
    $sidebar = $banners->where('position', 'sidebar');
@endphp

<div class="container-fluid">

    {{-- ═══════════════ BANNERS HOME (Carrossel Principal) ═══════════════ --}}
    <div class="card card-outline card-primary mb-4 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0">
                <i class="fas fa-home mr-2"></i> Banners Home
                <small class="font-weight-normal ml-2">— Carrossel Principal</small>
            </h5>
            <span class="badge badge-light">{{ $homeMain->count() }}</span>
        </div>
        <div class="card-body p-3">
            @if($homeMain->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fas fa-image fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Nenhum banner nesta posição.</p>
                    <small>Clique em "Novo Banner" e selecione "Carrossel Principal".</small>
                </div>
            @else
                <div class="row">
                    @foreach($homeMain as $banner)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="card h-100 shadow-sm border-0 rounded-lg overflow-hidden banner-card">
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="height: 130px; overflow: hidden;">
                                <img src="{{ asset($banner->image_path) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $banner->title }}">
                            </div>
                            <div class="card-footer bg-white py-2 px-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-truncate" style="max-width: 120px;">
                                        <strong class="small">{{ $banner->title ?? 'Sem título' }}</strong>
                                        <br><span class="text-muted" style="font-size: 11px;"><i class="fas fa-link"></i> {{ Str::limit($banner->link ?? '#', 18) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $banner->status ? 'badge-success' : 'badge-secondary' }} mr-2" style="font-size: 10px;">{{ $banner->status ? 'Ativo' : 'Inativo' }}</span>
                                        <button class="btn btn-sm btn-outline-primary py-0 px-1 mr-1" onclick='editBanner(@json($banner))' title="Editar"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Excluir este banner?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════ BANNERS ABAIXO DO BILHETE ═══════════════ --}}
    <div class="card card-outline card-warning mb-4 shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0">
                <i class="fas fa-receipt mr-2"></i> Banners Abaixo do Bilhete
            </h5>
            <span class="badge badge-dark">{{ $belowTicket->count() }}</span>
        </div>
        <div class="card-body p-3">
            @if($belowTicket->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fas fa-image fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Nenhum banner nesta posição.</p>
                    <small>Clique em "Novo Banner" e selecione "Abaixo do Bilhete".</small>
                </div>
            @else
                <div class="row">
                    @foreach($belowTicket as $banner)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="card h-100 shadow-sm border-0 rounded-lg overflow-hidden banner-card">
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="height: 130px; overflow: hidden;">
                                <img src="{{ asset($banner->image_path) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $banner->title }}">
                            </div>
                            <div class="card-footer bg-white py-2 px-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-truncate" style="max-width: 120px;">
                                        <strong class="small">{{ $banner->title ?? 'Sem título' }}</strong>
                                        <br><span class="text-muted" style="font-size: 11px;"><i class="fas fa-link"></i> {{ Str::limit($banner->link ?? '#', 18) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $banner->status ? 'badge-success' : 'badge-secondary' }} mr-2" style="font-size: 10px;">{{ $banner->status ? 'Ativo' : 'Inativo' }}</span>
                                        <button class="btn btn-sm btn-outline-primary py-0 px-1 mr-1" onclick='editBanner(@json($banner))' title="Editar"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Excluir este banner?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════ SIDEBAR LATERAL ═══════════════ --}}
    <div class="card card-outline card-info mb-4 shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0">
                <i class="fas fa-columns mr-2"></i> Sidebar Lateral
            </h5>
            <span class="badge badge-light">{{ $sidebar->count() }}</span>
        </div>
        <div class="card-body p-3">
            @if($sidebar->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fas fa-image fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Nenhum banner nesta posição.</p>
                    <small>Clique em "Novo Banner" e selecione "Sidebar Lateral".</small>
                </div>
            @else
                <div class="row">
                    @foreach($sidebar as $banner)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="card h-100 shadow-sm border-0 rounded-lg overflow-hidden banner-card">
                            <div class="card-body p-0 bg-dark d-flex align-items-center justify-content-center" style="height: 130px; overflow: hidden;">
                                <img src="{{ asset($banner->image_path) }}" class="w-100 h-100" style="object-fit: cover;" alt="{{ $banner->title }}">
                            </div>
                            <div class="card-footer bg-white py-2 px-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-truncate" style="max-width: 120px;">
                                        <strong class="small">{{ $banner->title ?? 'Sem título' }}</strong>
                                        <br><span class="text-muted" style="font-size: 11px;"><i class="fas fa-link"></i> {{ Str::limit($banner->link ?? '#', 18) }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $banner->status ? 'badge-success' : 'badge-secondary' }} mr-2" style="font-size: 10px;">{{ $banner->status ? 'Ativo' : 'Inativo' }}</span>
                                        <button class="btn btn-sm btn-outline-primary py-0 px-1 mr-1" onclick='editBanner(@json($banner))' title="Editar"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Excluir este banner?')" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal Adicionar Banner -->
<div class="modal fade" id="modalAddBanner" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Novo Banner</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group text-center mb-4">
                        <div class="image-preview mx-auto rounded shadow-sm d-flex align-items-center justify-content-center bg-light" style="width: 100%; height: 140px; border: 1px dashed #ccc; overflow: hidden;">
                            <span id="previewText"><i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1"></i><br>Pré-visualização</span>
                            <img id="imgPreview" style="display:none; width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <input type="file" name="image_file" id="fileInput" class="d-none" accept="image/*" required onchange="previewImage(this)">
                        <button type="button" class="btn btn-sm btn-info mt-2" onclick="$('#fileInput').click()"><i class="fas fa-upload"></i> Escolher Arte</button>
                    </div>

                    <div class="alert alert-light border small text-muted py-2 mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Tamanho sugerido: 1000x300px, até 500 KB
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Título do Banner</label>
                        <input type="text" name="title" class="form-control" placeholder="Ex: banner01" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Link</label>
                                <input type="text" name="link_url" class="form-control" placeholder="https://...">
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Exibição</label>
                                <select name="display_to" class="form-control">
                                    <option value="all">Todos</option>
                                    <option value="logged">Logados</option>
                                    <option value="visitors">Visitantes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Posição do Banner</label>
                                <select name="position" class="form-control">
                                    <option value="home_main">🏠 Carrossel Principal (Home)</option>
                                    <option value="below_ticket">📋 Abaixo do Bilhete</option>
                                    <option value="sidebar">📌 Sidebar Lateral</option>
                                </select>
                            </div>
                        </div>
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
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Editar Banner</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="formEditBanner" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="editImageContainer" class="mb-3 rounded overflow-hidden shadow-sm" style="width: 100%; height: 140px; background: #eee; border: 1px solid #ddd;">
                        <img id="editImgPreview" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    
                    <div class="form-group">
                        <label class="small font-weight-bold text-muted"><i class="fas fa-info-circle mr-1"></i> Tamanho sugerido: 1000x300px</label>
                        <div class="custom-file">
                            <input type="file" name="image_file" class="custom-file-input" id="editFileInput" accept="image/*" onchange="previewEditImage(this)">
                            <label class="custom-file-label" id="editFileLabel" for="editFileInput">Escolher arquivo...</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="small font-weight-bold">Título do Banner</label>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Exibição</label>
                                <select name="display_to" id="editDisplay" class="form-control">
                                    <option value="all">Todos</option>
                                    <option value="logged">Logados</option>
                                    <option value="visitors">Visitantes</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Posição do Banner</label>
                                <select name="position" id="editPosition" class="form-control">
                                    <option value="home_main">🏠 Carrossel Principal (Home)</option>
                                    <option value="below_ticket">📋 Abaixo do Bilhete</option>
                                    <option value="sidebar">📌 Sidebar Lateral</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-danger px-4" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .banner-card { transition: all 0.2s; border-radius: 8px !important; }
    .banner-card:hover { transform: translateY(-3px); box-shadow: 0 8px 16px rgba(0,0,0,0.12) !important; }
    .rounded-lg { border-radius: 8px !important; }
    .modal-content { border-radius: 12px !important; }
    .card-header { border-bottom: none !important; }
    .card-outline.card-primary { border-top: 3px solid #007bff !important; }
    .card-outline.card-warning { border-top: 3px solid #ffc107 !important; }
    .card-outline.card-info { border-top: 3px solid #17a2b8 !important; }
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
        
        let imagePath = banner.image_path;
        if (!imagePath.startsWith('/')) imagePath = '/' + imagePath;
        $('#editImgPreview').attr('src', imagePath);
        
        $('#modalEditBanner').modal('show');
    }
</script>
@stop
