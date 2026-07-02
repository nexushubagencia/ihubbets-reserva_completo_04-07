@extends('adminlte::page')

@section('title', 'Gerenciar Bancas - IHUB BETS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-network-wired"></i> Gerenciar Bancas (White Label)</h1>
        <button class="btn btn-success shadow-sm" data-toggle="modal" data-target="#modalCreateSite">
            <i class="fas fa-plus-circle"></i> NOVA BANCA
        </button>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- INSTRUÇÕES CÓPIA RÁPIDA DE DNS -->
        <div class="col-md-12">
            <div class="card card-dark card-outline shadow-sm mb-4">
                <div class="card-header bg-dark border-0">
                    <h3 class="card-title text-white font-weight-bold"><i class="fas fa-info-circle mr-2"></i> Configuração de DNS para Clientes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus text-white"></i></button>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <p class="text-muted small mb-3">Informe ao cliente os seguintes dados para apontamento do domínio:</p>
                    <div class="row text-center mb-2">
                        <div class="col-md-4">
                            <label class="d-block small font-weight-bold text-secondary mb-1">TIPO</label>
                            <div class="p-2 border rounded bg-white font-weight-bold">Registro A</div>
                        </div>
                        <div class="col-md-4">
                            <label class="d-block small font-weight-bold text-secondary mb-1">HOST</label>
                            <div class="p-2 border rounded bg-white font-weight-bold">@ ou em branco</div>
                        </div>
                        <div class="col-md-4">
                            <label class="d-block small font-weight-bold text-secondary mb-1">DESTINO (IP VPS)</label>
                            <div class="p-2 border border-primary rounded bg-white font-weight-bold text-primary">
                                {{ env('SERVER_IP_PUBLIC', '127.0.0.1') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 bg-navy">
                    <h3 class="card-title">Sites Ativos no Ecossistema</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-valign-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Domínio</th>
                                    <th>Acesso Admin</th>
                                    <th>Status</th>
                                    <th>Financeiro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sites as $site)
                                    <tr id="site-row-{{ $site->id }}">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-weight-bold">{{ $site->name }}</span>
                                                @php
                                                    $displayDomain = $site->domain;
                                                    $isFullDomain = str_contains($displayDomain, '.');
                                                    $url = $isFullDomain ? "http://{$displayDomain}" : "http://{$displayDomain}.localhost:8000";
                                                    if(config('app.env') === 'production') {
                                                        $url = "https://{$displayDomain}";
                                                    }
                                                @endphp
                                                <a href="{{ $url }}" target="_blank" class="text-xs text-primary">
                                                    <i class="fas fa-external-link-alt mr-1"></i> {{ str_replace(['http://', 'https://'], '', $url) }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-nowrap">
                                            <div class="bg-light p-1 rounded border shadow-sm" style="font-size: 0.75rem; line-height: 1.1;">
                                                <div class="mb-1">
                                                    <i class="fas fa-envelope text-navy mr-1"></i> {{ $site->admin_email ?? '---' }}
                                                </div>
                                                <div>
                                                    <i class="fas fa-lock text-navy mr-1"></i> <span class="badge badge-warning py-0 px-1">admin123</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $site->status === 'active' ? 'success' : 'danger' }}">
                                                {{ strtoupper($site->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small">Faturamento: <strong>R$ {{ number_format($site->due_value ?? 0, 2, ',', '.') }}</strong></div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" title="Configurar" 
                                                        onclick="editSite({{ json_encode([
                                                            'id' => $site->id,
                                                            'name' => $site->name,
                                                            'domain' => $site->domain,
                                                            'admin_email' => $site->admin_email,
                                                            'due_value' => $site->due_value,
                                                            'active_affiliates' => $site->active_affiliates ?? 1,
                                                            'active_payments' => $site->active_payments ?? 1,
                                                            'active_mercado_pago' => $site->active_mercado_pago ?? 1,
                                                            'active_loto' => $site->active_loto ?? 1,
                                                            'active_marketing' => $site->active_marketing ?? 1,
                                                            'active_configuracoes' => $site->active_configuracoes ?? 1,
                                                            'active_riscos' => $site->active_riscos ?? 1,
                                                            'active_lancamentos' => $site->active_lancamentos ?? 1,
                                                            'active_extrato' => $site->active_extrato ?? 1,
                                                            'active_banner_generator' => $site->active_banner_generator ?? 1,
                                                            'active_gateway_deposito' => $site->active_gateway_deposito ?? 1,
                                                        ]) }})">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <button class="btn btn-sm btn-{{ $site->status === 'active' ? 'outline-danger' : 'outline-success' }}" 
                                                        onclick="toggleStatus('{{ $site->id }}', '{{ $site->status }}')">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">Nenhum site encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL NOVA BANCA -->
    <div class="modal fade" id="modalCreateSite" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formCreateSite">
                    @csrf
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i> Provisionar Nova Banca</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nome da Banca</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Domínio</label>
                                <input type="text" name="domain" class="form-control" placeholder="dominio.com" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail Admin</label>
                                <input type="email" name="admin_email" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Mensalidade</label>
                                <input type="number" step="0.01" name="due_value" class="form-control" value="500.00">
                            </div>
                            <div class="col-md-12 mt-2">
                                <label class="text-primary font-weight-bold"><i class="fas fa-toggle-on"></i> Módulos Disponíveis</label>
                                <p class="text-muted small mb-2">Ative ou desative cada função. Itens desativados somem do menu e do frontend da banca.</p>
                                <div class="row bg-light p-3 rounded border">
                                    <!-- Row 1: Financeiro Core -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_gateway_deposito" name="active_gateway_deposito" value="1" checked>
                                            <label class="custom-control-label" for="create_active_gateway_deposito">
                                                <i class="fas fa-qrcode text-success mr-1"></i> Depósitos (Automático)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Mercado Pago PIX</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_payments" name="active_payments" value="1" checked>
                                            <label class="custom-control-label" for="create_active_payments">
                                                <i class="fas fa-money-bill-wave text-primary mr-1"></i> Saques (Manual)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Solicitações de Saque</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_extrato" name="active_extrato" value="1" checked>
                                            <label class="custom-control-label" for="create_active_extrato">
                                                <i class="fas fa-list-alt text-teal mr-1"></i> Financeiro & Relatórios
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Histórico e Extratos</small>
                                    </div>

                                    <!-- Row 2: Jogos & Promo -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_loto" name="active_loto" value="1">
                                            <label class="custom-control-label" for="create_active_loto">
                                                <i class="fas fa-dice text-warning mr-1"></i> Módulo Loto
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Quininha e Seninha</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_bonus" name="active_bonus" value="1">
                                            <label class="custom-control-label" for="create_active_bonus">
                                                <i class="fas fa-gift text-purple mr-1"></i> Módulo de Bônus
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Promoções e Códigos</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_affiliates" name="active_affiliates" value="1">
                                            <label class="custom-control-label" for="create_active_affiliates">
                                                <i class="fas fa-handshake text-info mr-1"></i> Afiliados (CPA/Rev)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Gestão de Parceiros</small>
                                    </div>

                                    <!-- Row 3: Admin & Gestão -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_configuracoes" name="active_configuracoes" value="1" checked>
                                            <label class="custom-control-label" for="create_active_configuracoes">
                                                <i class="fas fa-tools text-dark mr-1"></i> Config. Sistema
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Ligas, Odds e Mercados</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_marketing" name="active_marketing" value="1">
                                            <label class="custom-control-label" for="create_active_marketing">
                                                <i class="fas fa-bullhorn text-danger mr-1"></i> Marketing & Banners
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Home e Destaques</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_riscos" name="active_riscos" value="1" checked>
                                            <label class="custom-control-label" for="create_active_riscos">
                                                <i class="fas fa-user-shield text-secondary mr-1"></i> Gestão de Riscos
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Limites e Auditoria</small>
                                    </div>

                                    <!-- Row 4: Ferramentas Adicionais -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_lancamentos" name="active_lancamentos" value="1" checked>
                                            <label class="custom-control-label" for="create_active_lancamentos">
                                                <i class="fas fa-exchange-alt text-muted mr-1"></i> Lançamentos Manuais
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Créditos e Débitos</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_banner_generator" name="active_banner_generator" value="1" checked>
                                            <label class="custom-control-label" for="create_active_banner_generator">
                                                <i class="fas fa-magic text-pink mr-1"></i> Gerador de Banners
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Criação de artes</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="create_active_mercado_pago" name="active_mercado_pago" value="1" checked>
                                            <label class="custom-control-label" for="create_active_mercado_pago">
                                                <i class="fas fa-address-card text-info mr-1"></i> Chaves PIX (Clientes)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Lista de chaves p/ saque</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-success">CRIAR AGORA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR BANCA -->
    <div class="modal fade" id="modalEditSite" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="formEditSite">
                    @csrf
                    <input type="hidden" name="site_id" id="edit_site_id">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Editar Configurações da Banca</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nome da Banca</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Domínio</label>
                                <input type="text" name="domain" id="edit_domain" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>E-mail Admin (Login)</label>
                                <input type="email" name="admin_email" id="edit_admin_email" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Mensalidade (R$)</label>
                                <input type="number" step="0.01" name="due_value" id="edit_due_value" class="form-control">
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Nova Senha (deixe em branco para não alterar)</label>
                                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres">
                            </div>
                            <div class="col-md-12 mt-2">
                                <label class="text-primary font-weight-bold"><i class="fas fa-toggle-on"></i> Módulos Disponíveis</label>
                                <p class="text-muted small mb-2">Ative ou desative cada função. Itens desativados somem do menu e do frontend da banca.</p>
                                <div class="row bg-light p-3 rounded border">
                                    <!-- Row 1: Financeiro Core -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_gateway_deposito" name="active_gateway_deposito" value="1">
                                            <label class="custom-control-label" for="edit_active_gateway_deposito">
                                                <i class="fas fa-qrcode text-success mr-1"></i> Depósitos (Automático)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Mercado Pago PIX</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_payments" name="active_payments" value="1">
                                            <label class="custom-control-label" for="edit_active_payments">
                                                <i class="fas fa-money-bill-wave text-primary mr-1"></i> Saques (Manual)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Solicitações de Saque</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_extrato" name="active_extrato" value="1">
                                            <label class="custom-control-label" for="edit_active_extrato">
                                                <i class="fas fa-list-alt text-teal mr-1"></i> Financeiro & Relatórios
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Histórico e Extratos</small>
                                    </div>

                                    <!-- Row 2: Jogos & Promo -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_loto" name="active_loto" value="1">
                                            <label class="custom-control-label" for="edit_active_loto">
                                                <i class="fas fa-dice text-warning mr-1"></i> Módulo Loto
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Quininha e Seninha</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_bonus" name="active_bonus" value="1">
                                            <label class="custom-control-label" for="edit_active_bonus">
                                                <i class="fas fa-gift text-purple mr-1"></i> Módulo de Bônus
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Promoções e Códigos</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_affiliates" name="active_affiliates" value="1">
                                            <label class="custom-control-label" for="edit_active_affiliates">
                                                <i class="fas fa-handshake text-info mr-1"></i> Afiliados (CPA/Rev)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Gestão de Parceiros</small>
                                    </div>

                                    <!-- Row 3: Admin & Gestão -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_configuracoes" name="active_configuracoes" value="1">
                                            <label class="custom-control-label" for="edit_active_configuracoes">
                                                <i class="fas fa-tools text-dark mr-1"></i> Config. Sistema
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Ligas, Odds e Mercados</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_marketing" name="active_marketing" value="1">
                                            <label class="custom-control-label" for="edit_active_marketing">
                                                <i class="fas fa-bullhorn text-danger mr-1"></i> Marketing & Banners
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Home e Destaques</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_riscos" name="active_riscos" value="1">
                                            <label class="custom-control-label" for="edit_active_riscos">
                                                <i class="fas fa-user-shield text-secondary mr-1"></i> Gestão de Riscos
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Limites e Auditoria</small>
                                    </div>

                                    <!-- Row 4: Ferramentas Adicionais -->
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_lancamentos" name="active_lancamentos" value="1">
                                            <label class="custom-control-label" for="edit_active_lancamentos">
                                                <i class="fas fa-exchange-alt text-muted mr-1"></i> Lançamentos Manuais
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Créditos e Débitos</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_banner_generator" name="active_banner_generator" value="1">
                                            <label class="custom-control-label" for="edit_active_banner_generator">
                                                <i class="fas fa-magic text-pink mr-1"></i> Gerador de Banners
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Criação de artes</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="edit_active_mercado_pago" name="active_mercado_pago" value="1">
                                            <label class="custom-control-label" for="edit_active_mercado_pago">
                                                <i class="fas fa-address-card text-info mr-1"></i> Chaves PIX (Clientes)
                                            </label>
                                        </div>
                                        <small class="text-muted d-block ml-4">Lista de chaves p/ saque</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">SALVAR ALTERAÇÕES</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('#formCreateSite').on('submit', function(e) {
        e.preventDefault();
        $.post('/admin/master/banca/criar', $(this).serialize(), function(r) {
            Swal.fire('Sucesso!', 'Banca criada com sucesso.', 'success').then(() => location.reload());
        }).fail(e => Swal.fire('Erro', 'Falha ao criar banca', 'error'));
    });

    function editSite(data) {
        $('#edit_site_id').val(data.id);
        $('#edit_name').val(data.name);
        $('#edit_domain').val(data.domain);
        $('#edit_admin_email').val(data.admin_email);
        $('#edit_due_value').val(data.due_value);
        
        // Populate ALL toggles
        $('#edit_active_affiliates').prop('checked', data.active_affiliates == 1);
        $('#edit_active_payments').prop('checked', data.active_payments == 1);
        $('#edit_active_mercado_pago').prop('checked', data.active_mercado_pago == 1);
        $('#edit_active_loto').prop('checked', data.active_loto == 1);
        $('#edit_active_marketing').prop('checked', data.active_marketing == 1);
        $('#edit_active_bonus').prop('checked', data.active_bonus == 1);
        $('#edit_active_configuracoes').prop('checked', data.active_configuracoes == 1);
        $('#edit_active_riscos').prop('checked', data.active_riscos == 1);
        $('#edit_active_lancamentos').prop('checked', data.active_lancamentos == 1);
        $('#edit_active_extrato').prop('checked', data.active_extrato == 1);
        $('#edit_active_banner_generator').prop('checked', data.active_banner_generator == 1);
        $('#edit_active_gateway_deposito').prop('checked', data.active_gateway_deposito == 1);
        
        $('#modalEditSite').modal('show');
    }

    $('#formEditSite').on('submit', function(e) {
        e.preventDefault();
        $.post('/admin/master/banca/update', $(this).serialize(), function(r) {
            Swal.fire('Sucesso!', 'Configurações atualizadas.', 'success').then(() => location.reload());
        }).fail(e => {
            let msg = e.responseJSON ? e.responseJSON.message : 'Falha ao atualizar';
            Swal.fire('Erro', msg, 'error');
        });
    });

    function toggleStatus(id, current) {
        $.post(`/admin/master/banca/${id}/toggle`, { _token: '{{ csrf_token() }}' }, function() {
            location.reload();
        });
    }
</script>
@endpush
@stop

@section('css')
<style>
    .bg-navy { background-color: #001f3f !important; color: #fff; }
    .text-purple { color: #6f42c1; }
    .text-teal { color: #20c997; }
    .text-pink { color: #e83e8c; }
    .custom-control-label { font-size: 0.9rem; font-weight: 600; }
</style>
@stop
