@extends('adminlte::page')

@section('title', 'Validar PIN')

@section('content_header')
    <h1>Validar PIN (Pré-Bilhete)</h1>
@stop

@section('content')
<div class="container-fluid" id="app-validar-pin">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Informe o código do PIN</h3>
                </div>
                <div class="box-body">
                    <div class="input-group">
                        <input type="text" class="form-control input-lg text-center" 
                               placeholder="CÓDIGO DO PIN" 
                               v-model="pin" 
                               @keyup.enter="buscarPin"
                               style="font-weight: bold; text-transform: uppercase;">
                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-lg" type="button" @click="buscarPin" :disabled="loading">
                                <i v-if="loading" class="fa fa-spinner fa-spin"></i>
                                <i v-else class="fa fa-search"></i> PESQUISAR
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados do Bilhete -->
    <div class="row" v-if="bilhete" v-cloak>
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Detalhes do Pré-Bilhete</h3>
                    <span class="pull-right"><b>@{{ bilhete.codigo_bilhete }}</b></span>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td><b>Cliente:</b></td>
                                <td>@{{ bilhete.cliente }}</td>
                                <td><b>Data:</b></td>
                                <td>@{{ formatData(bilhete.created_at) }}</td>
                            </tr>
                            <tr>
                                <td><b>Apostado:</b></td>
                                <td class="text-green"><b>R$ @{{ formatMoney(bilhete.valor_apostado) }}</b></td>
                                <td><b>Retorno:</b></td>
                                <td class="text-orange"><b>R$ @{{ formatMoney(bilhete.retorno_possivel) }}</b></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="padding: 10px; background: #f9f9f9;">
                        <h4 style="margin-top: 0;">Jogos</h4>
                        <div v-for="p in bilhete.palpites" :key="p.id" style="padding: 10px; border-bottom: 1px solid #eee; background: #fff;">
                            <div class="row">
                                <div class="col-xs-9">
                                    <b>@{{ p.home_team }} x @{{ p.away_team }}</b><br>
                                    <small>@{{ p.market_name }} - @{{ p.selection_name }}</small>
                                </div>
                                <div class="col-xs-3 text-right">
                                    <span class="label label-warning">@{{ p.odd }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button class="btn btn-success btn-lg btn-block" @click="confirmarAposta" :disabled="submitting">
                        <i v-if="submitting" class="fa fa-spinner fa-spin"></i>
                        <i v-else class="fa fa-check"></i> VALIDAR APOSTA
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Erro -->
    <div class="row" v-if="error" v-cloak>
        <div class="col-md-6 col-md-offset-3">
            <div class="alert alert-danger">
                <h4><i class="icon fa fa-ban"></i> Erro</h4>
                @{{ error }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Sucesso -->
<div class="modal fade" id="modalSucesso" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <h4 class="modal-title">SUCESSO!</h4>
            </div>
            <div class="modal-body text-center">
                <h3>Bilhete Validado!</h3>
                <div style="padding: 20px; border: 2px dashed #333; margin: 20px 0;">
                    <p>CÓDIGO:</p>
                    <h1 style="font-weight: bold; font-size: 40px; margin: 0;">@{{ bilheteGerado.codigo_bilhete }}</h1>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <button class="btn btn-default btn-lg btn-block" @click="imprimirBilhete">
                            <i class="fa fa-print"></i> Imprimir
                        </button>
                    </div>
                    <div class="col-xs-6">
                        <button class="btn btn-success btn-lg btn-block" @click="whatsappBilhete">
                            <i class="fa fa-whatsapp"></i> WhatsApp
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">FECHAR</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    [v-cloak] { display: none; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script>
new Vue({
    el: '#app-validar-pin',
    data: {
        pin: '',
        loading: false,
        submitting: false,
        bilhete: null,
        bilheteGerado: {},
        error: null
    },
    methods: {
        buscarPin() {
            if (!this.pin) return;
            
            this.loading = true;
            this.error = null;
            this.bilhete = null;

            axios.post('/api/valida-cod', {
                codigo: this.pin,
                check_only: true
            })
            .then(response => {
                if (response.data.success) {
                    this.bilhete = response.data.bilhete;
                } else {
                    this.error = response.data.message || 'PIN não encontrado ou já validado.';
                }
            })
            .catch(error => {
                this.error = error.response?.data?.message || 'Erro ao buscar código PIN.';
            })
            .finally(() => {
                this.loading = false;
            });
        },

        confirmarAposta() {
            if (!this.bilhete) return;

            this.submitting = true;
            this.error = null;

            axios.post('/api/valida-cod', {
                codigo: this.pin
            })
            .then(response => {
                if (response.data.success) {
                    this.bilheteGerado = response.data.bilhete;
                    $('#modalSucesso').modal('show');
                    this.bilhete = null;
                    this.pin = '';
                } else {
                    this.error = response.data.message || 'Erro ao validar aposta.';
                }
            })
            .catch(error => {
                this.error = error.response?.data?.message || 'Erro de conexão com o servidor.';
            })
            .finally(() => {
                this.submitting = false;
            });
        },

        formatMoney(value) {
            return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        },
        formatOdds(value) {
            return parseFloat(value).toFixed(2);
        },
        formatData(date) {
            return moment(date).format('DD/MM/YYYY HH:mm');
        },
        imprimirBilhete() {
            window.open('/view-ticket/' + this.bilheteGerado.codigo_bilhete, '_blank');
        },
        whatsappBilhete() {
            const url = window.location.origin + '/view-ticket/' + this.bilheteGerado.codigo_bilhete;
            const text = encodeURIComponent('Confira sua aposta na IHUB BETS: ' + url);
            window.open('https://api.whatsapp.com/send?text=' + text, '_blank');
        }
    }
});
</script>
@stop
