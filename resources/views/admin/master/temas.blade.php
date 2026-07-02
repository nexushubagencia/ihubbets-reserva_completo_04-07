@extends('adminlte::page')

@section('title', 'Gerenciador de Temas')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between mb-3 px-2">
        <h1 class="m-0 text-dark fw-bold" style="font-family: 'Inter', sans-serif;">
            <i class="fas fa-layer-group me-2 text-primary"></i> Gerenciador Master de Temas
        </h1>
        <a href="{{ route('admin.master.temas.create') }}" class="btn btn-primary btn-lg rounded-pill shadow fw-bold px-4">
            <i class="fas fa-plus-circle me-2"></i> Criar Novo Tema
        </a>
    </div>
@stop

@section('content')

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-lg" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-lg" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    {{-- KPIs Rápidos --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm rounded-lg text-center p-3">
                <h2 class="fw-bold text-primary mb-0">{{ $themes->count() }}</h2>
                <small class="text-muted fw-bold uppercase">Total de Temas</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm rounded-lg text-center p-3">
                <h2 class="fw-bold text-success mb-0">{{ $bancas->count() }}</h2>
                <small class="text-muted fw-bold uppercase">Bancas Ativas</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm rounded-lg text-center p-3">
                <h2 class="fw-bold text-info mb-0">{{ $themes->where('is_base', true)->count() }}</h2>
                <small class="text-muted fw-bold uppercase">Temas Base</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm rounded-lg text-center p-3">
                <h2 class="fw-bold text-warning mb-0">{{ $themes->where('is_base', false)->count() }}</h2>
                <small class="text-muted fw-bold uppercase">Temas Customizados</small>
            </div>
        </div>
    </div>

    {{-- Lista de Temas --}}
    <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-palette me-2 text-primary"></i> Todos os Temas</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                @foreach($themes as $theme)
                @php $colors = is_array($theme->colors) ? $theme->colors : json_decode($theme->colors, true) ?? []; @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="theme-master-card shadow-sm rounded-lg overflow-hidden h-100 border">
                        {{-- Preview visual --}}
                        <div class="theme-preview-bar" style="background: linear-gradient(135deg, {{ $colors['sidebar_color'] ?? '#222' }} 0%, {{ $colors['primary_color'] ?? '#333' }} 50%, {{ $colors['background_color'] ?? '#f4f4f4' }} 100%); height: 80px; display: flex; align-items: center; justify-content: center; position: relative;">
                            <div class="d-flex gap-2" style="gap: 8px;">
                                @foreach(['primary_color', 'sidebar_color', 'background_color', 'border_color'] as $key)
                                <div title="{{ $key }}" style="width: 30px; height: 30px; border-radius: 50%; background: {{ $colors[$key] ?? '#ccc' }}; border: 2px solid rgba(255,255,255,0.6); box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                                @endforeach
                            </div>
                            @if($theme->is_base)
                            <span class="badge badge-light position-absolute" style="top: 8px; right: 8px; font-size: 10px;"><i class="fas fa-shield-alt"></i> BASE</span>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="p-3">
                            <h6 class="fw-bold mb-1">{{ $theme->name }}</h6>
                            <small class="text-muted d-block mb-2">
                                <code>{{ $theme->slug }}</code> · 
                                {{ count($colors) }} cores definidas
                            </small>

                            {{-- Bancas usando este tema --}}
                            @php $usedBy = $bancas->where('layout_theme', $theme->slug); @endphp
                            @if($usedBy->count() > 0)
                            <div class="mb-2">
                                <small class="text-success fw-bold"><i class="fas fa-store me-1"></i> Em uso por {{ $usedBy->count() }} banca(s):</small>
                                <div class="mt-1">
                                    @foreach($usedBy->take(3) as $b)
                                    <span class="badge badge-success mr-1">{{ $b->name }}</span>
                                    @endforeach
                                    @if($usedBy->count() > 3)
                                    <span class="badge badge-secondary">+{{ $usedBy->count() - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                            @else
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Nenhuma banca usando</small>
                            @endif
                        </div>

                        {{-- Ações --}}
                        <div class="border-top p-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.master.temas.edit', $theme->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                    <i class="fas fa-edit me-1"></i> Editar
                                </a>
                                <form action="{{ route('admin.master.temas.duplicate', $theme->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-info btn-sm rounded-pill px-3">
                                        <i class="fas fa-copy me-1"></i> Duplicar
                                    </button>
                                </form>
                            </div>
                            @if($usedBy->count() == 0)
                            <form action="{{ route('admin.master.temas.delete', $theme->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir o tema {{ $theme->name }}?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
    .fw-bold { font-weight: 700 !important; }
    .uppercase { text-transform: uppercase; }
    .theme-master-card { transition: 0.3s; }
    .theme-master-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important; }
    .rounded-lg { border-radius: 12px !important; }
</style>
@stop
