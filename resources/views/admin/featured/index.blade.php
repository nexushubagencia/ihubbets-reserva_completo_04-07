@extends('adminlte::page')

@section('title', 'Partidas em Destaque')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-star text-warning mr-2"></i> Partidas em Destaque</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">

    {{-- ══════════════ STATS CARDS ══════════════ --}}
    <div class="row mb-3" id="stats-row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner">
                    <h3 id="stat-featured">0</h3>
                    <p>Em Destaque</p>
                </div>
                <div class="icon"><i class="fas fa-star"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3 id="stat-available">0</h3>
                    <p>Disponíveis Hoje</p>
                </div>
                <div class="icon"><i class="fas fa-futbol"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner">
                    <h3 id="stat-manual">0</h3>
                    <p>Eventos Manuais</p>
                </div>
                <div class="icon"><i class="fas fa-hand-paper"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-secondary">
                <div class="inner">
                    <h3 id="stat-total">0</h3>
                    <p>Total de Partidas</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
    </div>

    {{-- ══════════════ DESTAQUES ATIVOS ══════════════ --}}
    <div class="row mb-3" id="featured-cards-section" style="display:none;">
        <div class="col-12">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-fire text-danger mr-2"></i> Destaques Ativos</h3>
                    <div class="card-tools">
                        <span class="badge badge-warning" id="badge-featured-count">0</span>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex flex-wrap gap-2" id="featured-cards" style="gap:10px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ BUSCA ══════════════ --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-search mr-2"></i> Buscar Partidas</h3>
                </div>
                <div class="card-body py-2">
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-2">
                            <label class="mb-1 small text-muted">Time ou Liga</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-filter"></i></span>
                                </div>
                                <input type="text" id="search-input" class="form-control" placeholder="Ex: Flamengo, Premier League...">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="mb-1 small text-muted">Data</label>
                            <input type="date" id="date-input" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-primary btn-block" id="search-btn">
                                <i class="fas fa-search mr-1"></i> Buscar Partidas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ TABELA DE PARTIDAS ══════════════ --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-dark shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i> Partidas Encontradas</h3>
                    <div class="card-tools">
                        <span class="badge badge-info" id="badge-total-count">0</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="matches-table">
                            <thead style="background:#f4f6f9;">
                                <tr>
                                    <th width="60" class="text-center">⭐</th>
                                    <th>Partida</th>
                                    <th>Liga</th>
                                    <th>Início</th>
                                    <th width="110" class="text-center">Tempo</th>
                                    <th width="120" class="text-center">Status</th>
                                    <th width="90" class="text-center">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                        <p class="mt-2 mb-0 text-muted">Carregando partidas...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ MODAL PERSONALIZAR ══════════════ --}}
<div class="modal fade" id="modal-personalizar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border:0;">
                <h5 class="modal-title"><i class="fas fa-paint-brush mr-2"></i> Personalizar Destaque</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-personalizar">
                @csrf
                <input type="hidden" name="match_id" id="p-match-id">
                <input type="hidden" name="is_manual" id="p-is-manual">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label><i class="fas fa-link mr-1 text-primary"></i> URL da Imagem de Fundo</label>
                        <input type="text" class="form-control" name="background_path" id="p-background" placeholder="https://site.com/fundo.jpg">
                        <small class="text-muted">Opcional. Deixe em branco para usar o padrão.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label><i class="fas fa-upload mr-1 text-success"></i> Ou Subir Arquivo</label>
                        <input type="file" class="form-control" name="background_file" id="p-background-file" accept="image/*">
                        <small class="text-info"><i class="fas fa-info-circle"></i> Recomendado: <b>320 × 200 px</b></small>
                    </div>
                    <div class="form-group mb-0">
                        <label><i class="fas fa-palette mr-1 text-warning"></i> Cor do Badge</label>
                        <input type="color" class="form-control" name="badge_color" id="p-badge-color" value="#23a73d" style="width:100%;height:45px;border-radius:8px;cursor:pointer;">
                        <small class="text-muted">Cor do botão de cotações extras no card.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* ─── Star Toggle ─── */
    .star-toggle{cursor:pointer;font-size:1.6rem;transition:all .25s ease;}
    .star-toggle:hover{transform:scale(1.3);filter:drop-shadow(0 0 6px rgba(255,193,7,.6));}
    .star-active{color:#ffc107!important;}
    .star-inactive{color:#d5d8dc!important;}

    /* ─── Featured Mini Cards ─── */
    .feat-card{
        background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:12px 16px;
        min-width:220px;max-width:280px;flex:1 1 220px;position:relative;
        transition:all .2s ease;box-shadow:0 1px 4px rgba(0,0,0,.06);
    }
    .feat-card:hover{transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.1);}
    .feat-card .fc-teams{font-weight:600;font-size:.9rem;color:#2d3436;}
    .feat-card .fc-league{font-size:.75rem;color:#636e72;margin-top:2px;}
    .feat-card .fc-time{font-size:.72rem;color:#00b894;font-weight:600;margin-top:4px;}
    .feat-card .fc-badge{
        position:absolute;top:8px;right:8px;font-size:.6rem;padding:2px 6px;border-radius:4px;
    }
    .feat-card .fc-remove{
        position:absolute;bottom:8px;right:8px;font-size:.7rem;cursor:pointer;
        color:#d63031;opacity:.6;transition:opacity .2s;
    }
    .feat-card .fc-remove:hover{opacity:1;}
    .feat-card .fc-edit{
        position:absolute;bottom:8px;right:40px;font-size:.7rem;cursor:pointer;
        color:#0984e3;opacity:.6;transition:opacity .2s;
    }
    .feat-card .fc-edit:hover{opacity:1;}

    /* ─── Countdown Badge ─── */
    .countdown-badge{
        display:inline-block;padding:2px 8px;border-radius:12px;font-size:.72rem;font-weight:600;
    }
    .countdown-green{background:#d4edda;color:#155724;}
    .countdown-yellow{background:#fff3cd;color:#856404;}
    .countdown-red{background:#f8d7da;color:#721c24;}

    /* ─── Table Row Highlight ─── */
    #matches-table tbody tr{transition:background .15s;}
    #matches-table tbody tr.row-featured{background:rgba(255,193,7,.06)!important;}

    /* ─── Stats box override ─── */
    .small-box{border-radius:8px;overflow:hidden;}
    .small-box .inner h3{font-size:1.8rem;font-weight:700;}
    .small-box .icon i{font-size:50px;opacity:.2;}

    /* ─── Empty state ─── */
    .empty-state{padding:40px 20px;text-align:center;color:#b2bec3;}
    .empty-state i{font-size:3rem;margin-bottom:12px;display:block;}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {

    // Carregar já com o filtro de data atual
    loadMatches('', $('#date-input').val());
    loadAllFeatured(); // Carrega TODOS os destaques ativos (sem filtro de data)

    $('#search-btn').on('click', function() {
        loadMatches($('#search-input').val(), $('#date-input').val());
    });

    $('#search-input').on('keypress', function(e) {
        if(e.which == 13) loadMatches($(this).val(), $('#date-input').val());
    });

    $('#date-input').on('change', function() {
        loadMatches($('#search-input').val(), $(this).val());
    });

    // ─── Carrega TODOS os destaques ativos (sem filtro de data) ───
    function loadAllFeatured() {
        $.get("{{ route('featured-matches.available') }}", { search: '', date: '' }, function(data) {
            const featured = data.filter(m => m.is_featured);
            $('#stat-featured').text(featured.length);
            $('#badge-featured-count').text(featured.length);
            buildFeaturedCards(featured);
        });
    }

    // ─── Load Matches (tabela filtrada por data) ───
    function loadMatches(search = '', date = '') {
        const tbody = $('#matches-table tbody');
        tbody.html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">Carregando...</p></td></tr>');

        $.get("{{ route('featured-matches.available') }}", { search: search, date: date }, function(data) {
            tbody.empty();

            // Stats da tabela (filtrada por data)
            let manualCount = 0;
            data.forEach(m => { if(m.is_manual) manualCount++; });
            $('#stat-available').text(data.length);
            $('#stat-manual').text(manualCount);
            $('#stat-total').text(data.length);
            $('#badge-total-count').text(data.length);


            if (data.length === 0) {
                tbody.append(`<tr><td colspan="7"><div class="empty-state"><i class="fas fa-search"></i><p>Nenhuma partida encontrada para esta data.</p></div></td></tr>`);
                return;
            }

            data.forEach(match => {
                const dt = new Date(match.start_time);
                const startFormatted = dt.toLocaleDateString('pt-BR') + ' · ' + dt.toLocaleTimeString('pt-BR', {hour:'2-digit',minute:'2-digit'});
                const starClass = match.is_featured ? 'star-active fas' : 'star-inactive far';
                const rowClass = match.is_featured ? 'row-featured' : '';
                const manualTag = match.is_manual ? '<span class="badge badge-warning ml-1" style="font-size:10px;">MANUAL</span>' : '';
                const countdown = getCountdown(dt);

                const statusBadge = match.is_featured
                    ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-star mr-1"></i>Destaque</span>'
                    : '<span class="badge badge-light text-muted px-2 py-1">Normal</span>';

                const actionBtn = match.is_featured
                    ? `<button class="btn btn-xs btn-outline-primary btn-personalizar"
                        data-id="${match.id}" data-manual="${match.is_manual}"
                        data-background="${match.background_path || ''}"
                        data-badge="${match.badge_color || '{{ $themeColor }}'}">
                        <i class="fas fa-edit mr-1"></i>Editar
                      </button>`
                    : '';

                tbody.append(`
                    <tr class="${rowClass}">
                        <td class="text-center align-middle">
                            <i class="fa-star star-toggle ${starClass}" data-id="${match.id}" data-manual="${match.is_manual}" title="Clique para alternar destaque"></i>
                        </td>
                        <td class="align-middle">
                            <div style="font-weight:600;color:#2d3436;">${match.home} <span class="text-muted">vs</span> ${match.away} ${manualTag}</div>
                        </td>
                        <td class="align-middle"><span class="text-muted">${match.league}</span></td>
                        <td class="align-middle"><small>${startFormatted}</small></td>
                        <td class="text-center align-middle">${countdown}</td>
                        <td class="text-center align-middle">${statusBadge}</td>
                        <td class="text-center align-middle">${actionBtn}</td>
                    </tr>
                `);
            });
        }).fail(function(){
            tbody.html('<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Erro ao carregar partidas.</td></tr>');
        });
    }

    // ─── Build Featured Cards ───
    function buildFeaturedCards(items) {
        const container = $('#featured-cards');
        const section = $('#featured-cards-section');
        container.empty();

        if (items.length === 0) {
            section.slideUp(200);
            return;
        }

        section.slideDown(200);

        items.forEach(m => {
            const dt = new Date(m.start_time);
            const timeStr = dt.toLocaleTimeString('pt-BR', {hour:'2-digit',minute:'2-digit'});
            const dateStr = dt.toLocaleDateString('pt-BR', {day:'2-digit',month:'2-digit'});
            const tag = m.is_manual ? '<span class="fc-badge badge badge-warning">MANUAL</span>' : '<span class="fc-badge badge badge-primary">AUTO</span>';

            container.append(`
                <div class="feat-card">
                    ${tag}
                    <div class="fc-teams">${m.home} vs ${m.away}</div>
                    <div class="fc-league"><i class="fas fa-trophy mr-1"></i>${m.league}</div>
                    <div class="fc-time"><i class="far fa-clock mr-1"></i>${dateStr} às ${timeStr}</div>
                    <span class="fc-edit btn-personalizar" data-id="${m.id}" data-manual="${m.is_manual}" data-background="${m.background_path||''}" data-badge="${m.badge_color||'{{ $themeColor }}'}"><i class="fas fa-edit"></i></span>
                    <span class="fc-remove star-toggle" data-id="${m.id}" data-manual="${m.is_manual}" title="Remover destaque"><i class="fas fa-times"></i></span>
                </div>
            `);
        });
    }

    // ─── Countdown Helper ───
    function getCountdown(dt) {
        const now = new Date();
        const diff = dt - now;
        if (diff <= 0) return '<span class="countdown-badge countdown-red"><i class="fas fa-play mr-1"></i>Em andamento</span>';

        const hours = Math.floor(diff / 3600000);
        const mins = Math.floor((diff % 3600000) / 60000);

        if (hours >= 24) {
            const days = Math.floor(hours / 24);
            return `<span class="countdown-badge countdown-green"><i class="far fa-clock mr-1"></i>${days}d ${hours%24}h</span>`;
        }
        if (hours >= 2) {
            return `<span class="countdown-badge countdown-green"><i class="far fa-clock mr-1"></i>${hours}h ${mins}m</span>`;
        }
        if (hours >= 1) {
            return `<span class="countdown-badge countdown-yellow"><i class="fas fa-hourglass-half mr-1"></i>${hours}h ${mins}m</span>`;
        }
        return `<span class="countdown-badge countdown-red"><i class="fas fa-bolt mr-1"></i>${mins} min</span>`;
    }

    // ─── Toggle Featured ───
    $(document).on('click', '.star-toggle', function() {
        const icon = $(this);
        const matchId = icon.data('id');
        const isManual = icon.data('manual');

        // Visual feedback
        icon.css('pointer-events','none');
        const origHtml = icon.hasClass('fc-remove') ? icon.html() : null;
        if (!origHtml) icon.removeClass('fa-star fas far').addClass('fas fa-spinner fa-spin');

        $.post("{{ route('featured-matches.toggle') }}", {
            _token: "{{ csrf_token() }}",
            match_id: matchId,
            is_manual: isManual
        }, function(response) {
            icon.css('pointer-events','');
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message || 'Erro ao processar');
            }
            loadMatches($('#search-input').val(), $('#date-input').val());
            loadAllFeatured();
        }).fail(function() {
            icon.css('pointer-events','');
            toastr.error('Erro de conexão com o servidor');
            loadMatches($('#search-input').val(), $('#date-input').val());
            loadAllFeatured();
        });
    });

    // ─── Open Modal ───
    $(document).on('click', '.btn-personalizar', function() {
        const btn = $(this);
        $('#p-match-id').val(btn.data('id'));
        $('#p-is-manual').val(btn.data('manual'));
        $('#p-background').val(btn.data('background'));
        $('#p-badge-color').val(btn.data('badge') || '{{ $themeColor }}');
        $('#modal-personalizar').modal('show');
    });

    // ─── Save Modal ───
    $('#form-personalizar').on('submit', function(e) {
        e.preventDefault();
        const btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...');

        $.ajax({
            url: "{{ route('featured-matches.update-meta') }}",
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.warning(response.message || 'Nenhuma alteração detectada');
                }
            },
            error: function() { toastr.error('Erro de conexão'); },
            complete: function() {
                btnSubmit.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Salvar');
                $('#modal-personalizar').modal('hide');
                $('#p-background-file').val('');
                // Recarrega a lista após o modal fechar
                setTimeout(function() {
                    loadMatches($('#search-input').val(), $('#date-input').val());
                    loadAllFeatured();
                }, 300);
            }
        });
    });
});
</script>
@stop
