{{-- resources/views/admin/banners/generator.blade.php --}}
@extends('adminlte::page')

@section('title', 'Gerador de Banners')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h3><i class="fas fa-rocket fa-sm"></i> Gerador de Banners
                <small class="lead">Banners promocionais da página inicial</small>
            </h3>
        </div>
        <div class="col-sm-6">
            
        </div>
    </div>
@stop

@section('content')
<div id="geradorApp">
<div class="row">

  {{-- ═══ PAINEL ESQUERDO: Configurações ══════════════════════════ --}}
  <div class="col-md-5">
    <div class="card card-secondary card-outline">
      <div class="card-header">
        <div class="card-title"><i class="fa fa-sliders-h mr-1"></i> Configurações</div>
        <div class="card-tools">
          <select v-model="selectedTemplateId" @change="loadTemplate"
                  class="form-control form-control-sm" style="width:160px;display:inline-block;">
            <option value="">🎨 Template padrão</option>
            <option v-for="t in templates" :key="t.id" :value="t.id">@{{ t.name }}</option>
          </select>
        </div>
      </div>
      <div class="card-body">

        {{-- Dia --}}
        <div class="form-group">
          <label>Dia dos eventos</label>
          <select v-model="form.day" @change="loadEvents" class="form-control">
            <option value="today">Hoje</option>
            <option value="tomorrow">Amanhã</option>
            <option value="after_tomorrow">Depois de amanhã</option>
          </select>
        </div>

        {{-- Esporte --}}
        <div class="form-group">
          <label>Esporte</label>
          <select v-model="form.sport" @change="loadEvents" class="form-control">
            <option>Futebol</option><option>Luta</option>
            <option>Basquete</option><option>Vôlei</option><option>Tênis</option>
          </select>
        </div>

        {{-- Instagram --}}
        <div class="form-group">
          <label>Instagram</label>
          <div class="input-group">
            <div class="input-group-prepend"><span class="input-group-text">@</span></div>
            <input v-model="form.instagram" type="text" placeholder="seuinstagram" class="form-control">
          </div>
        </div>

        {{-- Título --}}
        <div class="form-group">
          <label>Título do banner</label>
          <input v-model="form.title" type="text" placeholder="HOJE É O DIA PRA VENCER!" class="form-control">
        </div>

        {{-- Liga + Evento --}}
        <div class="row">
          <div class="col-md-5">
            <div class="form-group">
              <label>Liga</label>
              <select v-model="selectedLeague" @change="filterEvents" class="form-control form-control-sm">
                <option value="">Todas as ligas</option>
                <option v-for="l in leagues" :key="l" :value="l">@{{ l }}</option>
              </select>
            </div>
          </div>
          <div class="col-md-5">
            <div class="form-group">
              <label>Evento</label>
              <select v-model="selectedEvent" class="form-control form-control-sm">
                <option value="">Selecione...</option>
                <option v-for="e in filteredEvents" :key="e.id" :value="e.id">
                  @{{ e.home }} x @{{ e.away }} — @{{ e.date_fmt }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-2 d-flex align-items-end pb-3">
            <button @click="addGame" class="btn btn-primary btn-block btn-sm" :disabled="!selectedEvent">
              <i class="fa fa-plus"></i>
            </button>
          </div>
        </div>

        {{-- Jogos adicionados --}}
        <div v-if="form.games.length" class="table-responsive">
          <table class="table table-sm table-striped">
            <thead>
              <tr>
                <th class="text-right" style="width:35%">Casa</th>
                <th class="text-center" style="width:5%">X</th>
                <th style="width:35%">Fora</th>
                <th class="text-center" style="width:18%">Odds</th>
                <th style="width:7%"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(g, i) in form.games" :key="i">
                <td class="text-right align-middle">
                  <img v-if="g.teamALogo" :src="g.teamALogo" width="22" height="22" class="mr-1" style="border-radius:50%;object-fit:cover;">
                  <small>@{{ g.teamA }}</small>
                </td>
                <td class="text-center align-middle"><small>x</small></td>
                <td class="align-middle">
                  <img v-if="g.teamBLogo" :src="g.teamBLogo" width="22" height="22" class="mr-1" style="border-radius:50%;object-fit:cover;">
                  <small>@{{ g.teamB }}</small>
                </td>
                <td class="text-center align-middle" style="font-size:11px;line-height:1.3;">
                  <span class="text-success font-weight-bold">@{{ g.oddHome }}</span> /
                  <span class="text-warning font-weight-bold">@{{ g.oddDraw }}</span> /
                  <span class="text-info font-weight-bold">@{{ g.oddAway }}</span>
                </td>
                <td class="align-middle">
                  <button @click="removeGame(i)" class="btn btn-danger btn-xs">
                    <i class="fa fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center text-muted py-3" style="font-size:13px;">
          <i class="fa fa-info-circle mr-1"></i> Adicione jogos usando o seletor acima
        </div>

        {{-- Botões de ação --}}
        <button @click="generate" class="btn btn-success btn-block mt-3"
                :disabled="!form.games.length || generating">
          <i class="fa fa-image mr-1"></i>
          <span v-if="generating"><i class="fa fa-spinner fa-spin mr-1"></i> Gerando...</span>
          <span v-else>Gerar banner</span>
        </button>

        <button v-if="generated" @click="download" class="btn btn-warning btn-block mt-2">
          <i class="fa fa-download mr-1"></i> Download PNG
        </button>

        <button v-if="generated" @click="applyGlobal" class="btn btn-navy btn-block mt-2" 
                style="background:#001f3f;color:#fff;" :disabled="applyingGlobal">
          <i class="fa fa-globe mr-1"></i>
          <span v-if="applyingGlobal"><i class="fa fa-spinner fa-spin mr-1"></i> Aplicando...</span>
          <span v-else>Aplicar em todas as Bancas</span>
        </button>

      </div>
    </div>
  </div>

  {{-- ═══ PAINEL DIREITO: Preview ═══════════════════════════════════ --}}
  <div class="col-md-7">
    <div class="card card-secondary card-outline">
      <div class="card-header">
        <div class="card-title"><i class="fa fa-image mr-1"></i> Banner gerado</div>
        <div class="card-tools" v-if="generated">
          <button @click="copy" class="btn btn-sm btn-secondary">
            <i class="fa fa-copy mr-1"></i> Copiar
          </button>
        </div>
      </div>
      <div class="card-body text-center" style="min-height:400px;background:#1a1a1a;border-radius:4px;">
        <div v-if="!generated && !generating" class="d-flex align-items-center justify-content-center h-100 text-muted" style="min-height:380px;flex-direction:column;">
          <i class="fa fa-image fa-4x mb-3" style="opacity:.2;"></i>
          <p>Configure os jogos e clique em <strong>Gerar banner</strong></p>
        </div>
        <div v-if="generating" class="d-flex align-items-center justify-content-center" style="min-height:380px;">
          <div class="text-center text-light">
            <i class="fa fa-spinner fa-spin fa-3x mb-3" style="color:#22c55e;"></i>
            <p>Gerando seu banner...</p>
          </div>
        </div>
        <img v-show="generated" ref="previewImg" id="bannerPreview"
             style="max-width:100%;border-radius:6px;box-shadow:0 4px 24px rgba(34,197,94,.25);"
             alt="Banner">
      </div>
    </div>
  </div>

</div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/banner-engine.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.min.js"></script>
<script>
new Vue({
    el: '#geradorApp',

    data: {
        form: {
            day      : 'today',
            sport    : 'Futebol',
            instagram: '{{ $siteInstagram ?? '' }}',
            title    : 'HOJE É O DIA PRA VENCER! Apostou, Acertou, Ganhou!',
            games    : [],
        },
        selectedLeague   : '',
        selectedEvent    : '',
        selectedTemplateId: '',
        allEvents        : [],
        leagues          : [],
        filteredEvents   : [],
        templates        : [],
        activeTemplate   : {},
        generating       : false,
        generated        : false,
        applyingGlobal   : false,
        _lastCanvas      : null,
    },

    mounted() {
        this.loadEvents();
        this.loadTemplates();
    },

    methods: {
        /* ── carregar eventos via API ── */
        loadEvents() {
            this.allEvents = []; this.leagues = []; this.filteredEvents = [];
            fetch(`/api/events?day=${this.form.day}&sport=${this.form.sport}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    this.allEvents = data.events || data || [];
                    this.leagues = [...new Set(this.allEvents.map(e => e.league || e.categoria))].filter(Boolean).sort();
                    this.filteredEvents = this.allEvents;
                })
                .catch(() => {
                    // Sem eventos disponíveis — estado vazio
                    this.allEvents = [];
                    this.filteredEvents = [];
                });
        },

        filterEvents() {
            this.filteredEvents = this.selectedLeague
                ? this.allEvents.filter(e => (e.league || e.categoria) === this.selectedLeague)
                : this.allEvents;
            this.selectedEvent = '';
        },

        /* ── adicionar jogo à lista ── */
        addGame() {
            if (!this.selectedEvent) return;
            const ev = this.allEvents.find(e => e.id == this.selectedEvent);
            if (!ev) return;
            if (this.form.games.find(g => g.id == ev.id)) {
                toastr.warning('Esse jogo já foi adicionado.'); return;
            }
            if (this.form.games.length >= 6) {
                toastr.warning('Máximo de 6 jogos por banner.'); return;
            }
            this.form.games.push({
                id       : ev.id,
                teamA    : ev.home || ev.home_team,
                teamB    : ev.away || ev.away_team,
                teamALogo: ev.logo_home || ev.teamALogo || null,
                teamBLogo: ev.logo_away || ev.teamBLogo || null,
                oddHome  : ev.odd_home || ev.oddHome || '-',
                oddDraw  : ev.odd_draw || ev.oddDraw || '-',
                oddAway  : ev.odd_away || ev.oddAway || '-',
                dateTime : ev.date_fmt || ev.dateTime || '',
                league   : ev.league || ev.categoria || '',
            });
        },

        removeGame(i) { this.form.games.splice(i, 1); },

        /* ── templates ── */
        loadTemplates() {
            fetch('/admin/banner-templates?type=multi', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(d => { this.templates = d.data || []; })
                .catch(() => {});
        },

        loadTemplate() {
            if (!this.selectedTemplateId) { this.activeTemplate = {}; return; }
            fetch(`/admin/banner-templates/${this.selectedTemplateId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(d => { this.activeTemplate = d; })
                .catch(() => {});
        },

        /* ── GERAR BANNER ── */
        async generate() {
            if (!this.form.games.length) return;
            this.generating = true;
            this.generated  = false;

            const data = {
                siteLogo : '{{ $siteLogo ?? asset("images/logo.png") }}',
                siteUrl  : '{{ $siteUrl ?? config("app.url") }}',
                instagram: this.form.instagram ? '@' + this.form.instagram.replace('@','') : '',
                title    : this.form.title,
                games    : this.form.games,
            };

            try {
                const canvas = await window.BannerEngine.generateMultiGame(data, this.activeTemplate);
                this._lastCanvas = canvas;
                this.$refs.previewImg.src = window.BannerEngine.toDataURL(canvas);
                this.generated = true;
                toastr.success('Banner gerado com sucesso!', '', {timeOut: 2000});
            } catch (e) {
                toastr.error('Erro ao gerar banner: ' + e.message);
                console.error(e);
            } finally {
                this.generating = false;
            }
        },

        download() {
            if (!this._lastCanvas) return;
            window.BannerEngine.download(this._lastCanvas, 'banner-apostas.png');
        },

        async copy() {
            if (!this._lastCanvas) return;
            try {
                const blob = await new Promise(r => this._lastCanvas.toBlob(r, 'image/png'));
                await navigator.clipboard.write([new ClipboardItem({'image/png': blob})]);
                toastr.success('Imagem copiada para a área de transferência!');
            } catch {
                toastr.warning('Navegador não suporta cópia direta. Use o Download.');
            }
        },

        async applyGlobal() {
            if (!this._lastCanvas) return;
            
            const confirmed = await Swal.fire({
                title: 'Confirmar Replicação?',
                text: "Este banner será aplicado a TODAS as bancas do sistema imediatamente.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, aplicar!'
            });

            if (!confirmed.isConfirmed) return;

            this.applyingGlobal = true;
            const imageData = window.BannerEngine.toDataURL(this._lastCanvas);

            fetch('/admin/master/marketing/apply-global', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ image: imageData })
            })
            .then(r => r.json())
            .then(res => {
                if(res.status === 'success') {
                    Swal.fire('Sucesso!', res.message, 'success');
                } else {
                    Swal.fire('Erro', res.message, 'error');
                }
            })
            .catch(e => Swal.fire('Erro', 'Falha na conexão com o servidor', 'error'))
            .finally(() => {
                this.applyingGlobal = false;
            });
        }
    }
});
</script>
@stop

@section('css')
<style>
  .btn-xs { padding: 2px 6px; font-size: 11px; }
  #geradorApp .card-body { padding: 16px; }
  #geradorApp .table td, #geradorApp .table th { vertical-align: middle; padding: 5px 8px; font-size: 12px; }
</style>
@stop
