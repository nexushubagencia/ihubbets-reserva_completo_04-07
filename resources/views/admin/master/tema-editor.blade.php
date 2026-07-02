@extends('adminlte::page')

@section('title', $theme ? 'Editar Tema: ' . $theme->name : 'Criar Novo Tema')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between mb-3 px-2">
        <div>
            <a href="{{ route('admin.master.temas') }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-2">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            <h1 class="m-0 text-dark fw-bold" style="font-family: 'Inter', sans-serif;">
                <i class="fas fa-palette me-2 text-primary"></i> 
                {{ $theme ? 'Editar: ' . $theme->name : 'Criar Novo Tema' }}
            </h1>
        </div>
        <div class="badge badge-primary px-3 py-2 rounded-pill shadow-sm">
            {{ $theme ? count($theme->colors ?? []) . ' cores' : 'Novo' }}
        </div>
    </div>
@stop

@section('content')
<form 
    action="{{ $theme ? route('admin.master.temas.update', $theme->id) : route('admin.master.temas.store') }}" 
    method="POST" 
    id="themeEditorForm"
>
    @csrf
    @if($theme) @method('PUT') @endif

    {{-- NOME DO TEMA --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-gradient-primary text-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-tag me-2"></i> Identificação do Tema</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted small uppercase letter-spacing-1 mb-2">Nome do Tema</label>
                    <input type="text" name="name" value="{{ $theme->name ?? '' }}" 
                        class="form-control form-control-lg border shadow-sm" 
                        style="border-radius: 10px; font-weight: 700; font-size: 1.1rem;" 
                        placeholder="Ex: Neon Cyber Purple" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted small uppercase letter-spacing-1 mb-2">Slug (auto)</label>
                    <input type="text" value="{{ $theme->slug ?? 'gerado-automaticamente' }}" 
                        class="form-control form-control-lg border shadow-sm" 
                        style="border-radius: 10px;" disabled>
                </div>
            </div>
        </div>
    </div>

    {{-- SEÇÕES DE CORES --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="row no-gutters">
                {{-- SIDEBAR DE NAVEGAÇÃO --}}
                <div class="col-lg-3 border-right bg-light p-0">
                    <div class="nav flex-column nav-pills theme-editor-nav" id="sectionNav" role="tablist" aria-orientation="vertical">
                        @php $first = true; @endphp
                        @foreach($sections as $key => $section)
                        <a class="nav-link {{ $first ? 'active' : '' }} p-4 border-bottom rounded-0" 
                           id="nav-{{ $key }}-tab" data-toggle="pill" href="#nav-{{ $key }}" role="tab">
                            <i class="{{ $section['icon'] }} me-3" style="width: 20px; text-align: center;"></i> 
                            {{ $section['title'] }}
                            <span class="badge badge-light float-right mt-1">{{ count($section['fields']) }}</span>
                        </a>
                        @php $first = false; @endphp
                        @endforeach
                    </div>
                </div>

                {{-- CONTEÚDO DO EDITOR --}}
                <div class="col-lg-9 p-0 bg-white">
                    <div class="tab-content h-100" id="sectionContent">
                        @php $first = true; @endphp
                        @foreach($sections as $key => $section)
                        <div class="tab-pane fade {{ $first ? 'show active' : '' }} p-5" id="nav-{{ $key }}" role="tabpanel">
                            <div class="mb-4">
                                <h4 class="fw-bold">{{ $section['title'] }}</h4>
                                <p class="text-muted">{{ $section['description'] }}</p>
                            </div>
                            <div class="row">
                                @foreach($section['fields'] as $field)
                                <div class="col-md-6 mb-3">
                                    <div class="color-item-box">
                                        <div class="color-info">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="fw-bold small">{{ $field['label'] }}</span>
                                                <i class="fas fa-info-circle help-icon ml-1" data-toggle="tooltip" title="{{ $field['hint'] }}"></i>
                                            </div>
                                            <span class="color-code text-muted x-small" data-field="{{ $field['field'] }}">{{ $field['value'] }}</span>
                                        </div>
                                        <div class="color-preview-pill" style="background-color: {{ $field['value'] }};">
                                            <input type="color" name="{{ $field['field'] }}" value="{{ $field['value'] }}" 
                                                class="color-input-hidden" data-field="{{ $field['field'] }}">
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @php $first = false; @endphp
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PREVIEW RÁPIDO --}}
    <div class="card shadow-sm border-0 mb-5" style="border-radius: 16px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-eye me-2 text-primary"></i> Preview Rápido</h5>
        </div>
        <div class="card-body p-4" id="quickPreview">
            <div class="d-flex flex-wrap" style="gap: 12px;">
                {{-- Gerado dinamicamente pelo JS --}}
            </div>
        </div>
    </div>

    {{-- BOTÃO SALVAR FIXO --}}
    <div class="save-actions shadow-lg p-3 bg-white border-top fixed-bottom d-flex align-items-center justify-content-center" style="z-index: 1050; gap: 15px;">
        <a href="{{ route('admin.master.temas') }}" class="btn btn-outline-secondary btn-lg px-4 py-2 fw-bold rounded-pill">
            <i class="fas fa-times me-2"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-bold shadow rounded-pill">
            <i class="fas fa-save me-2"></i> {{ $theme ? 'SALVAR ALTERAÇÕES' : 'CRIAR TEMA' }}
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

    /* Nav do Editor */
    .theme-editor-nav .nav-link { 
        color: #475569; font-weight: 600; 
        border-bottom: 1px solid #f1f5f9 !important;
        transition: 0.3s; font-size: 0.9rem;
    }
    .theme-editor-nav .nav-link:hover { background: #f1f5f9; color: #2563eb; }
    .theme-editor-nav .nav-link.active { 
        background: white !important; color: #2563eb !important;
        box-shadow: -4px 0 0 #2563eb inset;
    }

    /* Color Item */
    .color-item-box { 
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; 
        padding: 12px 16px; display: flex; align-items: center; 
        justify-content: space-between; transition: 0.2s; 
    }
    .color-item-box:hover { border-color: #94a3b8; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .color-preview-pill { 
        width: 42px; height: 42px; border-radius: 10px; 
        border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.15); 
        position: relative; overflow: hidden; cursor: pointer; flex-shrink: 0; 
    }
    .color-input-hidden { 
        position: absolute; top: -10px; left: -10px; 
        width: 64px; height: 64px; cursor: pointer; border: none; opacity: 0; 
    }
    .color-info { flex-grow: 1; padding-right: 10px; }
    .help-icon { color: #94a3b8; cursor: help; font-size: 0.75rem; }

    /* Preview */
    .preview-swatch {
        width: 60px; height: 60px; border-radius: 10px; 
        border: 2px solid #e2e8f0; position: relative;
        display: flex; align-items: flex-end; justify-content: center;
        padding-bottom: 2px; font-size: 9px; color: rgba(0,0,0,0.5);
        font-weight: 700; text-shadow: 0 1px 1px rgba(255,255,255,0.8);
        transition: 0.3s;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        // Atualizar cor em tempo real
        $('.color-input-hidden').on('input', function() {
            const color = $(this).val();
            const field = $(this).data('field');
            
            // Atualizar preview pill
            $(this).parent().css('background-color', color);
            
            // Atualizar código hex
            $(`.color-code[data-field="${field}"]`).text(color);

            // Atualizar preview geral
            updateQuickPreview();
        });

        // Preview rápido
        function updateQuickPreview() {
            let html = '';
            $('.color-input-hidden').each(function() {
                const name = $(this).attr('name');
                const color = $(this).val();
                const label = name.replace(/_color$/, '').replace(/_/g, ' ');
                html += `<div class="preview-swatch" style="background-color: ${color};" title="${name}: ${color}">${label.substring(0, 8)}</div>`;
            });
            $('#quickPreview .d-flex').html(html);
        }
        updateQuickPreview();
    });
</script>
@stop
