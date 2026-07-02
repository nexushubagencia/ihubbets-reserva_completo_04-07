@extends('adminlte::page')

@section('title', 'Personalizar Layout')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between mb-3 px-2">
        <h1 class="m-0 text-dark fw-bold" style="font-family: 'Inter', sans-serif;"><i class="fas fa-paint-brush me-2 text-primary"></i> Personalizar Layout</h1>
        <div class="badge badge-primary px-3 py-2 rounded-pill shadow-sm">Identidade & Tema</div>
    </div>
@stop

@section('content')
<form action="{{ route('admin.settings.layout.update') }}" method="POST" enctype="multipart/form-data" id="mainLayoutForm">
    @csrf
    
    {{-- 1. GALERIA DE TEMAS --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group me-2 text-primary"></i> Escolha o Tema da sua Banca</h5>
            <p class="text-muted small mb-0">Selecione um tema visual para o seu site. As cores são gerenciadas pelo administrador master.</p>
        </div>
        <div class="card-body bg-light p-4">
            <div class="row theme-grid">
                @if(isset($globalThemes) && count($globalThemes) > 0)
                    @foreach($globalThemes as $gt)
                    @php $gtColors = is_array($gt->colors) ? $gt->colors : json_decode($gt->colors, true); @endphp
                    <div class="col-6 col-md-3 col-lg-2 mb-3">
                        <label class="theme-option-wrapper mb-0 w-100">
                            <input type="radio" name="layout_theme" value="{{ $gt->slug }}" {{ $settings->layout_theme == $gt->slug ? 'checked' : '' }} class="d-none theme-radio-input">
                            <div class="theme-card {{ $settings->layout_theme == $gt->slug ? 'active' : '' }}">
                                <div class="theme-preview-img shadow-sm" style="background: linear-gradient(135deg, {{ $gtColors['sidebar_color'] ?? '#222' }} 0%, {{ $gtColors['primary_color'] ?? '#333' }} 50%, {{ $gtColors['background_color'] ?? '#f4f4f4' }} 100%);">
                                    <div class="theme-check"><i class="fas fa-check-circle"></i></div>
                                    @php $imgPath = 'dist/img/themes/' . $gt->slug . '.png'; @endphp
                                    @if(file_exists(public_path($imgPath)))
                                        <img src="{{ asset($imgPath) }}" alt="{{ $gt->name }}">
                                    @else
                                        <div class="text-center text-white">
                                            <i class="fas fa-palette fa-2x mb-1" style="opacity:0.8;"></i>
                                            <div class="x-small fw-bold">{{ strtoupper($gt->slug) }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="theme-name text-center py-2 fw-bold text-truncate px-1 small">
                                    {{ $gt->name }}
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                @else
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fas fa-palette fa-3x mb-3"></i>
                        <p>Nenhum tema disponível. Contate o administrador master.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. IDENTIDADE VISUAL --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-id-card me-2 text-primary"></i> Identidade Visual</h5>
            <p class="text-muted small mb-0">Configure o nome, logotipo e favicon da sua banca.</p>
        </div>
        <div class="card-body p-5">
            {{-- NOME --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="p-3 bg-light border rounded shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <label class="fw-bold text-muted mb-0 mr-1 small uppercase letter-spacing-1">Nome do Site</label>
                            <i class="fas fa-info-circle help-icon" data-toggle="tooltip" title="Nome curto exibido no topo do site e na barra do navegador. Ex: IHUB BETS"></i>
                        </div>
                        <input type="text" name="name" value="{{ $settings->name ?? '' }}" class="form-control form-control-lg border shadow-sm" style="border-radius: 10px; font-weight: 700; font-size: 1.1rem;" placeholder="Ex: IHUB BETS">
                        <small class="text-muted mt-1 d-block x-small">Aparece no header do site e no painel admin.</small>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="p-3 bg-light border rounded shadow-sm">
                        <div class="d-flex align-items-center mb-2">
                            <label class="fw-bold text-muted mb-0 mr-1 small uppercase letter-spacing-1">Nome Completo</label>
                            <i class="fas fa-info-circle help-icon" data-toggle="tooltip" title="Nome completo com slogan. Ex: IHUB BETS - APOSTAS ESPORTIVAS"></i>
                        </div>
                        <input type="text" name="complete_name" value="{{ $settings->complete_name ?? '' }}" class="form-control form-control-lg border shadow-sm" style="border-radius: 10px; font-weight: 700; font-size: 1.1rem;" placeholder="Ex: IHUB BETS - APOSTAS ESPORTIVAS">
                        <small class="text-muted mt-1 d-block x-small">Aparece no rodapé do site e no título da aba do Google.</small>
                    </div>
                </div>
            </div>

            {{-- LOGO E FAVICON --}}
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="p-3 bg-light border rounded shadow-sm text-center">
                        <label class="fw-bold text-muted d-block mb-3 small uppercase letter-spacing-1">Logotipo Principal</label>
                        <div class="logo-preview-box mb-3 mx-auto glass-effect shadow-sm">
                            <img src="/{{ $settings->logo_path ?? 'dist/img/logo.png' }}" style="max-height: 80px;" id="previewLogo">
                        </div>
                        <div class="custom-file custom-file-sm">
                            <input type="file" name="logo_file" class="custom-file-input" id="logoFile" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif">
                            <label class="custom-file-label rounded-pill" for="logoFile">Alterar Logo</label>
                        </div>
                        <small class="text-muted mt-2 d-block x-small">PNG, JPG, SVG ou WEBP — Até 5MB</small>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="p-3 bg-light border rounded shadow-sm text-center h-100">
                        <label class="fw-bold text-muted d-block mb-3 small uppercase letter-spacing-1">Favicon</label>
                        <div class="favicon-preview-box mb-3 mx-auto d-flex align-items-center justify-content-center glass-effect shadow-sm" style="height: 80px; width: 80px;">
                            <img src="/{{ $settings->favicon_path ?? 'favicon.ico' }}" style="max-height: 48px; max-width: 48px;" id="previewFavicon">
                        </div>
                        <div class="custom-file custom-file-sm">
                            <input type="file" name="favicon_file" class="custom-file-input" id="faviconFile" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/jpeg,image/gif,image/svg+xml,image/webp">
                            <label class="custom-file-label rounded-pill" for="faviconFile">Alterar Ícone</label>
                        </div>
                        <small class="text-muted mt-2 d-block x-small">ICO, PNG, JPG, SVG, WEBP — Até 5MB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTÃO SALVAR --}}
    <div class="save-actions shadow-lg p-3 bg-white border-top fixed-bottom d-flex align-items-center justify-content-center" style="z-index: 1050; gap: 15px;">
        <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-bold shadow rounded-pill">
            <i class="fas fa-save me-2"></i> PUBLICAR ALTERAÇÕES
        </button>
    </div>
</form>
@stop

@section('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; padding-bottom: 80px; }
    .fw-bold { font-weight: 700 !important; }
    .uppercase { text-transform: uppercase; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .x-small { font-size: 11px; }

    /* Theme Cards */
    .theme-card { border-radius: 15px; border: 2px solid transparent; background: #fff; transition: 0.3s; padding: 12px; position: relative; cursor: pointer; }
    .theme-card.active { border-color: #2563eb; box-shadow: 0 10px 25px rgba(37,99,235,0.15); }
    .theme-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .theme-preview-img { height: 90px; border-radius: 10px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f1f5f9; }
    .theme-preview-img img { width: 100%; height: 100%; object-fit: cover; }
    .theme-check { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(37,99,235,0.8); color: white; font-size: 24px; display: none; align-items: center; justify-content: center; z-index: 2; backdrop-filter: blur(2px); }
    .theme-card.active .theme-check { display: flex; }

    .glass-effect { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(8px); }
    .logo-preview-box { width: 100%; max-width: 280px; height: 110px; border: 2px dashed #cbd5e0; border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 10px; background: white; margin: 0 auto; }
    .favicon-preview-box { height: 60px; width: 60px; border: 2px dashed #cbd5e0; border-radius: 12px; background: white; margin: 0 auto; }
    .help-icon { color: #94a3b8; cursor: help; font-size: 0.8rem; }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        // Seleção de tema
        $('.theme-option-wrapper').on('click', function() {
            $('.theme-card').removeClass('active');
            $(this).find('.theme-card').addClass('active');
        });

        // Preview de arquivos
        $("#logoFile").change(function() { readURL(this, '#previewLogo'); });
        $("#faviconFile").change(function() { readURL(this, '#previewFavicon'); });

        function readURL(input, target) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { $(target).attr('src', e.target.result); }
                reader.readAsDataURL(input.files[0]);
            }
        }
    });
</script>
@stop
