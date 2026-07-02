@extends('adminlte::page')

@section('title', 'Perfil de Usuário')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h1 class="font-weight-bold" style="font-family: 'Inter', sans-serif; font-size: 2.2rem; letter-spacing: -0.5px; color: var(--text-main);">
                Configurações de Perfil
            </h1>
            <p class="text-muted mb-0" style="font-size: 0.95rem;">Gerencie suas informações pessoais e credenciais de acesso.</p>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            
        </nav>
    </div>
@stop

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .ep-card {
        background: var(--bg-card, #fff) !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06) !important;
        color: var(--text-main, #1e293b) !important;
    }
    .dark-mode .ep-card {
        background: var(--dark-card, #1e293b) !important;
        border-color: var(--dark-border, #334155) !important;
        color: var(--dark-text, #e2e8f0) !important;
    }

    /* Avatar */
    .ep-avatar-wrap {
        position: relative;
        width: 130px; height: 130px;
        margin: 0 auto;
        border-radius: 50%;
        cursor: pointer;
        border: 4px solid rgba(78,115,223,0.3);
        overflow: hidden;
        transition: all 0.3s;
        background: transparent;
    }
    .ep-avatar-wrap:hover { border-color: rgba(78,115,223,0.7); transform: scale(1.03); }
    .ep-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; transition: filter 0.3s; }
    .ep-avatar-wrap:hover img { filter: brightness(0.5); }
    .ep-avatar-overlay {
        position: absolute; inset: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        color: #fff; opacity: 0; transition: opacity 0.3s;
    }
    .ep-avatar-wrap:hover .ep-avatar-overlay { opacity: 1; }

    .ep-role-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, rgba(78,115,223,0.15), rgba(78,115,223,0.08));
        color: #4e73df; padding: 5px 16px; border-radius: 20px;
        font-weight: 700; font-size: 0.72rem; letter-spacing: 0.8px; text-transform: uppercase;
        border: 1px solid rgba(78,115,223,0.2);
    }
    .dark-mode .ep-role-badge { background: rgba(96,165,250,0.15); color: #60a5fa; border-color: rgba(96,165,250,0.25); }

    /* Tabs */
    .ep-tabs { display: flex; gap: 4px; background: var(--bg-card, #f1f5f9); border-radius: 12px; padding: 4px; margin-bottom: 24px; border: 1px solid var(--border, #e2e8f0); }
    .dark-mode .ep-tabs { background: rgba(15,23,42,0.5); border-color: var(--dark-border, #334155); }
    .ep-tab {
        flex: 1; padding: 10px 16px; border-radius: 10px; border: none;
        background: transparent; color: var(--text-muted, #94a3b8);
        font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.25s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .ep-tab:hover { color: var(--text-main, #1e293b); background: rgba(78,115,223,0.05); }
    .ep-tab.active {
        background: #4e73df; color: #fff !important;
        box-shadow: 0 2px 8px rgba(78,115,223,0.3);
    }
    .dark-mode .ep-tab:hover { color: #e2e8f0; }
    .ep-tab-content { display: none; animation: fadeTab 0.3s ease; }
    .ep-tab-content.active { display: block; }
    @keyframes fadeTab { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    /* Form */
    .ep-label { font-size: 0.82rem; font-weight: 600; color: var(--text-main, #475569); margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
    .dark-mode .ep-label { color: var(--dark-text, #cbd5e1); }
    .ep-input {
        width: 100%; border-radius: 10px; padding: 11px 14px; font-size: 0.9rem;
        border: 1px solid var(--border, #e2e8f0); background: var(--bg-card, #fff);
        color: var(--text-main, #1e293b); transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .ep-input:focus { border-color: #4e73df; box-shadow: 0 0 0 3px rgba(78,115,223,0.12); outline: none; }
    .ep-input:read-only { background: #f8fafc; cursor: not-allowed; opacity: 0.7; }
    .dark-mode .ep-input { background: rgba(15,23,42,0.5); border-color: var(--dark-border, #334155); color: var(--dark-text, #e2e8f0); }
    .dark-mode .ep-input:read-only { background: rgba(0,0,0,0.2); }

    .ep-alert {
        border-radius: 12px; padding: 14px 18px; margin-bottom: 20px;
        display: flex; align-items: center; gap: 14px; border: none;
    }
    .ep-alert-success { background: rgba(34,197,94,0.08); color: #16a34a; }
    .ep-alert-error { background: rgba(239,68,68,0.08); color: #dc2626; }
    .ep-alert-warning { background: rgba(245,158,11,0.08); color: #d97706; border: 1px solid rgba(245,158,11,0.15); }

    .ep-btn-save {
        background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; border: none;
        border-radius: 10px; padding: 12px 32px; font-weight: 600; font-size: 0.9rem;
        cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(22,163,74,0.25);
    }
    .ep-btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(22,163,74,0.35); color: #fff; }

    .ep-btn-photo {
        background: transparent; border: 1.5px dashed rgba(78,115,223,0.4);
        color: #4e73df; border-radius: 10px; padding: 8px 18px; font-weight: 600;
        font-size: 0.8rem; cursor: pointer; transition: all 0.2s;
    }
    .ep-btn-photo:hover { background: rgba(78,115,223,0.06); border-color: #4e73df; }

    .ep-stat-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted, #94a3b8); }
    .ep-stat-value { font-weight: 700; font-size: 1.1rem; color: #4e73df; }
    .ep-divider { width: 1px; height: 36px; background: var(--border, #e2e8f0); }
    .dark-mode .ep-divider { background: var(--dark-border, #334155); }

    .ep-section-title { font-size: 0.9rem; font-weight: 700; color: var(--text-main, #1e293b); margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
    .ep-section-title i { font-size: 1rem; }
    .dark-mode .ep-section-title { color: var(--dark-text, #e2e8f0); }

    .ep-photo-preview { display: none; margin-top: 12px; padding: 10px 14px; background: rgba(78,115,223,0.05); border-radius: 10px; font-size: 0.82rem; color: #4e73df; align-items: center; gap: 8px; }
    .ep-photo-preview.show { display: flex; }

    .ep-toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted, #94a3b8); cursor: pointer; padding: 4px; }
</style>

<div class="container-fluid pb-5">
    <form action="{{ route('admin.profile.update') }}" method="POST" id="profileForm" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <!-- Bloqueia autofill agressivo do Chrome -->
        <input type="text" name="fake_user" style="display:none;" tabindex="-1" autocomplete="off">
        <input type="password" name="fake_pass" style="display:none;" tabindex="-1" autocomplete="new-password">
        <input type="file" name="avatar" id="avatarInput" style="display:none;" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">

        <div class="row">
            <!-- SIDEBAR: Avatar & Info -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="ep-card p-4 text-center h-100 d-flex flex-column align-items-center justify-content-center">

                    <div class="ep-avatar-wrap mb-3" onclick="document.getElementById('avatarInput').click()" title="Clique para trocar a foto">
                        @php
                            $avatarUrl = auth()->user()->avatar
                                ? asset(auth()->user()->avatar)
                                : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=4e73df&color=fff&size=256&bold=true';
                        @endphp
                        <img src="{{ $avatarUrl }}" alt="Foto de Perfil" class="avatar-preview-sync" id="avatarPreview">
                        <div class="ep-avatar-overlay">
                            <i class="fas fa-camera fa-lg mb-1"></i>
                            <span style="font-size:0.75rem; font-weight:600;">Alterar Foto</span>
                        </div>
                    </div>

                    <button type="button" class="ep-btn-photo mb-3" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-image mr-1"></i> Escolher Foto
                    </button>

                    <div id="photoPreviewMsg" class="ep-photo-preview mb-3">
                        <i class="fas fa-check-circle"></i>
                        <span id="photoFileName">foto.jpg</span>
                    </div>

                    <div class="ep-role-badge mb-3">
                        <i class="fas fa-shield-alt"></i> {{ strtoupper(auth()->user()->role ?? 'Admin') }}
                    </div>

                    <h4 class="font-weight-bold mb-1" style="font-size:1.2rem;">{{ auth()->user()->name }}</h4>
                    <p class="mb-4" style="color: var(--text-muted, #94a3b8); font-size:0.88rem;">@ {{ auth()->user()->username }}</p>

                    <div class="d-flex align-items-center justify-content-center gap-4">
                        <div class="text-center">
                            <span class="d-block ep-stat-label">ID da Conta</span>
                            <span class="ep-stat-value">#{{ str_pad(auth()->user()->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="ep-divider"></div>
                        <div class="text-center">
                            <span class="d-block ep-stat-label">Status</span>
                            <span class="badge px-3 py-1 mt-1" style="background:rgba(34,197,94,0.12); color:#16a34a; font-weight:700; border-radius:8px;">Ativo</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN: Formulários com Tabs -->
            <div class="col-xl-8 col-lg-7">
                <div class="ep-card p-4 p-md-5">

                    @if(session('success'))
                        <div class="ep-alert ep-alert-success">
                            <i class="fas fa-check-circle fa-lg"></i>
                            <div><h6 class="mb-0 font-weight-bold">Tudo certo!</h6><span style="font-size:0.85rem;">{{ session('success') }}</span></div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="ep-alert ep-alert-error">
                            <i class="fas fa-times-circle fa-lg"></i>
                            <div><h6 class="mb-0 font-weight-bold">Erro!</h6><span style="font-size:0.85rem;">{{ session('error') }}</span></div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="ep-alert ep-alert-error">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                            <div>
                                <h6 class="mb-0 font-weight-bold">Ops! Verifique os erros:</h6>
                                <ul class="mb-0 pl-3 mt-1" style="font-size:0.85rem;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- TABS -->
                    <div class="ep-tabs">
                        <button type="button" class="ep-tab active" data-tab="personal">
                            <i class="fas fa-user"></i> Dados Pessoais
                        </button>
                        <button type="button" class="ep-tab" data-tab="financial">
                            <i class="fas fa-wallet"></i> Financeiro / PIX
                        </button>
                        <button type="button" class="ep-tab" data-tab="security">
                            <i class="fas fa-lock"></i> Segurança
                        </button>
                    </div>

                    <!-- TAB: Dados Pessoais -->
                    <div class="ep-tab-content active" id="tab-personal">
                        <div class="ep-section-title">
                            <i class="fas fa-user-edit" style="color:#4e73df;"></i> Informações Pessoais
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">
                                    Nome de Exibição
                                    <i class="fas fa-info-circle" style="color:var(--text-muted,#94a3b8); font-size:0.78rem; cursor:help;" data-toggle="tooltip" title="Como você aparecerá no sistema."></i>
                                </label>
                                <input type="text" name="name" class="ep-input" value="{{ old('name', auth()->user()->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">
                                    Nome de Usuário (Login)
                                    <i class="fas fa-info-circle" style="color:var(--text-muted,#94a3b8); font-size:0.78rem; cursor:help;" data-toggle="tooltip" title="Não pode ser alterado."></i>
                                </label>
                                <input type="text" class="ep-input" value="{{ auth()->user()->username }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="ep-label"><i class="fas fa-envelope" style="color:#94a3b8;font-size:0.78rem;"></i> E-mail</label>
                                <input type="email" name="email" class="ep-input" value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                        </div>

                    </div>

                    <!-- TAB: Financeiro / PIX -->
                    <div class="ep-tab-content" id="tab-financial">
                        <div class="ep-section-title">
                            <i class="fas fa-wallet" style="color:#10b981;"></i> Dados Financeiros para Saques (PIX)
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">CPF</label>
                                <input type="text" name="cpf" class="ep-input cpf-mask" value="{{ old('cpf', auth()->user()->cpf) }}" placeholder="000.000.000-00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">Telefone / Contato</label>
                                <input type="text" name="phone" class="ep-input phone-mask" value="{{ old('phone', auth()->user()->contato) }}" placeholder="(00) 0 0000-0000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">Tipo de Chave PIX</label>
                                <select name="pix_key_type" class="ep-input" style="height: auto; padding: 10px 14px;">
                                    <option value="">Selecione...</option>
                                    <option value="cpf" {{ old('pix_key_type', auth()->user()->pix_key_type) == 'cpf' ? 'selected' : '' }}>CPF</option>
                                    <option value="cnpj" {{ old('pix_key_type', auth()->user()->pix_key_type) == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                    <option value="email" {{ old('pix_key_type', auth()->user()->pix_key_type) == 'email' ? 'selected' : '' }}>E-mail</option>
                                    <option value="telefone" {{ old('pix_key_type', auth()->user()->pix_key_type) == 'telefone' ? 'selected' : '' }}>Telefone</option>
                                    <option value="aleatoria" {{ old('pix_key_type', auth()->user()->pix_key_type) == 'aleatoria' ? 'selected' : '' }}>Chave Aleatória</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">Chave PIX</label>
                                <input type="text" name="pix_key" class="ep-input" value="{{ old('pix_key', auth()->user()->pix_key) }}" placeholder="Sua chave PIX">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="ep-label">Endereço</label>
                                <input type="text" name="endereco" class="ep-input" value="{{ old('endereco', auth()->user()->address) }}" placeholder="Rua, Número, Bairro, Cidade - UF">
                            </div>
                        </div>
                    </div>

                    <!-- TAB: Segurança -->
                    <div class="ep-tab-content" id="tab-security">
                        <div class="ep-section-title">
                            <i class="fas fa-shield-alt" style="color:#ef4444;"></i> Alterar Senha
                        </div>

                        <div class="ep-alert ep-alert-warning">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                            <div>
                                <h6 class="mb-0 font-weight-bold" style="font-size:0.88rem;">Atenção</h6>
                                <p class="mb-0" style="font-size:0.82rem;">Só preencha os campos abaixo caso deseje <strong>alterar sua senha atual</strong>. Deixe em branco para manter a mesma.</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">Nova Senha</label>
                                <div style="position:relative;">
                                    <input type="password" name="password" class="ep-input pw-field" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                                    <button type="button" class="ep-toggle-pw" onclick="togglePw(this)"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="ep-label">Confirmar Nova Senha</label>
                                <div style="position:relative;">
                                    <input type="password" name="password_confirmation" class="ep-input pw-field" placeholder="Repita a senha" autocomplete="new-password">
                                    <button type="button" class="ep-toggle-pw" onclick="togglePw(this)"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 text-right" style="border-top: 1px solid var(--border, #e2e8f0);">
                        <button type="submit" class="ep-btn-save" id="btnSave">
                            <i class="fas fa-save mr-2"></i> Salvar Configurações
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

@stop

@section('adminlte_js')
<script>
    $(document).ready(function() {
        try { $('[data-toggle="tooltip"]').tooltip(); } catch(e) {}

        // ---- TABS ----
        $(document).on('click', '.ep-tab', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var tab = $(this).data('tab');
            $('.ep-tab').removeClass('active');
            $(this).addClass('active');
            $('.ep-tab-content').removeClass('active');
            $('#tab-' + tab).addClass('active');
        });

        // ---- FOTO UPLOAD PREVIEW ----
        $('#avatarInput').on('change', function() {
            var file = this.files[0];
            if (!file) return;

            if (file.size > 20 * 1024 * 1024) {
                toastr.error('A imagem é muito grande! Máximo 20MB.', 'Erro');
                this.value = '';
                return;
            }

            var validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                toastr.error('Formato inválido! Use JPEG, PNG, GIF ou WEBP.', 'Erro');
                this.value = '';
                return;
            }

            $('.avatar-preview-sync').attr('src', URL.createObjectURL(file));

            $('#photoFileName').text(file.name);
            $('#photoPreviewMsg').addClass('show');
        });

        // ---- SUBMIT LOADING ----
        $('#profileForm').on('submit', function() {
            // Limpa senhas vazias antes de enviar para evitar conflito com autofill
            $('.pw-field').each(function() {
                if ($(this).val() === '' || $(this).val().length < 6) {
                    $(this).prop('disabled', true);
                }
            });
            var btn = $('#btnSave');
            btn.html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Salvando...').css('opacity','0.7').prop('disabled', true);
        });
    });

    function togglePw(btn) {
        var input = $(btn).siblings('input');
        var icon = $(btn).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }
</script>
@stop