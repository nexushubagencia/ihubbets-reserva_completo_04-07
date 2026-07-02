@extends('adminlte::page')

@section('title', 'Personalizar Layout')

@section('content_header')
    <div class="layout-header mb-4 text-white" style="border-radius: 16px; background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%); box-shadow: 0 8px 32px rgba(37,99,235,0.25); padding: 20px 28px;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="m-0 fw-bold" style="font-family: 'Inter', sans-serif; font-size: 1.6rem; letter-spacing: -0.5px;">
                    <i class="fas fa-paint-brush me-2" style="opacity: 0.85;"></i> Personalizar Layout
                </h1>
                <p class="mb-0 mt-2" style="opacity: 0.85; font-size: 0.85rem;">Configure o tema visual e a identidade da sua banca de apostas.</p>
            </div>
            <div class="d-none d-md-flex align-items-center gap-2">
                <span class="badge badge-light px-3 py-2 rounded-pill" style="font-size: 0.75rem; font-weight: 600;">
                    <i class="fas fa-eye me-1"></i> Identidade & Tema
                </span>
            </div>
        </div>
    </div>
@stop

@section('content')
@if(session('success'))
    <div class="alert-success-toast" id="successToast">
        <div class="d-flex align-items-center">
            <div class="toast-icon-circle">
                <i class="fas fa-check"></i>
            </div>
            <div class="ml-3">
                <strong style="font-size: 0.9rem;">Salvo com sucesso!</strong>
                <p class="mb-0" style="font-size: 0.8rem; opacity: 0.85;">{{ session('success') }}</p>
            </div>
            <button type="button" class="ml-3 close-toast" onclick="closeToast()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif
<form action="{{ route('admin.settings.layout.update') }}" method="POST" enctype="multipart/form-data" id="mainLayoutForm">
    @csrf
    
    {{-- 1. GALERIA DE TEMAS --}}
    <div class="card border-0 mb-4 layout-section-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.05);">
        <div class="card-header border-0" style="background: white; border-bottom: 1px solid #f0f0f0; padding: 18px 28px;">
            <div class="d-flex align-items-center">
                <div class="section-icon-circle" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed; margin-right: 20px;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h5 class="fw-bold" style="color: #1e293b; font-size: 1.05rem; margin-bottom: 4px;">Escolha o Tema da sua Banca</h5>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Selecione um tema visual. As cores são gerenciadas pelo administrador master.</p>
                </div>
            </div>
        </div>
        <div class="card-body" style="background: #fafbfd; padding: 24px 28px;">
            <div class="row theme-grid">
                @if(isset($globalThemes) && count($globalThemes) > 0)
                    @foreach($globalThemes as $gt)
                    @php $gtColors = is_array($gt->colors) ? $gt->colors : json_decode($gt->colors, true); @endphp
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                        <label class="theme-option-wrapper mb-0 w-100">
                            <input type="radio" name="layout_theme" value="{{ $gt->slug }}" {{ $settings->layout_theme == $gt->slug ? 'checked' : '' }} class="d-none theme-radio-input">
                            <div class="theme-card {{ $settings->layout_theme == $gt->slug ? 'active' : '' }}">
                                <div class="theme-preview-img" style="background: linear-gradient(135deg, {{ $gtColors['sidebar_color'] ?? '#222' }} 0%, {{ $gtColors['primary_color'] ?? '#333' }} 50%, {{ $gtColors['background_color'] ?? '#f4f4f4' }} 100%);">
                                    <div class="theme-check"><i class="fas fa-check"></i></div>
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
                    <div class="col-12 text-center py-5" style="color: #94a3b8;">
                        <i class="fas fa-palette fa-3x mb-3" style="opacity: 0.4;"></i>
                        <p class="mb-0">Nenhum tema disponível. Contate o administrador master.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. IDENTIDADE VISUAL --}}
    <div class="card border-0 mb-4 layout-section-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.05);">
        <div class="card-header border-0" style="background: white; border-bottom: 1px solid #f0f0f0; padding: 18px 28px;">
            <div class="d-flex align-items-center">
                <div class="section-icon-circle" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; margin-right: 20px;">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h5 class="fw-bold" style="color: #1e293b; font-size: 1.05rem; margin-bottom: 4px;">Identidade Visual</h5>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">Configure o nome, logotipo e favicon da sua banca.</p>
                </div>
            </div>
        </div>
        <div class="card-body" style="background: white; padding: 28px;">
            {{-- NOME --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="input-group-custom">
                        <div class="d-flex align-items-center mb-2">
                            <label class="fw-bold mb-0 small uppercase letter-spacing-1" style="color: #64748b; font-size: 0.7rem;">Nome do Site</label>
                            <i class="fas fa-info-circle help-icon ml-1" data-toggle="tooltip" title="Nome curto exibido no topo do site e na barra do navegador. Ex: IHUB BETS"></i>
                        </div>
                        <input type="text" name="name" value="{{ $settings->name ?? '' }}" class="form-control form-control-lg" style="border-radius: 10px; font-weight: 700; font-size: 1.05rem; border: 2px solid #e2e8f0; transition: all 0.3s ease; padding: 14px 18px;" placeholder="Ex: IHUB BETS" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        <small class="text-muted mt-1 d-block" style="font-size: 0.7rem;">Aparece no header do site e no painel admin.</small>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="input-group-custom">
                        <div class="d-flex align-items-center mb-2">
                            <label class="fw-bold mb-0 small uppercase letter-spacing-1" style="color: #64748b; font-size: 0.7rem;">Nome Completo</label>
                            <i class="fas fa-info-circle help-icon ml-1" data-toggle="tooltip" title="Nome completo com slogan. Ex: IHUB BETS - APOSTAS ESPORTIVAS"></i>
                        </div>
                        <input type="text" name="complete_name" value="{{ $settings->complete_name ?? '' }}" class="form-control form-control-lg" style="border-radius: 10px; font-weight: 700; font-size: 1.05rem; border: 2px solid #e2e8f0; transition: all 0.3s ease; padding: 14px 18px;" placeholder="Ex: IHUB BETS - APOSTAS ESPORTIVAS" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        <small class="text-muted mt-1 d-block" style="font-size: 0.7rem;">Aparece no rodapé do site e no título da aba do Google.</small>
                    </div>
                </div>
            </div>

            {{-- LOGO E FAVICON --}}
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="upload-zone text-center" style="border: 2px dashed #e2e8f0; border-radius: 16px; background: #fafbfd; transition: all 0.3s ease; padding: 28px 20px;" onmouseenter="this.style.borderColor='#3b82f6'; this.style.background='#f0f7ff'" onmouseleave="this.style.borderColor='#e2e8f0'; this.style.background='#fafbfd'">
                        <label class="fw-bold d-block mb-3 small uppercase letter-spacing-1" style="color: #64748b; font-size: 0.7rem;">Logotipo Principal</label>
                        <div class="logo-preview-box mb-3 mx-auto" style="border: 2px dashed #cbd5e0; border-radius: 14px; display: flex; align-items: center; justify-content: center; padding: 16px; background: white; width: 100%; max-width: 260px; height: 100px; transition: all 0.3s ease;">
                            <img src="/{{ $settings->logo_path ?? 'dist/img/logo.png' }}" style="max-height: 75px; transition: transform 0.3s ease;" id="previewLogo" onmouseenter="this.style.transform='scale(1.05)'" onmouseleave="this.style.transform='scale(1)'">
                        </div>
                        <div class="custom-file custom-file-sm mt-2">
                            <input type="file" name="logo_file" class="custom-file-input" id="logoFile" accept="image/png,image/jpeg,image/svg+xml,image/webp,image/gif">
                            <label class="custom-file-label rounded-pill" for="logoFile" style="border: 2px solid #e2e8f0; font-size: 0.8rem; padding: 8px 20px; cursor: pointer; transition: all 0.3s ease;">
                                <i class="fas fa-cloud-upload-alt me-1"></i> Alterar Logo
                            </label>
                        </div>
                        <small class="text-muted mt-2 d-block" style="font-size: 0.68rem;">PNG, JPG, SVG ou WEBP — Até 5MB</small>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="upload-zone text-center h-100 d-flex flex-column justify-content-center" style="border: 2px dashed #e2e8f0; border-radius: 16px; background: #fafbfd; transition: all 0.3s ease; padding: 28px 20px;" onmouseenter="this.style.borderColor='#3b82f6'; this.style.background='#f0f7ff'" onmouseleave="this.style.borderColor='#e2e8f0'; this.style.background='#fafbfd'">
                        <label class="fw-bold d-block mb-3 small uppercase letter-spacing-1" style="color: #64748b; font-size: 0.7rem;">Favicon</label>
                        <div class="favicon-preview-box mb-3 mx-auto d-flex align-items-center justify-content-center" style="height: 72px; width: 72px; border: 2px dashed #cbd5e0; border-radius: 14px; background: white; transition: all 0.3s ease;">
                            <img src="/{{ $settings->favicon_path ?? 'favicon.ico' }}" style="max-height: 44px; max-width: 44px; transition: transform 0.3s ease;" id="previewFavicon" onmouseenter="this.style.transform='scale(1.1)'" onmouseleave="this.style.transform='scale(1)'">
                        </div>
                        <div class="custom-file custom-file-sm mt-2">
                            <input type="file" name="favicon_file" class="custom-file-input" id="faviconFile" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/jpeg,image/gif,image/svg+xml,image/webp">
                            <label class="custom-file-label rounded-pill" for="faviconFile" style="border: 2px solid #e2e8f0; font-size: 0.8rem; padding: 8px 20px; cursor: pointer; transition: all 0.3s ease;">
                                <i class="fas fa-cloud-upload-alt me-1"></i> Alterar Ícone
                            </label>
                        </div>
                        <small class="text-muted mt-2 d-block" style="font-size: 0.68rem;">ICO, PNG, JPG, SVG, WEBP — Até 5MB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTÃO SALVAR --}}
    <div class="d-flex justify-content-center mt-4 mb-3">
        <button type="submit" class="btn-save-publish" id="btnSave">
            <i class="fas fa-save me-2"></i> PUBLICAR ALTERAÇÕES
        </button>
    </div>
</form>
@stop

@section('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #f1f5f9; 
        padding-bottom: 30px; 
    }
    
    .fw-bold { font-weight: 700 !important; }
    .uppercase { text-transform: uppercase; }
    .letter-spacing-1 { letter-spacing: 1.5px; }

    /* Header */
    .layout-header {
        animation: fadeInDown 0.5s ease;
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Section Cards */
    .layout-section-card {
        animation: fadeInUp 0.5s ease;
        transition: box-shadow 0.3s ease;
    }
    .layout-section-card:nth-child(2) { animation-delay: 0.1s; }
    .layout-section-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.08) !important;
    }

    /* Section Icon Circle */
    .section-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    /* Theme Cards */
    .theme-card { 
        border-radius: 14px; 
        border: 2px solid #e2e8f0; 
        background: #fff; 
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); 
        padding: 12px; 
        position: relative; 
        cursor: pointer; 
    }
    .theme-card.active { 
        border-color: #3b82f6; 
        box-shadow: 0 8px 25px rgba(59,130,246,0.2); 
        transform: translateY(-2px);
    }
    .theme-card:hover { 
        transform: translateY(-4px); 
        box-shadow: 0 12px 28px rgba(0,0,0,0.12); 
        border-color: #93c5fd;
    }
    .theme-card.active:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(59,130,246,0.25);
    }
    
    .theme-preview-img { 
        height: 90px; 
        border-radius: 10px; 
        position: relative; 
        overflow: hidden; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        background: #f1f5f9; 
    }
    .theme-preview-img img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        transition: transform 0.4s ease;
    }
    .theme-card:hover .theme-preview-img img {
        transform: scale(1.05);
    }
    
    .theme-check { 
        position: absolute; 
        top: 0; left: 0; 
        width: 100%; height: 100%; 
        background: rgba(59,130,246,0.85); 
        color: white; 
        font-size: 22px; 
        display: none; 
        align-items: center; 
        justify-content: center; 
        z-index: 2; 
        backdrop-filter: blur(3px);
        animation: checkPop 0.3s ease;
    }
    .theme-card.active .theme-check { display: flex; }
    
    @keyframes checkPop {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .theme-name { 
        font-size: 0.78rem; 
        color: #475569;
        transition: color 0.3s ease;
    }
    .theme-card.active .theme-name { color: #2563eb; }

    /* Upload Zone */
    .upload-zone {
        transition: all 0.3s ease;
    }

    /* Save Button */
    .btn-save-publish {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border: none;
        padding: 14px 48px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
        position: relative;
        overflow: hidden;
    }
    .btn-save-publish::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.5s ease;
    }
    .btn-save-publish:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37,99,235,0.4);
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
    }
    .btn-save-publish:hover::before {
        left: 100%;
    }
    .btn-save-publish:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
    }

    /* Input focus */
    .form-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.12) !important;
    }

    /* Help icon */
    .help-icon { 
        color: #a0aec0; 
        cursor: help; 
        font-size: 0.7rem; 
        transition: color 0.2s ease;
    }
    .help-icon:hover { color: #3b82f6; }

    /* Custom file label */
    .custom-file-label {
        border: 2px solid #e2e8f0;
        font-size: 0.8rem;
        padding: 8px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .custom-file-label:hover {
        border-color: #3b82f6;
        background: #f0f7ff;
        color: #2563eb;
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Success Toast */
    .alert-success-toast {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
        background: white;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        border-left: 4px solid #10b981;
        min-width: 320px;
        max-width: 420px;
        animation: slideInRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .alert-success-toast.hiding {
        animation: slideOutRight 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    .toast-icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .close-toast {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
        font-size: 0.85rem;
        transition: color 0.2s ease;
    }
    .close-toast:hover { color: #475569; }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOutRight {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .layout-header h1 { font-size: 1.2rem !important; }
        .btn-save-publish { padding: 14px 36px; font-size: 0.82rem; }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        // Auto-fechar toast de sucesso após 4 segundos
        if ($('#successToast').length) {
            setTimeout(function() { closeToast(); }, 4000);
        }

        // Seleção de tema com animação
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
                reader.onload = function(e) { 
                    $(target).attr('src', e.target.result);
                    $(target).css('transform', 'scale(1.05)');
                    setTimeout(function(){ $(target).css('transform', 'scale(1)'); }, 300);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Animação no botão salvar
        $('#mainLayoutForm').on('submit', function() {
            var btn = $('#btnSave');
            btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Salvando...');
            btn.prop('disabled', true).css('opacity', '0.8');
        });
    });

    function closeToast() {
        var toast = $('#successToast');
        toast.addClass('hiding');
        setTimeout(function() { toast.remove(); }, 300);
    }
</script>
@stop