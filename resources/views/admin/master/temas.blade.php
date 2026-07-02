@extends('adminlte::page')

@section('title', 'Gerenciador de Temas')

@section('content_header')
    <div class="layout-header mb-4 text-white" style="border-radius: 16px; background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #3b82f6 100%); box-shadow: 0 8px 32px rgba(37,99,235,0.25); padding: 20px 28px;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="m-0 fw-bold" style="font-family: 'Inter', sans-serif; font-size: 1.6rem; letter-spacing: -0.5px;">
                    <i class="fas fa-layer-group me-2" style="opacity: 0.85;"></i> Gerenciador Master de Temas
                </h1>
                <p class="mb-0 mt-2" style="opacity: 0.85; font-size: 0.85rem;">Crie, edite e gerencie os temas visuais das banca de apostas.</p>
            </div>
            <a href="{{ route('admin.master.temas.create') }}" class="btn btn-light btn-sm rounded-pill fw-bold px-4" style="color: #1e3a5f; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <i class="fas fa-plus-circle me-2"></i> Criar Novo Tema
            </a>
        </div>
    </div>
@stop

@section('content')

    {{-- Toast de Sucesso --}}
    @if(session('success'))
    <div class="alert-success-toast" id="successToast">
        <div class="d-flex align-items-center">
            <div class="toast-icon-circle" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-check"></i>
            </div>
            <div class="ml-3">
                <strong style="font-size: 0.9rem;">Sucesso!</strong>
                <p class="mb-0" style="font-size: 0.8rem; opacity: 0.85;">{{ session('success') }}</p>
            </div>
            <button type="button" class="ml-3 close-toast" onclick="closeToast()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    @endif

    {{-- Toast de Erro --}}
    @if(session('error'))
    <div class="alert-success-toast" id="errorToast" style="border-left-color: #ef4444;">
        <div class="d-flex align-items-center">
            <div class="toast-icon-circle" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ml-3">
                <strong style="font-size: 0.9rem;">Erro!</strong>
                <p class="mb-0" style="font-size: 0.8rem; opacity: 0.85;">{{ session('error') }}</p>
            </div>
            <button type="button" class="ml-3 close-toast" onclick="closeErrorToast()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    @endif

    {{-- KPIs --}}
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="kpi-card" style="border-left: 4px solid #2563eb;">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb;">
                    <i class="fas fa-palette"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number" style="color: #2563eb;">{{ $themes->count() }}</h2>
                    <small class="kpi-label">Total de Temas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="kpi-card" style="border-left: 4px solid #10b981;">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #10b981;">
                    <i class="fas fa-store"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number" style="color: #10b981;">{{ $bancas->count() }}</h2>
                    <small class="kpi-label">Bancas Ativas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="kpi-card" style="border-left: 4px solid #3b82f6;">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number" style="color: #3b82f6;">{{ $themes->where('is_base', true)->count() }}</h2>
                    <small class="kpi-label">Temas Base</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="kpi-card" style="border-left: 4px solid #f59e0b;">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #f59e0b;">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div class="kpi-content">
                    <h2 class="kpi-number" style="color: #f59e0b;">{{ $themes->where('is_base', false)->count() }}</h2>
                    <small class="kpi-label">Customizados</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Temas --}}
    <div class="card border-0 layout-section-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.05);">
        <div class="card-header border-0" style="background: white; border-bottom: 1px solid #f0f0f0; padding: 18px 28px;">
            <div class="d-flex align-items-center">
                <div class="section-icon-circle" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #2563eb; margin-right: 20px;">
                    <i class="fas fa-palette"></i>
                </div>
                <div>
                    <h5 class="fw-bold" style="color: #1e293b; font-size: 1.05rem; margin-bottom: 4px;">Todos os Temas</h5>
                    <p class="text-muted mb-0" style="font-size: 0.8rem;">{{ $themes->count() }} tema(s) disponível(is)</p>
                </div>
            </div>
        </div>
        <div class="card-body" style="background: #fafbfd; padding: 28px;">
            <div class="row">
                @foreach($themes as $theme)
                @php $colors = is_array($theme->colors) ? $theme->colors : json_decode($theme->colors, true) ?? []; @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="theme-master-card h-100">
                        {{-- Preview visual --}}
                        <div class="theme-preview-bar" style="background: linear-gradient(135deg, {{ $colors['sidebar_color'] ?? '#222' }} 0%, {{ $colors['primary_color'] ?? '#333' }} 50%, {{ $colors['background_color'] ?? '#f4f4f4' }} 100%); height: 90px; display: flex; align-items: center; justify-content: center; position: relative; border-radius: 14px 14px 0 0;">
                            <div class="d-flex" style="gap: 10px;">
                                @foreach(['primary_color', 'sidebar_color', 'background_color', 'border_color'] as $key)
                                <div title="{{ $key }}" style="width: 32px; height: 32px; border-radius: 50%; background: {{ $colors[$key] ?? '#ccc' }}; border: 2px solid rgba(255,255,255,0.6); box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: transform 0.2s;" onmouseenter="this.style.transform='scale(1.15)'" onmouseleave="this.style.transform='scale(1)'"></div>
                                @endforeach
                            </div>
                            @if($theme->is_base)
                            <span class="badge badge-light position-absolute" style="top: 10px; right: 10px; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; background: rgba(255,255,255,0.9);">
                                <i class="fas fa-shield-alt me-1"></i> BASE
                            </span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-3" style="background: white;">
                            <h6 class="fw-bold mb-1" style="color: #1e293b; font-size: 0.95rem;">{{ $theme->name }}</h6>
                            <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">
                                <code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem;">{{ $theme->slug }}</code> · 
                                {{ count($colors) }} cores
                            </small>

                            {{-- Bancas usando este tema --}}
                            @php $usedBy = $bancas->where('layout_theme', $theme->slug); @endphp
                            @if($usedBy->count() > 0)
                            <div class="mb-2">
                                <small class="fw-bold" style="color: #10b981; font-size: 0.72rem;">
                                    <i class="fas fa-store me-1"></i> {{ $usedBy->count() }} banca(s)
                                </small>
                                <div class="mt-1 d-flex flex-wrap" style="gap: 4px;">
                                    @foreach($usedBy->take(3) as $b)
                                    <span class="badge" style="background: #d1fae5; color: #065f46; font-size: 0.65rem; font-weight: 600; padding: 3px 8px; border-radius: 20px;">{{ $b->name }}</span>
                                    @endforeach
                                    @if($usedBy->count() > 3)
                                    <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 0.65rem; font-weight: 600; padding: 3px 8px; border-radius: 20px;">+{{ $usedBy->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                            @else
                            <small class="text-muted" style="font-size: 0.72rem;"><i class="fas fa-info-circle me-1"></i> Nenhuma banca usando</small>
                            @endif
                        </div>

                        {{-- Ações --}}
                        <div class="theme-actions d-flex justify-content-between align-items-center" style="padding: 12px 16px; background: #fafbfd; border-top: 1px solid #f0f0f0; border-radius: 0 0 14px 14px;">
                            <div class="d-flex" style="gap: 6px;">
                                <a href="{{ route('admin.master.temas.edit', $theme->id) }}" class="btn btn-sm rounded-pill px-3 fw-bold" style="background: #2563eb; color: white; font-size: 0.75rem;">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                                <form action="{{ route('admin.master.temas.duplicate', $theme->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm rounded-pill px-3" style="border: 1.5px solid #e2e8f0; color: #64748b; background: white; font-size: 0.75rem;">
                                        <i class="fas fa-copy me-1"></i> Duplicar
                                    </button>
                                </form>
                            </div>
                            @if($usedBy->count() == 0)
                            <form action="{{ route('admin.master.temas.delete', $theme->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir o tema {{ $theme->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm rounded-pill px-3" style="border: 1.5px solid #fecaca; color: #ef4444; background: white; font-size: 0.75rem;">
                                    <i class="fas fa-trash me-1"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

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

    /* Header */
    .layout-header { animation: fadeInDown 0.5s ease; }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* KPI Cards */
    .kpi-card {
        background: white;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        animation: fadeInUp 0.5s ease;
    }
    .kpi-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .kpi-number {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0;
        line-height: 1;
    }
    .kpi-label {
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Section Cards */
    .layout-section-card {
        animation: fadeInUp 0.5s ease;
        transition: box-shadow 0.3s ease;
    }
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

    /* Theme Master Card */
    .theme-master-card {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: white;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .theme-master-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
        border-color: #93c5fd;
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
        .kpi-card { padding: 14px 16px; }
        .kpi-number { font-size: 1.2rem; }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Auto-fechar toasts
        if ($('#successToast').length) { setTimeout(function() { closeToast(); }, 4000); }
        if ($('#errorToast').length) { setTimeout(function() { closeErrorToast(); }, 4000); }
    });

    function closeToast() {
        var toast = $('#successToast');
        toast.addClass('hiding');
        setTimeout(function() { toast.remove(); }, 300);
    }
    function closeErrorToast() {
        var toast = $('#errorToast');
        toast.addClass('hiding');
        setTimeout(function() { toast.remove(); }, 300);
    }
</script>
@stop