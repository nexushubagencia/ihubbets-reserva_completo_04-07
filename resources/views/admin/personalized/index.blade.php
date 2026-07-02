@extends('adminlte::page')

@section('title', 'Partidas Personalizadas - IHUB V2')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-tags text-primary"></i> Partidas Personalizadas 
            <small class="text-muted" style="font-size: 0.6em;">Pesquisar no sistema</small>
        </h1>
        
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <!-- Espaço para busca se necessário futuramente -->
                </div>
                <div class="col-md-4 text-right">
                    <form action="{{ route('admin.personalized.index') }}" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por Time ou Liga..." value="{{ $search ?? '' }}">
                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if($search)
                                    <a href="{{ route('admin.personalized.index') }}" class="btn btn-default border" title="Limpar Busca">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                    <button class="btn btn-info shadow-sm px-4 font-weight-bold" data-toggle="modal" data-target="#modalInserirPartida">
                        Inserir Partida <i class="fas fa-plus ml-1"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-hover border-bottom">
                    <thead class="bg-light">
                        <tr class="text-secondary small font-weight-bold">
                            <th class="border-0" style="width: 35%;">CONFRONTO / LIGA</th>
                            <th class="border-0 text-center">DATA / HORA</th>
                            <th class="border-0 text-center">CASA</th>
                            <th class="border-0 text-center">EMPATE</th>
                            <th class="border-0 text-center">FORA</th>
                            <th class="border-0 text-center">HOME?</th>
                            <th class="border-0 text-center">EXIBIR</th>
                            <th class="border-0 text-center">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matches as $match)
                            <tr class="align-middle" id="row-{{ $match->id }}" style="transition: all 0.3s ease;">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            @if($match->home_flag)
                                                <img src="/{{ $match->home_flag }}" width="32" class="shadow-sm rounded bg-white border" style="padding: 2px;">
                                            @else
                                                <img src="/assets/img/default-shield.png" width="32" class="opacity-50">
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold text-dark">{{ $match->home_team }}</span>
                                            <span class="text-muted small">vs {{ $match->away_team }}</span>
                                            <small class="text-primary font-weight-bold" style="font-size: 10px;">
                                                <i class="fas fa-trophy mr-1"></i>{{ $match->league_name }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($match->start_time)->format('d/m/Y') }}</span>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border px-2 py-1">{{ number_format($match->odd_home, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border px-2 py-1">{{ number_format($match->odd_draw, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border px-2 py-1">{{ number_format($match->odd_away, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($match->is_featured)
                                        <span class="badge badge-success"><i class="fas fa-home"></i> Sim</span>
                                    @else
                                        <span class="badge badge-secondary">Não</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                        <input type="checkbox" class="custom-control-input btn-toggle-status" 
                                               id="switchExibir{{ $match->id }}" 
                                               data-id="{{ $match->id }}"
                                               {{ $match->status == 'open' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="switchExibir{{ $match->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info btn-edit" 
                                            data-id="{{ $match->id }}"
                                            data-json="{{ json_encode($match) }}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.personalized.destroy', $match->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir partida?')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i><br>
                                    Nenhuma partida personalizada cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $matches->appends(['search' => $search])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Inserir Partida -->
<div class="modal fade" id="modalInserirPartida" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('admin.personalized.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">Inserir Partida Personalizada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-white">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Time de Casa</label>
                            <input type="text" name="home_team" class="form-control" placeholder="Nome do Time" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Time de Fora</label>
                            <input type="text" name="away_team" class="form-control" placeholder="Nome do Time" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group text-center">
                            <label class="small font-weight-bold">Casa</label>
                            <input type="number" step="0.01" name="odd_home" class="form-control text-center" placeholder="odd" required>
                        </div>
                        <div class="col-md-4 form-group text-center">
                            <label class="small font-weight-bold">Empate</label>
                            <input type="number" step="0.01" name="odd_draw" class="form-control text-center" placeholder="odd">
                        </div>
                        <div class="col-md-4 form-group text-center">
                            <label class="small font-weight-bold">Fora</label>
                            <input type="number" step="0.01" name="odd_away" class="form-control text-center" placeholder="odd" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Nome da Liga</label>
                            <input type="text" name="league_name" class="form-control" placeholder="ex: Kings League" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Esporte</label>
                            <input list="esportesList" name="sport_name" class="form-control" placeholder="Digite ou selecione" required>
                            <datalist id="esportesList">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">DATA</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">HORA</label>
                            <input type="time" name="start_time_only" class="form-control" required>
                        </div>
                        <div class="col-md-4 pt-4">
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="isFeatured" name="is_featured">
                                <label class="custom-control-label" for="isFeatured">Destaque na Home?</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div id="marketGroupsContainer">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0"><i class="fas fa-list text-primary"></i> Grupos de Mercados (Dinâmicos)</h6>
                            <div class="custom-control custom-checkbox ml-3">
                                <input type="checkbox" class="custom-control-input" id="hasExtraMarkets" name="has_extra_markets" value="1" checked>
                                <label class="custom-control-label small font-weight-bold" for="hasExtraMarkets">Habilitar no Site?</label>
                            </div>
                            <button type="button" class="btn btn-xs btn-primary btn-add-group ml-auto"><i class="fas fa-plus mr-1"></i> Novo Grupo</button>
                        </div>
                        
                        <!-- Template de Grupo será inserido aqui -->
                        <div class="text-center py-3 border rounded bg-light empty-groups-msg">
                            <small class="text-muted">Nenhum grupo de mercado extra adicionado.</small>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 text-center">
                            <div class="mb-2">
                                <img id="previewHome" src="{{ asset('img/placeholders/shield.png') }}" class="img-fluid shadow-sm rounded" style="max-height: 80px; width: 80px; object-fit: contain;">
                            </div>
                            <label class="small font-weight-bold">Bandeira Time da Casa</label>
                            <div class="custom-file">
                                <input type="file" name="home_flag" class="custom-file-input" id="inputHome">
                                <label class="custom-file-label text-left">Selecione bandeira</label>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="mb-2">
                                <img id="previewAway" src="{{ asset('img/placeholders/shield.png') }}" class="img-fluid shadow-sm rounded" style="max-height: 80px; width: 80px; object-fit: contain;">
                            </div>
                            <label class="small font-weight-bold">Bandeira Time de Fora</label>
                            <div class="custom-file">
                                <input type="file" name="away_flag" class="custom-file-input" id="inputAway">
                                <label class="custom-file-label text-left">Selecione bandeira</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-success px-5 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Partida -->
<div class="modal fade" id="modalEditarPartida" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formEditarPartida" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Editar Partida</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-white">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Time de Casa</label>
                            <input type="text" name="home_team" id="edit_home_team" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="small font-weight-bold">Time de Fora</label>
                            <input type="text" name="away_team" id="edit_away_team" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group text-center">
                            <label class="small font-weight-bold">Casa</label>
                            <input type="number" step="0.01" name="odd_home" id="edit_odd_home" class="form-control text-center" required>
                        </div>
                        <div class="col-md-4 form-group text-center">
                            <label class="small font-weight-bold">Empate</label>
                            <input type="number" step="0.01" name="odd_draw" id="edit_odd_draw" class="form-control text-center">
                        </div>
                        <div class="col-md-4 form-group text-center">
                            <label class="small font-weight-bold">Fora</label>
                            <input type="number" step="0.01" name="odd_away" id="edit_odd_away" class="form-control text-center" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Nome da Liga</label>
                            <input type="text" name="league_name" id="edit_league_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">Esporte</label>
                            <input list="esportesListEdit" name="sport_name" id="edit_sport_name" class="form-control" placeholder="Digite ou selecione" required>
                            <datalist id="esportesListEdit">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">DATA</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="small font-weight-bold">HORA</label>
                            <input type="time" name="start_time_only" id="edit_start_time_only" class="form-control" required>
                        </div>
                        <div class="col-md-4 pt-4">
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="edit_isFeatured" name="is_featured">
                                <label class="custom-control-label" for="edit_isFeatured">Destaque na Home?</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div id="editMarketGroupsContainer">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0"><i class="fas fa-list text-primary"></i> Grupos de Mercados</h6>
                            <div class="custom-control custom-checkbox ml-3">
                                <input type="checkbox" class="custom-control-input" id="edit_hasExtraMarkets" name="has_extra_markets" value="1">
                                <label class="custom-control-label small font-weight-bold" for="edit_hasExtraMarkets">Habilitar no Site?</label>
                            </div>
                            <button type="button" class="btn btn-xs btn-primary btn-add-group-edit ml-auto"><i class="fas fa-plus mr-1"></i> Novo Grupo</button>
                        </div>
                        <!-- Preenchido via JS -->
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6 text-center border-right">
                             <img id="editPreviewHome" src="{{ asset('img/placeholders/shield.png') }}" class="img-fluid shadow-sm rounded mb-2" style="max-height: 80px; width: 80px; object-fit: contain;">
                             <div class="custom-file">
                                 <input type="file" name="home_flag" class="custom-file-input">
                                 <label class="custom-file-label text-left">Trocar Bandeira</label>
                             </div>
                        </div>
                        <div class="col-md-6 text-center">
                             <img id="editPreviewAway" src="{{ asset('img/placeholders/shield.png') }}" class="img-fluid shadow-sm rounded mb-2" style="max-height: 80px; width: 80px; object-fit: contain;">
                             <div class="custom-file">
                                 <input type="file" name="away_flag" class="custom-file-input">
                                 <label class="custom-file-label text-left">Trocar Bandeira</label>
                             </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-5 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .table thead th { border-top: 0; text-transform: uppercase; font-size: 0.85em; color: #666; }
    .custom-file-label::after { content: "Buscar"; }
    .badge { border-radius: 4px; font-weight: 600; }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
    });

    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + previewId).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Mostrar nome do arquivo no input custom-file e preview
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
        
        let preview = $(this).closest('.col-md-6').find('img');
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // AJAX para alternar status (Exibir)
    $('.btn-toggle-status').on('change', function() {
        let id = $(this).data('id');
        $.post(`/admin/partidas-personalizadas/${id}/toggle`, {
            _token: '{{ csrf_token() }}'
        }, function(res) {
            if(res.success) {
                toastr.success('Status da partida atualizado!');
            }
        });
    });

    // Abrir Modal de Edição e popular campos
    $('.btn-edit').on('click', function() {
        let data = $(this).data('json');
        let id = data.id;

        $('#formEditarPartida').attr('action', `/admin/partidas-personalizadas/${id}/update`);
        $('#edit_home_team').val(data.home_team);
        $('#edit_away_team').val(data.away_team);
        $('#edit_odd_home').val(data.odd_home);
        $('#edit_odd_draw').val(data.odd_draw);
        $('#edit_odd_away').val(data.odd_away);
        $('#edit_league_name').val(data.league_name);
        $('#edit_sport_name').val(data.category ? data.category.name : 'Futebol');
        
        // Formatar data e hora para campos separados
        let dateObj = new Date(data.start_time);
        let dateStr = dateObj.toISOString().split('T')[0];
        let timeStr = dateObj.toTimeString().split(' ')[0].substring(0, 5);
        
        $('#edit_start_date').val(dateStr);
        $('#edit_start_time_only').val(timeStr);
        $('#edit_isFeatured').prop('checked', data.is_featured == 1);

        // Previews
        if(data.home_flag) $('#editPreviewHome').attr('src', '/' + data.home_flag);
        else $('#editPreviewHome').attr('src', '{{ asset("img/placeholders/shield.png") }}');

        if(data.away_flag) $('#editPreviewAway').attr('src', '/' + data.away_flag);
        else $('#editPreviewAway').attr('src', '{{ asset("img/placeholders/shield.png") }}');

        $('#edit_hasExtraMarkets').prop('checked', data.has_extra_markets == 1);

        // Mercados Dinâmicos no Edit
        $('#editMarketGroupsContainer .market-group-item').remove();
        if (data.extra_markets && data.extra_markets.length > 0) {
            data.extra_markets.forEach(function(group, gIdx) {
                renderMarketGroup('#editMarketGroupsContainer', group, gIdx);
            });
        }

        $('#modalEditarPartida').modal('show');
    });

    // Funções para Grupos de Mercados
    let groupCounter = 0;

    function renderMarketGroup(container, groupData = null, index = null) {
        let idx = index !== null ? index : Date.now();
        let title = groupData ? groupData.group_name : '';
        
        let html = `
            <div class="market-group-item border rounded p-3 mb-3 bg-light shadow-sm" data-idx="${idx}">
                <div class="row mb-2">
                    <div class="col-10">
                        <input type="text" name="groups[${idx}][title]" class="form-control form-control-sm font-weight-bold" placeholder="Título do Grupo (ex: Dupla Chance)" value="${title}">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-group w-100"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="selections-container-${idx}">
                    <!-- Selections will be added here -->
                </div>
                <button type="button" class="btn btn-xs btn-outline-success mt-2 btn-add-selection" data-group="${idx}">
                    <i class="fas fa-plus mr-1"></i> Adicionar Seleção
                </button>
            </div>
        `;
        
        $(container).find('.empty-groups-msg').hide();
        $(container).append(html);

        if (groupData && groupData.selections) {
            groupData.selections.forEach(function(sel) {
                addSelectionRow(idx, sel.name, sel.odd);
            });
        } else {
            addSelectionRow(idx);
        }
    }

    function addSelectionRow(groupIdx, name = '', odd = '') {
        let sIdx = Date.now() + Math.floor(Math.random() * 1000);
        let html = `
            <div class="selection-row row no-gutters mb-1">
                <div class="col-8 pr-1">
                    <input type="text" name="groups[${groupIdx}][selections][${sIdx}][name]" class="form-control form-control-sm" placeholder="Nome (ex: Casa ou Empate)" value="${name}">
                </div>
                <div class="col-3 pr-1">
                    <input type="number" step="0.01" name="groups[${groupIdx}][selections][${sIdx}][odd]" class="form-control form-control-sm" placeholder="Odd" value="${odd}">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm text-danger btn-remove-selection"><i class="fas fa-minus-circle"></i></button>
                </div>
            </div>
        `;
        $(`.selections-container-${groupIdx}`).append(html);
    }

    $(document).on('click', '.btn-add-group', function() {
        renderMarketGroup('#marketGroupsContainer');
    });

    $(document).on('click', '.btn-add-group-edit', function() {
        renderMarketGroup('#editMarketGroupsContainer');
    });

    $(document).on('click', '.btn-add-selection', function() {
        let groupIdx = $(this).data('group');
        addSelectionRow(groupIdx);
    });

    $(document).on('click', '.btn-remove-group', function() {
        $(this).closest('.market-group-item').remove();
    });

    $(document).on('click', '.btn-remove-selection', function() {
        $(this).closest('.selection-row').remove();
    });
</script>
@stop
