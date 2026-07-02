@extends('adminlte::page')

@section('title', 'Meu Perfil Premium')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="text-dark font-weight-bold" style="font-family: 'Outfit', sans-serif; font-size: 2rem;">
            <i class="fas fa-id-card-alt text-primary mr-2"></i> Perfil da Conta
        </h1>
        <nav aria-label="breadcrumb">
            
        </nav>
    </div>
@stop

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        --dark-bg: #1a1c23;
    }

    .profile-card {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .dark-mode .profile-card {
        background: rgba(30, 32, 40, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
    }

    .form-control {
        border-radius: 12px;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.1);
        border-color: #4e73df;
    }

    .btn-save {
        background: var(--primary-gradient);
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 10px 20px rgba(78, 115, 223, 0.2);
        transition: all 0.3s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(78, 115, 223, 0.3);
    }

    .theme-toggle-card {
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .theme-toggle-card.active {
        border-color: #4e73df;
        background: rgba(78, 115, 223, 0.05);
    }

    .avatar-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto 20px;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .badge-role {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #4e73df;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
        border: 2px solid #fff;
    }
</style>

<div class="container-fluid pb-5">
    <form action="{{ route('admin.profile.update') }}" method="POST" id="profileForm">
        @csrf

        <input type="hidden" name="theme_preference" id="theme_preference" value="{{ $user->theme_preference }}">

        <div class="row">
            <!-- Coluna Esquerda: Dados Principais -->
            <div class="col-lg-8">
                <div class="profile-card p-4 mb-4">
                    <h4 class="mb-4 font-weight-bold"><i class="fas fa-id-badge text-primary mr-2"></i> Dados Pessoais</h4>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" style="border-radius: 12px;">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small font-weight-bold">NOME COMPLETO</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small font-weight-bold">E-MAIL PRINCIPAL</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <hr class="opacity-50">
                            <h4 class="mb-4 mt-2 font-weight-bold text-danger"><i class="fas fa-shield-alt mr-2"></i> Alterar Senha</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small font-weight-bold">NOVA SENHA</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small font-weight-bold">CONFIRMAR SENHA</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="bg-light p-3 rounded" style="border-left: 4px solid #f6c23e;">
                                <small class="text-dark"><i class="fas fa-info-circle mr-1"></i> Deixe os campos de senha em branco caso não deseje alterar sua senha atual.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão de Salvar (Mobile Sticky ou Desktop Normal) -->
                <button type="submit" class="btn btn-save btn-block text-white">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> ATUALIZAR PERFIL AGORA
                </button>
            </div>

            <!-- Coluna Direita: Estilo e Info -->
            <div class="col-lg-4">
                <!-- Card de Tema -->
                <div class="profile-card p-4 mb-4">
                    <h4 class="mb-4 font-weight-bold"><i class="fas fa-palette text-primary mr-2"></i> Estilo do Painel</h4>
                    <p class="text-muted small">Escolha o tema que melhor combina com seu ambiente de trabalho.</p>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="theme-toggle-card p-3 text-center profile-card {{ $user->theme_preference == 'light' ? 'active' : '' }}" onclick="setTheme('light')">
                                <i class="fas fa-sun fa-2x text-warning mb-2"></i>
                                <span class="d-block font-weight-bold small">LIGHT</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="theme-toggle-card p-3 text-center profile-card {{ $user->theme_preference == 'dark' ? 'active' : '' }}" onclick="setTheme('dark')" style="background: #2d3436;">
                                <i class="fas fa-moon fa-2x text-white mb-2"></i>
                                <span class="d-block font-weight-bold small text-white">DARK</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de Info Rápida -->
                <div class="profile-card p-4 text-center">
                    <div class="avatar-wrapper">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4e73df&color=fff&size=128" alt="User Avatar" class="avatar-img">
                        <span class="badge-role">{{ $user->nivel ?? 'Admin' }}</span>
                    </div>
                    <h5 class="font-weight-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>
                    
                    <div class="bg-light p-3 rounded text-left">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Última Atualização:</span>
                            <span class="font-weight-bold small">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">ID do Sistema:</span>
                            <span class="font-weight-bold small text-primary">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@stop

@section('js')
<script>
    function setTheme(theme) {
        $('#theme_preference').val(theme);
        $('.theme-toggle-card').removeClass('active');
        event.currentTarget.classList.add('active');
        
        // Aplica o tema em tempo real para prévia
        if(theme === 'dark') {
            $('body').addClass('dark-mode');
        } else {
            $('body').removeClass('dark-mode');
        }
        
        toastr.info('Tema ' + (theme === 'dark' ? 'Escuro' : 'Claro') + ' selecionado. Salve para aplicar permanentemente.');
    }

    // Aplica o tema inicial baseado na preferência salva
    $(document).ready(function() {
        const currentTheme = '{{ $user->theme_preference }}';
        if(currentTheme === 'dark') {
            $('body').addClass('dark-mode');
        }
    });

    $('#profileForm').on('submit', function() {
        $(this).find('.btn-save').html('<i class="fas fa-spinner fa-spin mr-2"></i> SALVANDO...').prop('disabled', true);
    });
</script>
@stop
