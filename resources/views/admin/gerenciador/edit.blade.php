@extends('adminlte::page')

@section('title', 'Editar Banca | Gerenciador Master')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>⚙️ Editar Banca: {{ $site->name }}</h1>
        <a href="{{ route('admin.gerenciador.index') }}" class="btn btn-secondary shadow-sm"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
@endsection

@section('content')
<form action="{{ route('admin.gerenciador.update', $site->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <!-- Coluna Esquerda: Dados Principais e Financeiro -->
        <div class="col-md-6">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informações Principais</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nome da Banca</label>
                        <input type="text" name="name" class="form-control" value="{{ $site->name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Domínio da Banca</label>
                        <input type="text" name="domain" class="form-control" value="{{ $site->domain }}" required>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Fechamento e Fatura</h3>
                </div>
                <div class="card-body row">
                    <div class="form-group col-md-6">
                        <label>Valor Mensal (R$)</label>
                        <input type="number" step="0.01" name="due_value" class="form-control" value="{{ $site->due_value }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Dia de Vencimento</label>
                        <input type="number" name="billing_day" class="form-control" value="{{ $site->billing_day }}" min="1" max="31" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Identidade Visual Mestra -->
        <div class="col-md-6">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-paint-brush"></i> Identidade Visual Externa</h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">Gerência Profunda</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Tema Inteligente</label>
                            <select name="layout_theme" class="form-control">
                                <option value="dark-emerald" {{ $site->layout_theme == 'dark-emerald' ? 'selected' : '' }}>Emerald (Padrão)</option>
                                <option value="midnight-blue" {{ $site->layout_theme == 'midnight-blue' ? 'selected' : '' }}>Midnight Blue</option>
                                <option value="classic-gold" {{ $site->layout_theme == 'classic-gold' ? 'selected' : '' }}>Classic Gold</option>
                                <option value="ruby-wine" {{ $site->layout_theme == 'ruby-wine' ? 'selected' : '' }}>Ruby Wine</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between my-3">
                        <label class="fw-bold mb-0">Personalizar Cores Manualmente</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="active_custom_colors" name="active_custom_colors" {{ $site->active_custom_colors ? 'checked' : '' }}>
                            <label class="custom-control-label" for="active_custom_colors">Ativo</label>
                        </div>
                    </div>

                    <div id="customColorsSection" style="display: {{ $site->active_custom_colors ? 'block' : 'none' }};">
                        @php
                            $colors = $site->custom_colors ? json_decode($site->custom_colors, true) : [];
                            $elements = [
                                'sidebar_bg' => 'Painel Lateral',
                                'header_menu_bg' => 'Cabeçalho Superior',
                                'game_container' => 'Módulos de Jogo',
                                'odds_main_btn' => 'Botões de ODDs',
                                'site_bg' => 'Background',
                                'selected_btn_bg' => 'Odd Selecionada'
                            ];
                        @endphp
                        <div class="row g-2">
                        @foreach($elements as $key => $label)
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small>{{ $label }}</small>
                                    <input type="color" name="custom_colors[{{ $key }}]" value="{{ $colors[$key] ?? '#10b981' }}" class="form-control p-0 border-0" style="width: 35px; height: 35px;">
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>Logotipo do Cliente (PNG Recomendado)</label>
                        <div class="custom-file">
                            <input type="file" name="logo_file" class="custom-file-input" id="logoUpload" accept="image/*">
                            <label class="custom-file-label" for="logoUpload">Substituir Logomarca...</label>
                        </div>
                        @if($settings && isset($settings->logo_path))
                            <div class="mt-2 text-center bg-dark p-2 rounded">
                                <img src="{{ asset($settings->logo_path) }}" alt="Logo Atual" style="max-height: 50px;">
                            </div>
                        @endif
                    </div>

                    <div class="form-group mt-4">
                        <label>Favicon do Cliente (Ícone da Aba)</label>
                        <div class="custom-file">
                            <input type="file" name="favicon_file" class="custom-file-input" id="favUpload" accept="image/png, image/x-icon">
                            <label class="custom-file-label" for="favUpload">Substituir Ícone...</label>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group mt-4">
                        <label>Adicionar Banners Promocionais</label>
                        <div class="custom-file">
                            <input type="file" name="new_banners[]" class="custom-file-input" id="bannersUpload" accept="image/*" multiple>
                            <label class="custom-file-label" for="bannersUpload">Selecione uma ou mais imagens...</label>
                        </div>
                        <small class="text-muted">Estes banners serão injetados automaticamente no carrossel do cliente.</small>
                    </div>
                </div>
                <div class="card-footer text-right bg-light">
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">
                        <i class="fas fa-save"></i> SALVAR ALTERAÇÕES DA BANCA
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('custom_js')
<script>
    // Mostrar nome do arquivo após selecionar
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
    // Mostrar/ocultar paleta de cores customizadas
    $('#active_custom_colors').on('change', function() {
        if ($(this).is(':checked')) {
            $('#customColorsSection').slideDown();
        } else {
            $('#customColorsSection').slideUp();
        }
    });
</script>
@stop
