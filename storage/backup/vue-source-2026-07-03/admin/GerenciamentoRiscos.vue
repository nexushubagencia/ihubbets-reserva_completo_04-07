<template>
    <div class="box box-primary">
        <div class="content">
            <div class="row">
                <!--Modal Bilhete (Recibo Premium)-->
                <div class="modal fade in" id="modal-bilhete" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
                    <div class="modal-dialog" style="max-width: 400px; margin: 30px auto;">
                        <div class="modal-content" v-for="palpite in palpites" :key="palpite.id" style="border: none; border-radius: 0; background-color: #FDF5D2; color: #333; font-family: 'Montserrat', sans-serif;">
                            
                            <!-- 🚀 CABEÇALHO TEAL -->
                            <div class="modal-header" style="background-color: #00ADEF; border: none; padding: 15px; text-align: center; border-radius: 0;">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 1; font-size: 20px; position: absolute; right: 15px; top: 15px;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" style="color: #fff; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: 1.5px; font-size: 22px;">
                                    {{ palpite.status }}
                                </h4>                          
                            </div>

                            <div class="modal-body" style="padding: 0;">
                                <!-- 🚀 LOGO PREMIUM -->
                                <div style="padding: 20px 0; text-align: center;">
                                    <img src="/img/logo.png" style="max-width: 180px; height: auto;">
                                    <h3 style="margin: 10px 0 0; font-weight: 900; color: #000; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">
                                        IHUB BETS
                                    </h3>
                                </div>

                                <!-- 🚀 DIVISOR DASHED -->
                                <div style="border-top: 1.5px dashed #BDB76B; width: 90%; margin: 0 auto 20px;"></div>

                                <div style="padding: 0 20px;">
                                    <h4 style="text-align: center; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; color: #444; letter-spacing: 1px;">
                                        {{ palpite.tipo }}
                                    </h4>

                                    <!-- 🚀 INFO GERAL -->
                                    <div style="font-size: 13px; line-height: 1.8; margin-bottom: 20px; color: #555;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>DATA</span>
                                            <b style="color: #000;">{{ palpite.created_at | formatDate() }}</b>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>VENDEDOR</span>
                                            <b style="color: #000;">{{ palpite.vendedor }}</b>
                                        </div>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>CLIENTE</span>
                                            <b style="color: #000;">{{ palpite.cliente }}</b>
                                        </div>
                                    </div>

                                    <!-- 🚀 DIVISOR DASHED -->
                                    <div style="border-top: 1.5px dashed #BDB76B; width: 100%; margin-bottom: 15px;"></div>

                                    <!-- 🚀 HEADER PALPITES -->
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 10px;">
                                        <span>EVENTO / MERCADO</span>
                                        <span>COTAÇÃO</span>
                                    </div>

                                    <!-- 🚀 LISTA DE JOGOS -->
                                    <div v-for="palp in palpite.palpites" :key="palp.id" style="margin-bottom: 25px;">
                                        <div style="font-size: 11px; color: #777; margin-bottom: 3px;">
                                            {{ palp.sport }} • {{ palp.match_temp | formatDate() }}
                                        </div>
                                        <div style="color: #D37D2A; font-weight: 800; font-size: 13px; text-transform: uppercase; margin-bottom: 4px;">
                                            {{ palp.league }}
                                        </div>
                                        <div style="font-weight: 900; font-size: 16px; color: #000; margin-bottom: 4px;">
                                            {{ palp.home }} X {{ palp.away }}
                                        </div>
                                        <div style="font-size: 12px; color: #666; font-style: italic; margin-bottom: 8px;">
                                            {{ palp.group_opp }}
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                                            <b style="font-size: 16px; color: #000;">{{ palp.palpite }}</b>
                                            <b style="font-size: 18px; color: #000;">{{ palp.cotacao | formatCotacao() }}</b>
                                        </div>

                                        <!-- 🚀 BARRA DE STATUS DO JOGO -->
                                        <div style="background-color: #00ADEF; color: #fff; text-align: center; font-weight: 900; font-size: 12px; padding: 4px 0; text-transform: uppercase; border-radius: 4px;">
                                            {{ palp.status }}
                                        </div>
                                    </div>

                                    <!-- 🚀 CÓDIGO PIN / CUPOM CENTRAL -->
                                    <div style="margin: 30px 0; text-align: center; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 15px 0;">
                                        <h1 style="font-weight: 900; font-size: 42px; margin: 0; color: #000; letter-spacing: 2px;">
                                            {{ palpite.cupom }}
                                        </h1>
                                    </div>

                                    <!-- 🚀 RESUMO FINANCEIRO -->
                                    <div style="padding-bottom: 20px;">
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                            <span>Quantidade de Jogos</span>
                                            <b>{{ bilhete_selecionado.total_palpites }}</b>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                            <span>Cotação Total</span>
                                            <b>{{ (bilhete_selecionado.cotacao || (bilhete_selecionado.retorno_possivel / bilhete_selecionado.valor_apostado)) | formatCotacao() }}</b>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                            <span>Valor Apostado</span>
                                            <b>{{ bilhete_selecionado.valor_apostado | formatMoeda() }}</b>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; padding: 15px 0; align-items: center;">
                                            <span style="font-weight: 800; font-size: 16px;">Retorno Possível</span>
                                            <b style="font-size: 22px; color: #28A745; font-weight: 900;">{{ bilhete_selecionado.retorno_possivel | formatMoeda() }}</b>
                                        </div>
                                    </div>

                                    <!-- 🚀 BOTÕES DE AÇÃO -->
                                    <div style="padding: 10px 0 20px; display: flex; flex-direction: column; gap: 10px;">
                                        <a :href="link" target="_blank" class="btn btn-success btn-block" style="background-color: #25D366; border: none; font-weight: 800; padding: 12px; border-radius: 8px; text-transform: uppercase;">
                                            <i class="fa fa-whatsapp"></i> Compartilhar WhatsApp
                                        </a>
                                        <button class="btn btn-secondary btn-block" data-dismiss="modal" style="font-weight: 700; padding: 10px; border-radius: 8px;">
                                            Fechar
                                        </button>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               <!--End Modal Bilhete-->
                
               <div class="col-md-4">
                      <div class="form-group">
                        <label>Escolha uma Opção:</label>
                         <select class="form-control" v-model="opcao_risco" @change="searchGerente(opcao_risco)">
                            <option>Possível Retorno</option>
                            <option>Quantida de Bilhetes</option>
                            <option>Valor Apostado</option>
                            <option>Quantidade de Apostas em Aberto</option>
                            <option>Quntidade de Apostas no Bilhete</option>
                        </select>

                       
                    </div>
               </div>
               
            </div>

            <div class="row">
                <div class="col-md-12">
                        <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="tabela-class" >
                                        <tr class="header-tabela">
                                            <th class="tabela-class">CUPOM</th>
                                            <th class="tabela-class">VALOR APOSTADO</th>
                                            <th class="tabela-class">POSSÍVEL RETORNO</th>
                                            <th class="tabela-class">APOSTAS EM ABERTO</th>
                                            <th class="tabela-class">CONFERIR BILHETE</th>
                                            
                                        </tr>
                                    </thead>
                                    
                                    <tbody class="tabela-class">
                                   
                                        <tr class="tbody-table" v-for="bilhete in bilhetes" :key="bilhete.id">
                                            <td class="body-cupom"><b>{{bilhete.cupom}}</b></td>
                                            <td><b>{{bilhete.valor_apostado | formatMoeda()}}</b></td>
                                            <td><b>{{bilhete.retorno_possivel  | formatMoeda()}}</b></td>
                                            <td><b>{{bilhete.andamento_palpites}}/{{bilhete.total_palpites}}</b></td>
                                            <td><button class="btn btn-success" @click="viewBilhete(bilhete.id, bilhete.status, bilhete.cupom, bilhete.created_at, bilhete.vendedor, bilhete.cliente, bilhete.total_palpites, bilhete.cotacao, bilhete.valor_apostado, bilhete.retorno_possivel,bilhete.tipo )"><i class="fa fa-tags"></i></button></td>
                                        </tr>
                                </tbody>
                                    </table>
                                    <scale-loader :loading="loading"></scale-loader>
                        </div>
                </div>
            </div>
            
         </div>
    </div>
</template>
<style>
    .header-tabela {
        background: #00466A;
        color:#FFF;
        font-size: 13px;
        text-align: center;
    }
    .tbody-table {
        text-align: center;
        padding-bottom: 5px;
    }
    .body-cupom{
        color: red;
    }
    .body-bilhete {
        background:#F8ECC2;
        color: #000;   
    }
    .title-bilhete {
        text-align: center;
    }
    .tipo-aposta {
        text-align: center;
        font-size: 16px;
        border-bottom: #000 1px dashed;
        padding-bottom: 8px;
    }
    .info-aposta-header {
        padding: 5px;
        border-bottom: #000 1px dashed;
        padding-bottom: 8px;
  
    }
    .info-aposta-header p {
        margin-bottom: 0px;
    }
    .aposta-footer {
        margin-bottom: 0px;
    }
    .header-palpite {
        width: 100%;
        height: 22px;
        margin-bottom: 20px;
        border-bottom:  1px  #000 dashed;
        font-size: 15px;
        padding-bottom: 9px;
    }
    .palpite-left {
      width: 49%;
      float: left;
      text-align: left;

    }
    .palpite-right {
        width: 49%;
        float: right;
        text-align: right;
    }
    .body-palpite {
        width: 100%;
        height: 170px;;
        margin-bottom: 20px;
        border-bottom:  1px  #000 dashed;
        font-size: 15px;
        padding-bottom: 9px;
    }
    .body-palpite p {
        margin-bottom: 0px;
    }
    .body-palpite-left {
      width: 49%;
      float: left;
      text-align: left;

    }
    .body-palpite-right {
        width: 49%;
        float: right;
        text-align: right;
    }
    .aposta-footer {
        width: 100%;
        height: 100px;
        margin-bottom: 20px;
        border-top: 1px  #000 dashed;
        border-bottom:  1px  #000 dashed;
    }
    .info-aposta-footer-left p {
        margin-bottom: 0px;
        
    }
    .info-aposta-footer-left {
      width: 49%;
      height: auto;
      float: left;
      text-align: left;
      font-size: 15px;
    }
    .info-aposta-footer-right p {
        margin-bottom: 0px;
    }
    .info-aposta-footer-right {
        width: 49%;
        height: auto;
        float: right;
        text-align: right;
        font-size: 15px;
    }
    .cupom-bilhete {
        text-align: center;
    }


    .Aberto {
        background: #00C0EF;
        color: #FFF;
    }
    .Perdeu {
        background: #FF0000;
        color: #FFF;
    }
    .Ganhou {
        background: #008D4C;
        color: #FFF;
    }
    .Cancelado {
        background: #E69222;
        color: #FFF;
    }

    .Devolvido {
        background: #331E1B;
        color: #FFF;
    }
</style>

<script>
export default {
    created() {
         this.searchGerente('Possível Retorno');
    },
    data() {
        return {
            bilhetes: [],
            palpites: [],
            bilhete_selecionado: {},
            link: '',
            bilheteCupm: '',
            opcao_risco: 'Possível Retorno',
            loading: true,
        }
    },
    filters: {
            formatDate(date) {
                return moment(date).format('DD/MM HH:mm');
            },
            formatDate2(date) {
                return moment(date).format('DD/MM hh:mm');
            },
             formatCotacao(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] =  numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
            formatMoeda(numero) {
                var numero = numero.toFixed(2).split('.');
                numero[0] = "R$ " + numero[0].split(/(?=(?:...)*$)/).join('.');
                return numero.join(',');
            },
            andamentoPalp(acertos, erros){


            },
    },
    methods:{
        
        searchGerente(opcao) {
            this.bilhetes = [];
            this.loading = true;
            axios.post('/admin/list-bilhete-risco',{opcao:opcao})
                    .then((response)=>{
                        
                            this.bilhetes = response.data;
                    })
                    .catch(error => {
                        console.log(error)
                    })
                    .finally(()=>{
                        this.loading = false;
                    })
        },
        viewBilhete(id, status, cupom, created_at, vendedor, cliente, total_palpites, cotacao, valor_apostado, retorno_possivel, tipo) {
            this.bilhete_selecionado = {
                id, status, cupom, created_at, vendedor, cliente, total_palpites, cotacao, valor_apostado, retorno_possivel, tipo
            };
            
            // 🚀 LINK WHATSAPP
            this.link = "https://api.whatsapp.com/send?text=" + encodeURIComponent("Confira meu bilhete: " + window.location.origin + "/acompanhar?c=" + cupom);

            $('#modal-bilhete').modal('show');
            this.palpites = []
            axios.get('/admin/palpites-bilhete/'+id)
                    .then((response)=>{
                        this.palpites = response.data
                    })
                        .catch((err) =>{
                            console.log(err)
                        })
                        .finally(()=>{
                            
                        })

                

                

            //Carrega os Palpites

    },
        
    }
}
</script>
