<template>
  <div class="wrapper">
    <notifications group="foo" />
    <!--Dados logado-->
    <div class="modal fade" id="modal-login">
      <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
          <div class="modal-header" style="border-bottom: none; padding: 15px 15px 0;">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              style="opacity: 0.5; outline: none;"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
          </div>

          <div class="modal-body" style="padding: 0 30px 30px;">
            <div class="text-center" style="margin-bottom: 25px;">
              <img :src="server.logo_img" style="max-width: 150px; max-height: 120px; margin-bottom: 15px;" alt="Logo">
              <h3 style="margin: 0; font-weight: 800; color: #333; font-family: 'Antipasto', Helvetica; text-transform: uppercase; font-size: 24px;">
                {{ server.logo.split(" - ")[0] }}
              </h3>
              <p style="color: #888; font-size: 14px; margin-top: 5px;">Iniciar sua sessão</p>
            </div>

            <div class="login-box-body" style="padding: 0; background: transparent;">
              <div
                class="alert alert-danger alert-dismissible"
                v-if="errorLogin"
                style="padding: 8px; font-size: 13px; border-radius: 4px;"
              >
                {{ messageError }}
              </div>

              <div class="form-group has-feedback" style="margin-bottom: 15px;">
                <input
                  type="text"
                  class="form-control"
                  v-model="username"
                  placeholder="Login"
                  style="height: 45px; border-radius: 4px; border: 1px solid #ddd; background: var(--container_jogos--color, #f9f9f9); padding-right: 40px;"
                />
                <span
                  class="fa fa-envelope form-control-feedback"
                  style="line-height: 45px; color: #777;"
                ></span>
              </div>
              <div class="form-group has-feedback" style="margin-bottom: 10px;">
                <input
                  type="password"
                  v-model="password"
                  @keyup.enter="login()"
                  class="form-control"
                  placeholder="Senha"
                  style="height: 45px; border-radius: 4px; border: 1px solid #ddd; background: var(--container_jogos--color, #f9f9f9); padding-right: 40px;"
                />
                <span
                  class="fa fa-lock form-control-feedback"
                  style="line-height: 45px; color: #777;"
                ></span>
              </div>

              <div style="margin-bottom: 20px;">
                <a href="password/reset" style="color: #333; font-size: 14px; font-weight: 500;">Esqueceu a senha?</a>
              </div>

              <button
                class="btn btn-block"
                style="background-color: var(--sidebar--color) !important; color: #fff !important; font-weight: bold; height: 50px; font-size: 18px; border-radius: 4px; border: none; text-transform: capitalize;"
                @click="login()"
              >
                {{ text_btn_login }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--End Modal -->

    <!-- Modal Cadastro -->
    <div id="modal-register" aria-modal="true" role="dialog" class="modal fade">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header" style="color: #333 !important;">
            <button type="button" data-dismiss="modal" aria-label="Close" class="close" style="color: #333 !important; opacity: 1;">
              <span aria-hidden="true"><i class="fa fa-close"></i></span>
            </button> 
            <h4 class="modal-title" style="color: #333 !important;">
              <i class="fa fa-user-plus" style="margin-right: 5px; color: #333 !important;"></i>
              <strong style="color: #333 !important;">CADASTRO DE USUÁRIO</strong>
            </h4>
          </div> 
          <div class="modal-body">
            <div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center">Nome Completo *</label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-user"></i></span> 
                      <input tabindex="1" type="text" placeholder="Nome Completo *" class="form-control" v-model="formRegister.nome">
                    </div> 
                  </div>
                </div> 
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Nome de usuário *</label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-tag"></i></span> 
                      <input tabindex="2" type="text" placeholder="Nome de usuário" class="form-control" v-model="formRegister.username">
                    </div> 
                  </div>
                </div>
              </div> 
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Criar senha *</label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                      <input tabindex="3" autocomplete="new-password" type="password" placeholder="Criar senha" class="form-control" v-model="formRegister.password">
                    </div> 
                  </div>
                </div> 
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Confirmar senha *</label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                      <input tabindex="4" type="password" placeholder="Confirmar senha" class="form-control" v-model="formRegister.password_confirmation">
                    </div> 
                  </div>
                </div>
              </div> 
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">CPF * <small>(Seu CPF será sua chave Pix)</small></label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-id-card"></i></span> 
                      <input tabindex="5" placeholder="CPF" class="form-control" v-model="formRegister.cpf">
                    </div> 
                  </div>
                </div> 
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Contato *</label> 
                    <div class="d-flex" style="display: flex;">
                      <select class="form-control" style="width: 30%;">
                        <option value="55">+55</option>
                      </select> 
                      <input tabindex="6" placeholder="Celular" class="form-control" style="width: 70%;" v-model="formRegister.telefone">
                    </div> 
                  </div>
                </div>
              </div> 
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Data de nascimento *</label> 
                    <small class="visible-xs text-center" style="margin-left: 3%;">Data de nascimento *</small> 
                    <div class="input-group" style="display: flex;">
                      <input placeholder="dia" class="form-control col-md-4" style="width: 30%;" v-model="formRegister.dia"> 
                      <select class="form-control col-md-4" style="width: 40%;" v-model="formRegister.mes">
                        <option value="">Mês</option> 
                        <option value="1">Janeiro</option> 
                        <option value="2">Fevereiro</option> 
                        <option value="3">Março</option> 
                        <option value="4">Abril</option> 
                        <option value="5">Maio</option> 
                        <option value="6">Junho</option> 
                        <option value="7">Julho</option> 
                        <option value="8">Agosto</option> 
                        <option value="9">Setembro</option> 
                        <option value="10">Outubro</option> 
                        <option value="11">Novembro</option> 
                        <option value="12">Dezembro</option>
                      </select> 
                      <input placeholder="ano" class="form-control col-md-4" style="width: 30%;" v-model="formRegister.ano">
                    </div> 
                  </div>
                </div> 
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Email *</label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-envelope"></i></span> 
                      <input tabindex="8" type="email" placeholder="E-mail" class="form-control" v-model="formRegister.email">
                    </div> 
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Tipo Chave Pix *</label> 
                    <select class="form-control" v-model="formRegister.pix_key_type">
                      <option value="CPF">CPF</option>
                      <option value="EMAIL">E-mail</option>
                      <option value="TELEFONE">Telefone</option>
                      <option value="ALEATORIA">Chave Aleatória</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label class="hidden-xs text-center" style="margin-left: 3%;">Chave Pix para Recebimento *</label> 
                    <div class="input-group">
                      <span class="input-group-addon"><i class="fa fa-key"></i></span> 
                      <input type="text" placeholder="Sua chave Pix" class="form-control" v-model="formRegister.pix_key">
                    </div> 
                  </div>
                </div>
              </div>
            </div> 
            <div style="margin-top: 15px; margin-bottom: 2%; font-size: 14px;">
              <label style="cursor: pointer; font-weight: normal; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" v-model="formRegister.termos" style="width: 20px; height: 20px; cursor: pointer !important; pointer-events: all !important;"> 
                <span>Certifico que tenho mais de 18 anos de idade e declaro que li e concordo com os <a class="cursor-pointer" @click.stop="loadRegulamento()">termos de uso do site</a></span>
              </label>
            </div>
          </div> 
          <div class="modal-footer d-flex justify-content-center" style="text-align: center;">
            <button class="btn btn-success col-md-6 col-md-offset-3 col-xs-12" 
                    style="font-size: 20px; font-weight: bold; margin-top: 10px;" 
                    @click="submitRegister()">
              Registrar-se
            </button>
          </div>
        </div>
      </div>
    </div> 
    <!-- End Modal Cadastro -->

    <!-- Modal Depósito -->
    <div id="modal-deposit" class="modal fade">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close"><span>&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-money"></i> DEPOSITAR VIA PIX</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Valor do Depósito (R$)</label>
              <input type="number" v-model="depositAmount" class="form-control" placeholder="Ex: 20.00">
            </div>
            <div class="row">
              <div class="col-xs-4" v-for="val in [10, 20, 50, 100, 200, 500]" :key="val" style="margin-bottom: 5px;">
                <button class="btn btn-default btn-block btn-xs" @click="depositAmount = val">R$ {{val}}</button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success btn-block" @click="submitPix()" :disabled="loadingPix">
              <i class="fa fa-qrcode"></i> GERAR QR CODE
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Display PIX -->
    <div id="modal-pix-display" class="modal fade">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close"><span>&times;</span></button>
            <h4 class="modal-title">PAGAMENTO PIX</h4>
          </div>
          <div class="modal-body text-center">
            <p>Escaneie o QR Code abaixo para pagar:</p>
            <img v-if="pixData.qr_code_base64" :src="'data:image/png;base64,' + pixData.qr_code_base64" style="max-width: 250px; margin: 10px auto; display: block;">
            
            <div class="form-group" style="margin-top: 20px;">
              <label>PIX Copia e Cola:</label>
              <div class="input-group">
                <input type="text" readonly :value="pixData.qr_code" class="form-control">
                <span class="input-group-btn">
                  <button class="btn btn-primary" @click="copyPix()">COPIAR</button>
                </span>
              </div>
            </div>
            <div class="alert alert-info">
              <i class="fa fa-info-circle"></i> O saldo será adicionado automaticamente após a confirmação.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Saque -->
    <div id="modal-withdrawal" class="modal fade">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close"><span>&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-university"></i> SOLICITAR SAQUE</h4>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning" style="font-size: 12px;">
              <i class="fa fa-info-circle"></i> O valor será enviado para sua chave Pix cadastrada.
            </div>
            <div class="form-group">
              <label>Valor do Saque (R$)</label>
              <input type="number" v-model="withdrawalAmount" class="form-control" placeholder="Mínimo R$ 20.00">
            </div>
            <p v-if="caixaUser" style="font-weight: bold;">Saldo disponível: R$ {{caixaUser.balance}}</p>

            <hr>
            <h5 style="font-weight: bold;"><i class="fa fa-history"></i> Meus Saques Recentes</h5>
            <div class="table-responsive">
              <table class="table table-condensed" style="font-size: 11px; color: #333 !important;">
                <thead>
                  <tr style="background: var(--container_jogos--color, #f4f4f4);">
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="w in withdrawalHistory" :key="w.id">
                    <td>{{ w.created_at | formatDateShort }}</td>
                    <td>R$ {{ w.amount }}</td>
                    <td>
                      <span v-if="w.status == 'pending'" class="label label-warning">Pendente</span>
                      <span v-if="w.status == 'approved'" class="label label-success">Pago</span>
                      <span v-if="w.status == 'rejected'" class="label label-danger">Recusado</span>
                    </td>
                  </tr>
                  <tr v-if="withdrawalHistory.length == 0">
                    <td colspan="3" class="text-center">Nenhum saque solicitado.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary btn-block" @click="submitWithdrawal()" :disabled="loadingWithdrawal">
              SOLICITAR SAQUE
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Bônus -->
    <div id="modal-bonus" class="modal fade">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close"><span>&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-gift"></i> ATIVAR BÔNUS</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Código Promocional</label>
              <input type="text" v-model="promoCode" class="form-control" placeholder="Digite seu código">
            </div>
            
            <div v-if="bonusData" class="alert alert-info" style="color: #31708f !important;">
              <h5 style="margin-top: 0;"><b>Bônus Ativo:</b></h5>
              <p>Saldo: R$ {{bonusData.current_balance}}</p>
              <p>Rollover: {{bonusData.current_rollover}} / {{bonusData.target_rollover}}</p>
              <div class="progress progress-xs" style="margin-bottom: 0;">
                <div class="progress-bar progress-bar-success" :style="'width: ' + (bonusData.current_rollover / bonusData.target_rollover * 100) + '%'"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success btn-block" @click="applyPromoCode()" :disabled="loadingBonus">
              ATIVAR CÓDIGO
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Minha Conta -->
    <div id="modal-account" class="modal fade">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" class="close"><span>&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-user"></i> MINHA CONTA</h4>
          </div>
          <div class="modal-body" style="color: #333 !important;">
            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_perfil" data-toggle="tab">Perfil</a></li>
                <li><a href="#tab_senha" data-toggle="tab">Alterar Senha</a></li>
              </ul>
              <div class="tab-content" style="padding: 15px;">
                <div class="tab-pane active" id="tab_perfil">
                  <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" readonly :value="name" class="form-control">
                  </div>
                  <div class="form-group" v-if="caixaUser">
                    <label>CPF:</label>
                    <input type="text" readonly :value="maskSensitiveData(caixaUser.cpf)" class="form-control">
                  </div>
                  <div class="form-group" v-if="caixaUser">
                    <label>Chave PIX:</label>
                    <input type="text" readonly :value="maskSensitiveData(caixaUser.pix_key)" class="form-control">
                    <small>Tipo: {{caixaUser.pix_key_type}}</small>
                  </div>
                </div>
                <div class="tab-pane" id="tab_senha">
                  <div class="form-group">
                    <label>Nova Senha:</label>
                    <input type="password" v-model="formPassword.password" class="form-control" placeholder="Mínimo 6 caracteres">
                  </div>
                  <div class="form-group">
                    <label>Confirmar Senha:</label>
                    <input type="password" v-model="formPassword.password_confirmation" class="form-control">
                  </div>
                  <button class="btn btn-primary btn-block" @click="changePassword()" :disabled="loadingPassword">
                    <i class="fa fa-refresh" v-if="loadingPassword"></i> ATUALIZAR SENHA
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modal-caixa" v-show="logado">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-money"></i>
              <b>{{ name }}</b>
            </h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="row">
              <!-- <scale-loader :loading="loadingCaixa"></scale-loader> -->
              <div class="col-md-12">
                <div class="valor-fechamento-positivo">
                  Quantidade de Bilhetes: {{ caixaUser.quantidade }}
                </div>
                <div class="valor-fechamento-positivo">
                  Apostas no Ponto: {{ caixaUser.entradas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-total-aberto">
                  Apostas Aguardando :
                  {{ caixaUser.entradas_abertas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-total-negativo">
                  Total Prêmios: {{ caixaUser.saidas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-positivo">
                  Adiantamentos: {{ caixaUser.lancamentos | formatMoeda() }}
                </div>
                <div class="valor-fechamento-positivo">
                  Comissões: {{ caixaUser.comissoes | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-positivo"
                  v-if="caixaUser.total >= 0"
                >
                  Total: {{ caixaUser.total | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-negativo"
                  v-if="caixaUser.total < 0"
                >
                  Total: {{ caixaUser.total | formatMoeda() }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal Caixa-->

    <div class="modal fade" id="modal-relatorio" v-show="logado">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-money"></i>
              <b>Relatório</b>
            </h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="form-inline relatorio">
              <div class="form-group">
                <label>De:</label>

                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input
                    type="date"
                    v-model="date1"
                    class="form-control pull-right"
                    id="datepicker-start"
                    @change="sendRelatorio()"
                  />
                </div>
                <!-- /.input group -->
                <label>Até:</label>

                <div class="input-group date">
                  <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <input
                    type="date"
                    v-model="date2"
                    class="form-control pull-right"
                    id="datepicker-end"
                    @change="sendRelatorio()"
                  />
                </div>
                <!-- /.input group -->
              </div>
            </div>
            <div class="row">
              <clip-loader
                :loading="loadingCaixa"
                :color="color"
                :size="size"
              ></clip-loader>
              <div
                class="col-md-12"
                v-if="Object.values(this.relatorio).length > 0"
              >
                <div class="valor-fechamento-positivo">
                  Quantidade: {{ relatorio.quantidade }}
                </div>
                <div class="valor-fechamento-positivo">
                  Entradas: {{ relatorio.entradas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-total-negativo">
                  Saídas: {{ relatorio.saidas | formatMoeda() }}
                </div>
                <div class="valor-fechamento-positivo">
                  Comissões: {{ relatorio.comissaocambista | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-positivo"
                  v-if="relatorio.saldo >= 0"
                >
                  Total: {{ relatorio.saldo | formatMoeda() }}
                </div>
                <div
                  class="valor-fechamento-total-negativo"
                  v-if="relatorio.saldo < 0"
                >
                  Total: {{ relatorio.saldo | formatMoeda() }}
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal Caixa-->
    <!--End dados logado-->

    <!--Modal PIN Pre-Aposta-->
    <div class="modal fade" id="modal-pre-aposta" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 4px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
          <div class="modal-header" style="background-color: #fff; border-bottom: 3px solid #3c8dbc; padding: 15px 20px;">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="font-size: 24px; opacity: 0.5;">&times;</button>
            <h4 class="modal-title" style="font-weight: 500; color: #555; font-size: 18px;"><i class="fa fa-ticket"></i> PIN GERADO</h4>
          </div>
          <div class="modal-body" style="text-align: center; padding: 30px 20px;">
            <p style="font-size: 16px; color: #555; text-align: left; margin-bottom: 25px;">Procure o colaborador mais próximo, informando o seguinte código, para validação:</p>
            
            <h2 style="font-weight: 800; font-size: 34px; letter-spacing: 1px; margin-bottom: 25px; color: #333;">{{ cupom_pre_aposta }}</h2>
            
            <div style="margin-bottom: 30px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
              <button class="btn btn-info" style="background-color: #17a2b8; border-color: #17a2b8; color: #fff; padding: 8px 15px; font-weight: bold; border-radius: 3px;" @click="copyToClipboard(cupom_pre_aposta)">
                <i class="fa fa-copy"></i> COPIAR CÓDIGO
              </button>
              <a :href="link" target="_blank" class="btn btn-info" style="background-color: #17a2b8; border-color: #17a2b8; color: #fff; padding: 8px 15px; font-weight: bold; border-radius: 3px;">
                <i class="fa fa-whatsapp"></i> COMPARTILHAR
              </a>
            </div>

            <div style="background-color: #dd4b39; color: #fff; padding: 15px; border-radius: 3px; font-size: 14px; text-align: left; line-height: 1.4;">
              <i class="fa fa-exclamation-triangle"></i> O PIN não poderá ser validado após <strong>300 minutos</strong> ou em caso de indisponibilidade de partidas no momento da validação
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--End Modal PIN Pre-Aposta-->

    <div class="modal fade" id="modal-bilhete" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div
          class="modal-content"
          v-for="palpite in bilhetes"
          :key="palpite.id"
          style="border: none; border-radius: 0; background-color: #FDF5D2; color: #333; font-family: 'Montserrat', sans-serif;"
        >
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
                  <img :src="server.logo_img || '/img/logo.png'" style="max-width: 200px; height: auto;">
                  <h3 v-if="server.logo" style="margin: 10px 0 0; font-weight: 900; color: #000; text-transform: uppercase; font-size: 18px; letter-spacing: 1px;">
                    {{ server.logo.split(" - ")[0] }}
                  </h3>
              </div>

              <!-- 🚀 DIVISOR DASHED -->
              <div style="border-top: 1.5px dashed #BDB76B; width: 90%; margin: 0 auto 20px;"></div>

              <div style="padding: 0 20px;">
                  <h4 v-if="palpite.modalidade != 'Loto' && (!palpite.cupom || !palpite.cupom.startsWith('LOTO-'))" style="text-align: center; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; color: #444; letter-spacing: 1px;">
                    {{ palpite.tipo }}
                  </h4>

                  <!-- 🚀 INFO GERAL -->
                  <div v-if="palpite.modalidade != 'Loto' && (!palpite.cupom || !palpite.cupom.startsWith('LOTO-'))" style="font-size: 13px; line-height: 1.8; margin-bottom: 20px; color: #555;">
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

                  <!-- 🚀 HEADER PALPITES (ESCONDER SE FOR LOTO) -->
                  <div v-if="palpite.modalidade != 'Loto' && (!palpite.palpites_loto || palpite.palpites_loto.length == 0) && (!palpite.cupom || !palpite.cupom.startsWith('LOTO-'))" style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 10px;">
                      <span>EVENTO / MERCADO</span>
                      <span>COTAÇÃO</span>
                  </div>

                  <!-- 🚀 LISTA DE JOGOS (ESCONDER SE FOR LOTO) -->
                  <div v-if="palpite.modalidade != 'Loto' && (!palpite.palpites_loto || palpite.palpites_loto.length == 0) && (!palpite.cupom || !palpite.cupom.startsWith('LOTO-'))" v-for="palp in palpite.palpites" :key="palp.id" style="margin-bottom: 25px;">
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

                  <!-- 🚀 CASO SEJA LOTO -->
                  <div v-if="palpite.modalidade == 'Loto' || (palpite.palpites_loto && palpite.palpites_loto.length > 0) || (palpite.cupom && palpite.cupom.startsWith('LOTO-'))" style="padding: 10px 0;">
                      <div style="background: #00ADEF; color: #fff; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                      <div class="thermal-loto-ticket" 
                           style="background: #f8ecc2; color: #000; font-family: 'Courier New', Courier, monospace; padding: 25px 20px; border: 1px solid #e1d1a1; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 20px; position: relative; overflow: hidden; font-size: 13px; line-height: 1.4;">
                        
                        <!-- Top serrated edge effect -->
                        <div style="position: absolute; top: -5px; left: 0; right: 0; height: 10px; background: radial-gradient(circle, transparent 70%, #fff 70%); background-size: 15px 15px;"></div>

                        <div style="text-align: center; border-bottom: 1.5px dashed #000; padding-bottom: 15px; margin-bottom: 15px;">
                          <h2 style="margin: 0; font-weight: 900; font-size: 24px; letter-spacing: 1px;">{{ palpite.tipo.toUpperCase() }}</h2>
                          <p style="margin: 8px 0; font-size: 15px; font-weight: 900; background: #000; color: #f8ecc2; display: inline-block; padding: 2px 10px;">CONCURSO: {{ palpite.concurso || 'OFICIAL' }}</p>
                          <p style="margin: 5px 0; font-size: 12px;">EMISSÃO: {{ palpite.created_at | formatDate }}</p>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                          <p style="font-weight: 900; margin-bottom: 12px; text-decoration: underline; text-align: center;">DEZENAS ESCOLHIDAS</p>
                          <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px;">
                            <div v-for="palp in palpite.palpites_loto" :key="palp.id" 
                                 style="border: 1.5px solid #000; min-width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 18px; border-radius: 4px;">
                              {{ palp.dezena }}
                            </div>
                          </div>
                        </div>

                        <div style="border-top: 1.5px dashed #000; border-bottom: 1.5px dashed #000; padding: 15px 0; margin-bottom: 15px;">
                           <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                              <span>VALOR DA APOSTA:</span>
                              <strong>R$ {{ (palpite.valor_apostado || 0) }}</strong>
                           </div>
                           <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                              <span>COTAÇÃO ATRIBUÍDA:</span>
                              <strong>{{ (palpite.cotacao || 1) }}x</strong>
                           </div>
                           <div style="display: flex; justify-content: space-between; margin-top: 12px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.2);">
                              <span style="font-weight: 900;">PREMIAÇÃO ESTIMADA:</span>
                              <strong style="font-size: 20px; font-weight: 900;">R$ {{ (palpite.retorno_possivel || 0) }}</strong>
                           </div>
                        </div>

                        <div style="text-align: center; font-size: 12px; line-height: 1.5;">
                          <p style="margin: 0; font-weight: 900; text-transform: uppercase;">{{ server.name || 'IHUB BETS' }}</p>
                          <p style="margin: 3px 0;">VALIDO PELO RESULTADO DA LOTERIA FEDERAL</p>
                          <p style="margin: 10px 0 0; font-weight: 900; font-size: 16px; border: 2px solid #000; padding: 5px; display: inline-block;">PIN: {{ palpite.cupom }}</p>
                          <p style="margin: 5px 0 0; font-size: 10px; opacity: 0.8;">Cliente: {{ palpite.cliente || 'Consumidor' }}</p>
                        </div>

                        <!-- Bottom serrated edge effect -->
                        <div style="position: absolute; bottom: -5px; left: 0; right: 0; height: 10px; background: radial-gradient(circle, transparent 70%, #fff 70%); background-size: 15px 15px; transform: rotate(180deg);"></div>
                      </div>
                    </div>
                  </div>

                  <!-- 🚀 CÓDIGO PIN / CUPOM CENTRAL -->
                  <div v-if="palpite.modalidade != 'Loto'" style="margin: 30px 0; text-align: center; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 15px 0;">
                      <h1 style="font-weight: 900; font-size: 42px; margin: 0; color: #000; letter-spacing: 2px;">
                        {{ palpite.cupom }}
                      </h1>
                  </div>

                  <!-- 🚀 RESUMO FINANCEIRO -->
                  <div v-if="palpite.modalidade != 'Loto'" style="padding-bottom: 20px;">
                      <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <span>Quantidade de Jogos</span>
                        <b>{{ palpite.total_palpites }}</b>
                      </div>
                      <div v-if="palpite.status != 'Aberto'" style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <span>Acertos</span>
                        <b>{{ palpite.acertos_palpites }}</b>
                      </div>
                      <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <span>Cotação Total</span>
                        <b>{{ (palpite.cotacao || palpite.total_cotacao) | formatCotacao() }}</b>
                      </div>
                      <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <span>Valor Apostado</span>
                        <b>{{ palpite.valor_apostado | formatMoeda() }}</b>
                      </div>
                      
                      <div style="display: flex; justify-content: space-between; padding: 15px 0; align-items: center;">
                        <span style="font-weight: 800; font-size: 16px;">Retorno Possível</span>
                        <b style="font-size: 22px; color: var(--container_jogos--color); font-weight: 900;">{{ palpite.retorno_possivel | formatMoeda() }}</b>
                      </div>
                  </div>

                  <!-- 🚀 DIVISOR FINAL -->
                  <div style="border-top: 1.5px solid #000; width: 100%; margin-bottom: 15px;"></div>

                  <!-- 🚀 REGRAS RODAPÉ -->
                  <div style="padding-bottom: 30px;">
                      <b style="font-size: 10px; text-transform: uppercase; color: #000;">REGRAS:</b>
                      <p style="font-size: 10px; color: #666; margin-top: 5px; line-height: 1.4;">
                         {{ server.texto_rodape }}
                      </p>
                  </div>

                  <!-- 🚀 BOTÕES DE AÇÃO (COMPARTILHAR / IMPRIMIR) -->
                  <div class="no-print" style="padding: 10px 0 20px; display: flex; flex-direction: column; gap: 10px;">
                      <a :href="link" class="btn btn-success btn-block" style="background-color: #25D366; border: none; font-weight: 800; padding: 12px; border-radius: 8px; text-transform: uppercase;">
                          <i class="fa fa-whatsapp"></i> Compartilhar WhatsApp
                      </a>
                      <div style="display: flex; gap: 10px;">
                          <button @click="printJogos(palpite.id)" class="btn btn-dark" style="flex: 1; font-weight: 700; padding: 10px; border-radius: 8px;">
                              <i class="fa fa-print"></i> Imprimir
                          </button>
                          <button @click="downloadTicketImage(palpite.cupom)" class="btn btn-info" style="flex: 1; font-weight: 700; padding: 10px; border-radius: 8px;">
                              <i class="fa fa-download"></i> Baixar Imagem
                          </button>
                      </div>
                      <button class="btn btn-secondary btn-block" data-dismiss="modal" style="font-weight: 700; padding: 10px; border-radius: 8px; margin-top: 5px;">
                          Fechar
                      </button>
                  </div>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!--End Modal Bilhete-->

    <div class="modal fade" id="modal-match-old-1" v-show="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-trophy"></i>
              {{ liga }} - {{ match.date | formatDate() }}
            </h4>
          </div>

          <div class="modal-body box box-primary">
            <table style="width: 100%" v-if="!live">
              <tr>
                <td align="left" width="45%">
                  <span class="timeMatch"> {{ match.home }} </span>
                </td>
                <td align="center" width="10%">
                  <span class="score-real-time">
                    <strong> X </strong>
                  </span>
                </td>
                <td align="right" width="45%">
                  <span class="timeMatch">{{ match.away }} </span>
                </td>
              </tr>
              <br />
            </table>
            <br />

            <div class="real-time" v-if="live">
              <div class="placar">
                <span class="score-real-time">
                  <strong v-if="live">{{ match.score }}</strong>
                </span>
                <div class="time-real-time" v-if="match.time == 0">
                  Não Iniciado {{ match.time }}
                  <span class="pisca">'</span>
                </div>
                <div
                  class="time-real-time"
                  v-if="match.time < 45 && match.time != 0"
                >
                  1º Tempo {{ match.time }}
                  <span class="pisca">'</span>
                </div>
                <div class="time-real-time" v-if="match.time == 45">
                  Intervalo
                  <span class="pisca">'</span>
                </div>
                <div class="time-real-time" v-if="match.time > 45">
                  2º Tempo {{ match.time }}
                  <span class="pisca">'</span>
                </div>

                <table class="tableInfo">
                  <thead>
                    <tr class="table-header">
                      <th class="left padding-10">
                        <span>{{ liga }}</span>
                      </th>
                      <th class="cell-soccer">1T</th>
                      <th class="cell-soccer">2T</th>
                      <th class="cell-soccer">
                        <label class="icon corner"></label>
                      </th>
                      <th class="cell-soccer">
                        <label class="icon yellow-card"></label>
                      </th>
                      <th class="cell-soccer">
                        <label class="icon red-card"></label>
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr class="table-row">
                      <td class="left padding-10">{{ match.home }}</td>
                      <td class="cell-soccer">{{ match.halfTimeScoreHome }}</td>
                      <td class="cell-soccer">{{ match.fullTimeScoreHome }}</td>
                      <td class="cell-soccer">
                        {{ match.numberOfCornersHome }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfYellowCardsHome }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfRedCardsHome }}
                      </td>
                    </tr>
                    <tr class="table-row">
                      <td class="left padding-10">{{ match.away }}</td>
                      <td class="cell-soccer">{{ match.halfTimeScoreAway }}</td>
                      <td class="cell-soccer">{{ match.fullTimeScoreAway }}</td>
                      <td class="cell-soccer">
                        {{ match.numberOfCornersAway }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfYellowCardsAway }}
                      </td>
                      <td class="cell-soccer">
                        {{ match.numberOfRedCardsAway }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <clip-loader
              :loading="loading_odds"
              :color="color"
              :size="size"
            ></clip-loader>

            <div class="row" v-for="mercado in mercados" :key="mercado.id">
              <div class="titulo-grupo">{{ mercado.name }}</div>
              <div class="row">
                <div
                  class="col-md-12"
                  v-for="odd in mercado.odds"
                  :key="odd.id"
                >
                    <div class="odd-match-plus" v-if="odd.cotacao == 0">
                      <span class="odd-match-plus-left">
                        <strong>{{ odd.odd }}</strong>
                      </span>
                      <span
                        class="odd-match-plus-right"
                        :class="{ 'selecionado': selectionsIds.includes(odd.uuid) }"
                        :taxaJogo="match.event_id"
                        :taxa="odd.id"
                        @click="
                          addPalpite(
                            odd.uuid,
                            odd.id,
                            match.sport,
                            match.event_id,
                            odd.group_opp,
                            odd.odd,
                            odd.cotacao,
                            liga,
                            match.date,
                            match.home,
                            match.away,
                            odd.type,
                            odd.cotacaoOriginal
                          )
                        "
                      >
                        <i class="fa fa-lock"></i>
                      </span>
                    </div>
                    <div class="odd-match-plus" v-if="odd.cotacao > 0">
                      <span class="odd-match-plus-left">
                        <strong>{{ odd.odd }}</strong>
                      </span>
                      <span
                        class="odd-match-plus-right"
                        :class="{ 'selecionado': selectionsIds.includes(odd.uuid) }"
                        :taxaJogo="match.event_id"
                        :taxa="odd.id"
                        @click="
                          addPalpite(
                            odd.uuid,
                            odd.id,
                            match.sport,
                            match.event_id,
                            odd.group_opp,
                            odd.odd,
                            odd.cotacao,
                            liga,
                            match.date,
                            match.home,
                            match.away,
                            odd.type,
                            odd.cotacaoOriginal
                          )
                        "
                        >{{ odd.cotacao | formatCotacao() }}</span
                      >
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!--End Modal -->

    <header class="main-header">
      <!-- Logo -->
      <a href="/" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
         {{ server.logoMini }}
        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
         {{ server.logo }}
        </span>
      </a>

      <!-- Header Navbar: style can be found in header.less -->
      <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="javascript:void(0)" class="sidebar-toggle" data-toggle="push-menu" role="button">
          <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Control Sidebar Toggle Button -->
            <!-- Control Sidebar Toggle Button -->
            <li v-if="logar" style="margin-top: 4px;padding: 6px 2px 6px 6px;">
              <a href="javascript:void(0)" data-toggle="modal" data-target="#modal-login" class="btn btn-acessar-demo">
                <i class="fa fa-sign-in" style="margin-right: 1px;"></i> Entrar
              </a>
            </li>
            <li v-if="logar" style="margin-top: 4px;padding: 6px 8px 6px 10px;">
              <a href="javascript:void(0)" data-toggle="modal" data-target="#modal-register" class="btn btn-cadastrar-demo">
                <i class="fa fa-user-plus" style="margin-right: 2px;"></i> Cadastre-se
              </a>
            </li>

            <li v-if="logout">
              <a href="javascript:void(0)" @click="sair()">
                <i class="fa fa-close">Sair</i>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <nav class="navbar navbar-static-top" id="nav-mobile">
        <input
          class="form-control"
          id="input-mobile-top"
          placeholder="valor"
          type="number"
          v-model="apostado"
          @keyup="calculaCotacao()"
        />

        <span class="ganho-mobile" v-if="selection.length > 0">{{ retorno | formatMoeda() }}</span>
        <span class="ganho-mobile" v-if="selection.length == 0">{{ 0 | formatMoeda() }}</span>
        <button
          class="btn btn-danger"
          id="btn-zerar-mobile"
          @click="removePalpites(selection)"
          v-if="selection.length > 0"
        >
          <i class="fa fa-trash" style="color: #fff !important;"></i>
        </button>

        <button
          class="btn btn-info btnSendBet"
          id="btn-finalizar-mobile"
          @click="mostraPalpites"
        >( {{selection.length}} ) Finalizar</button>
      </nav>

    </header>


    <!-- Left side column. contains the logo and sidebar -->
    <!-- <main-sidebar-component></main-sidebar-component> -->
    <aside class="main-sidebar">
      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
          <img
            v-if="server.logo_img"
            :src="server.logo_img"
            :alt="server.logo"
          />
          <div class="dados-logado">
            <p v-if="logado">{{ name }}</p>
            <p v-if="logado" style="font-size: 13px; color: #fff;">
              Saldo: {{ caixaUser.saldo_simples | formatMoeda() }}
            </p>
            <p v-if="logado" style="font-size: 13px; color: #ffc107;">
              Bônus: {{ caixaUser.saldo_casadinha | formatMoeda() }}
            </p>
          </div>
        </div>
        <!--Form Busca-->
        <div class="sidebar-form">
          <div class="input-group">
            <input
              type="text"
              v-model="cupom"
              class="form-control"
              placeholder="Conferir bilhete"
              style="background-color: var(--search_bar_bg--color, #fff); color: var(--search_bar_text--color, #333); border: 1px solid var(--linhas--color, #eee); border-right: none;"
            />
            <span class="input-group-btn">
              <button @click="searchBilhete()" class="btn btn-flat" style="background-color: var(--ticket_consult_bg--color, var(--primary-color)); color: #fff; border: 1px solid var(--ticket_consult_bg--color, var(--primary-color));">
                <i class="fa fa-search"></i>
              </button>
            </span>
          </div>
        </div>

        <ul class="sidebar-menu tree" data-widget="tree">
          <li class="header"><i class="fa fa-list"></i> MENU PRINCIPAL</li>
          <li class="treeview">
            <a href="#" @click="loadRegulamento" class="sidebar-toggle">
              <i class="fa fa-map"></i>
              <span>Regulamento</span>
            </a>
          </li>
          <li>
            <a v-bind:href="`${server.linkApp}`">
              <i class="fa fa-android"></i>
              <span>Baixar Aplicativo</span>
            </a>
          </li>

          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadValidarPin()">
              <i class="fa fa-code"></i>
              <span>Validar PIN</span>
            </a>
          </li>

          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadMeusClientes()">
              <i class="fa fa-users"></i>
              <span>Meus Clientes</span>
            </a>
          </li>

          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadCaixa()">
              <i class="fa fa-money"></i>
              <span>Meu Caixa</span>
            </a>
          </li>
          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadRelatorio()">
              <i class="fa fa-pie-chart"></i>
              <span>Relatório</span>
            </a>
          </li>
          <li v-if="token != null && nivel == 'cambista'">
            <a href="javascript:void(0)" @click="loadBilhetes()">
              <i class="fa fa-tags"></i>
              <span>Bilhetes</span>
            </a>
          </li>

          <!-- Menu Gerente -->
          <li v-if="token != null && (nivel == 'gerente' || nivel == 'manager')">
            <a href="javascript:void(0)" @click="loadMeusCambistas()">
              <i class="fa fa-users"></i>
              <span>Meus Cambistas</span>
            </a>
          </li>
          <li v-if="token != null && (nivel == 'gerente' || nivel == 'manager')">
            <a href="javascript:void(0)" @click="loadRelatorioGeral()">
              <i class="fa fa-line-chart"></i>
              <span>Relatório Geral</span>
            </a>
          </li>

          <!-- Menu Jogador -->
          <li v-if="logado && (nivel == 'cliente' || nivel == 'user')">
            <a href="javascript:void(0)" @click="loadBilhetes()">
              <i class="fa fa-tags"></i>
              <span>Meus Bilhetes</span>
            </a>
          </li>
          <li v-if="logado && (nivel == 'cliente' || nivel == 'user')">
            <a href="javascript:void(0)" @click="load_deposit()">
              <i class="fa fa-money"></i>
              <span>Depositar</span>
            </a>
          </li>
          <li v-if="logado && (nivel == 'cliente' || nivel == 'user')">
            <a href="javascript:void(0)" @click="load_withdrawal()">
              <i class="fa fa-university"></i>
              <span>Sacar</span>
            </a>
          </li>

          <li v-if="logado">
            <a href="javascript:void(0)" @click="loadMinhaConta()">
              <i class="fa fa-user"></i>
              <span>Minha Conta</span>
            </a>
          </li>

          <!-- 🍀 SEÇÃO LOTO -->
          <template v-if="op_quininha == 'Sim' || op_quininha == 'Ativado' || op_seninha == 'Sim' || op_seninha == 'Ativado' || configuracoes.op_quininha == 'Sim' || configuracoes.op_quininha == 'Ativado' || configuracoes.op_seninha == 'Sim' || configuracoes.op_seninha == 'Ativado'">
            <li class="header"><i class="fa fa-money"></i> LOTO</li>
            <li :class="{'active': modalitySelected == 'Quininha'}" v-if="op_quininha == 'Sim' || op_quininha == 'Ativado' || configuracoes.op_quininha == 'Sim' || configuracoes.op_quininha == 'Ativado'">
              <a href="javascript:void(0)" @click="loadQuininha()">
                <i class="fa fa-ticket" style="color: var(--container_jogos--color);"></i>
                <span>Quininha</span>
              </a>
            </li>
            <li :class="{'active': modalitySelected == 'Seninha'}" v-if="op_seninha == 'Sim' || op_seninha == 'Ativado' || configuracoes.op_seninha == 'Sim' || configuracoes.op_seninha == 'Ativado'">
              <a href="javascript:void(0)" @click="loadSeninha()">
                <i class="fa fa-ticket" style="color: #ff9800;"></i>
                <span>Seninha</span>
              </a>
            </li>
          </template>



          <!-- LIGAS MANUAIS (ESPECIAIS) -->
          <template v-if="manualLeagues && manualLeagues.length > 0">
            <li class="header"><i class="fa fa-star"></i> ESPECIAIS</li>
            <li v-for="league in manualLeagues" :key="league.id">
              <a href="javascript:void(0)" @click="seachLeague(league.league || league.name)" style="display: flex; align-items: center; padding: 8px 15px;">
                <img :src="league.flag || league.image" style="width: 18px; height: 13px; margin-right: 10px; border-radius: 2px; object-fit: cover;" @error="$event.target.src = '/img/countries/trophy.svg'">
                <span style="font-size: 13px; font-weight: 400; color: #ccc;">{{ league.league || league.name }}</span>
              </a>
            </li>
          </template>

          <!-- LIGAS PRINCIPAIS (Demo Style) -->
          <li class="header"><i class="fa fa-trophy"></i> LIGAS PRINCIPAIS</li>

          <li v-for="league_main in filteredLeaguesMain" :key="league_main.id">
            <a href="javascript:void(0)" @click="seachLeague(league_main.league || league_main.name)" style="display: flex; align-items: center; padding: 8px 15px;">
              <img :src="league_main.flag || league_main.image" style="width: 18px; height: 13px; margin-right: 10px; border-radius: 2px; object-fit: cover;" @error="$event.target.src = '/img/countries/trophy.svg'">
              <span style="font-size: 13px; font-weight: 400; color: #ccc;">{{ league_main.league || league_main.name }}</span>
            </a>
          </li>

          <!-- OUTRAS LIGAS - Accordion por País (Demo Style) -->
          <li class="header"><i class="fa fa-globe"></i> OUTRAS LIGAS</li>

          <li v-for="group in groupedLeaguesOthers" :key="group.cc || 'other_group'">
            <a href="javascript:void(0)" @click="toggleCountry(group.cc || 'other')" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 15px; cursor: pointer;">
              <div style="display: flex; align-items: center;">
                <img :src="group.flag" style="width: 18px; height: 13px; margin-right: 10px; border-radius: 2px; object-fit: cover;" @error="$event.target.src = '/img/countries/trophy.svg'">
                <span style="font-size: 13px; font-weight: 400; color: #ccc;">{{ group.country || group.name }} ({{ group.leagues.length }})</span>
              </div>
              <i class="fa" :class="openedCountries.includes(group.cc || 'other') ? 'fa-angle-down' : 'fa-angle-left'" style="font-size: 14px; color: #777;"></i>
            </a>
            <ul v-if="openedCountries.includes(group.cc || 'other')" style="list-style: none; padding: 4px 0; margin: 0; background: rgba(0,0,0,0.12);">
              <li v-for="(l, idx) in group.leagues" :key="idx">
                <a href="javascript:void(0)" @click="seachLeague(l.league)" style="padding: 6px 15px 6px 40px; font-size: 12px; font-weight: 300; color: #aaa; display: block; white-space: normal; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#aaa'">
                  {{ l.league }}
                </a>
              </li>
            </ul>
          </li>
        </ul>

        <!-- Banner Sidebar -->
        <div class="banner-sidebar mt-2" v-if="sidebarBanners && sidebarBanners.length > 0">
          <div v-for="(banner, index) in sidebarBanners" :key="'sb-'+index" style="margin-bottom: 10px;">
            <a :href="banner.link || '#'">
              <img :src="banner.image || banner.img" style="width: 100%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            </a>
          </div>
        </div>
      </section>
      <!-- /.sidebar -->
    </aside>

    <!--End Sidebar-->
    <!--<content-wrap-component></content-wrap-component>-->
    <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-9">
            <!--Content Bilhetes-->

            <!--Content Bilhetes-->
            <div v-if="bilheteView && logado" class="bilhetes-content">
              <div class="form-inline relatorio">
                <div class="form-group">
                  <label>Data:</label>

                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input
                      type="date"
                      v-model="date1"
                      class="form-control pull-right"
                      id="datepicker"
                      @change="pesquisaBilhetes(date1)"
                    />
                  </div>
                  <!-- /.input group -->
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="tabela-class-home">
                    <tr>
                      <th>CUPOM</th>
                      <th>DATA</th>
                      <th>STATUS</th>
                      <th>APOSTADO</th>
                      <th>RETORNO</th>
                      <th>CLIENTE</th>
                      <th>COMISSÃO</th>
                      <th>COTAÇÃO</th>
                      <th>TIPO</th>
                      <th>AP. ABERTAS</th>
                      <th>MOSTRAR</th>
                      <th>CANCELAR</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="bilhete in bilhetesLogado" :key="bilhete.id">
                      <td v-bind:class="bilhete.tipo_aposta">
                        <b>{{ bilhete.cupom }}</b>
                      </td>
                      <td>{{ bilhete.created_at | formatDate() }}</td>
                      <td v-bind:class="bilhete.status">
                        {{ bilhete.status }}
                      </td>
                      <td>{{ bilhete.valor_apostado | formatMoeda() }}</td>
                      <td>{{ bilhete.retorno_possivel | formatMoeda() }}</td>
                      <td>{{ bilhete.cliente }}</td>
                      <td>{{ bilhete.comicao | formatMoeda() }}</td>
                      <td>{{ bilhete.cotacao | formatCotacao() }}</td>
                      <td>{{ bilhete.tipo }}</td>
                      <td>
                        {{ bilhete.andamento_palpites }}/{{
                          bilhete.total_palpites
                        }}
                      </td>
                      <td>
                        <button
                          class="btn btn-primary"
                          @click="
                            viewBilhete(
                              bilhete.id,
                              bilhete.status,
                              bilhete.cupom,
                              bilhete.created_at,
                              bilhete.vendedor,
                              bilhete.cliente,
                              bilhete.total_palpites,
                              bilhete.cotacao,
                              bilhete.valor_apostado,
                              bilhete.retorno_possivel
                            )
                          "
                        >
                          <i class="fa fa-eye"></i>
                        </button>
                      </td>
                      <td>
                        <button v-if="bilhete.status == 'Cancelado'" class="btn btn-default" disabled><i class="fa fa-exclamation-circle"></i></button>
                        <button v-if="bilhete.status != 'Cancelado'" class="btn btn-danger" @click="alterarBilhete(bilhete.id, bilhete)"><i class="fa fa-remove"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <clip-loader :loading="loading" :color="color" :size="size"></clip-loader>
              </div>
            </div>

            <div v-if="jogosView">
              <div class="row">
                <div class="col-lg-12">
                  <div id="carouselbanners" data-ride="carousel" class="carousel slide mb-3" v-if="mainBanners.length > 0">
                    <div class="carousel-inner">
                      <div v-for="(banner, index) in mainBanners" :key="'banner-'+index" :class="['item', { active: index === 0 }]">
                        <a :href="banner.link || '#'"><img :src="banner.image" class="w-100"></a>
                      </div>
                    </div> 
                    <a href="#carouselbanners" data-slide="prev" class="left carousel-control"><span class="glyphicon glyphicon-chevron-left"></span></a> 
                    <a href="#carouselbanners" data-slide="next" class="right carousel-control"><span class="glyphicon glyphicon-chevron-right"></span></a>
                  </div>
                </div>
              </div>

              <!-- Jogos em Destaque (Estilo Solusbet) -->
              <div class="events-carousel-container" style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; -webkit-box-shadow: none !important;" v-if="filteredFeaturedGames.length > 0 && !live">
                <div class="carousel-header theme-bg">
                  <h2 class="carousel-title">
                    <span class="carousel-title-icon"><i class="fa fa-star"></i></span>
                    <span class="carousel-title-text">Jogos em destaque</span>
                  </h2>
                  <div class="carousel-controls">
                    <button class="control-btn prev" @click="scrollCarousel(-1); stopAutoplay()"><i class="fa fa-chevron-left"></i></button>
                    <button class="control-btn next" @click="scrollCarousel(1); stopAutoplay()"><i class="fa fa-chevron-right"></i></button>
                  </div>
                </div>
                <div class="carousel-scroller-wrapper no-scrollbar" style="background: transparent !important; background-color: transparent !important; box-shadow: none !important; -webkit-box-shadow: none !important;" ref="carouselScroller" @mouseenter="stopAutoplay" @mouseleave="startAutoplay">
                  <div class="carousel-card" v-for="match in filteredFeaturedGames" :key="'featured-'+match.id">
                    <div class="card-bg" :style="{ backgroundImage: 'url(' + (match.img_featured || '/images/featured_bg.jpg') + ')' }"></div>
                    <div class="card-overlay"></div>
                    
                    <div class="card-top-info">
                      <div class="info-league">
                        <img v-if="match.flag" :src="match.flag" class="info-league-flag">
                        <i v-else class="fa fa-trophy info-league-flag" style="color: #ffd700; display: flex; align-items: center; justify-content: center; font-size: 10px;"></i>
                        <span>{{ match.league }}</span>
                      </div>
                      <div class="info-right">
                        <div class="info-time-row">
                          <span class="info-time"><i class="fa fa-clock-o"></i> {{ match.time }}</span>
                          <span class="info-countdown" v-if="getTimeRemaining(match.date) !== 'Iniciado'">
                            <i class="fa fa-hourglass-half"></i> {{ getTimeRemaining(match.date) }}
                          </span>
                        </div>
                        <span class="info-category">{{ match.sport || 'Futebol' }} - {{ match.date | formatDateHome }}</span>
                      </div>
                    </div>

                    <div class="card-competitors">
                      <div class="competitor-logos">
                        <div class="logo-wrapper">
                          <img :src="match.logo_home || '/img/placeholder_team.png'">
                        </div>
                        <div class="logo-wrapper logo-down">
                          <img :src="match.logo_away || '/img/placeholder_team.png'">
                        </div>
                      </div>
                      <div class="competitor-names">
                        <div class="competitor-name">{{ match.home }}</div>
                        <div class="competitor-name">{{ match.away }}</div>
                      </div>
                      <button 
                        class="offer-badge" 
                        @click.stop="loadOdd(match.league, match, null)"
                      >
                         {{ match.badge_text || match.count_odd_label || 'Apostar Agora' }}
                      </button>
                    </div>

                    <div class="card-markets">
                      <div v-for="odd in match.odds" :key="odd.uuid" class="market-btn" 
                           :class="{'active': isPalpiteActive(odd.uuid), 'disabled-market': !odd.cotacao || odd.cotacao <= 0}"
                           @click="odd.cotacao && odd.cotacao > 0 ? addPalpite(odd.uuid, odd.id, match.sport, match.id, odd.group_opp, odd.odd, odd.cotacao, match.league, match.date, match.home, match.away, odd.type, odd.cotacaoOriginal, match.logo_home, match.logo_away) : null">
                        <span class="market-indicator">{{ odd.odd }}</span>
                        <span class="market-value" v-if="odd.cotacao && odd.cotacao > 0">{{ odd.cotacao | formatCotacao }}</span>
                        <span class="market-value" v-else>---</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <ul class="menu-jogos no-scrollbar" style="background: var(--sidebar--color) !important; overflow-x: auto; -webkit-overflow-scrolling: touch; white-space: nowrap; display: flex; list-style: none; padding: 0; margin: 0 !important; margin-bottom: 0 !important; padding-right: 30px; border-bottom: none !important;">
                <li class="modality-item-demo" style="flex: 0 0 auto; flex-shrink: 0;" v-if="op_futebol == 'Sim' || op_futebol == 'Ativado' || configuracoes.op_futebol == 'Sim' || configuracoes.op_futebol == 'Ativado'">
                  <a href="javascript:void(0)" @click="loadFutebol()" :class="{'ativo': futebol && !modalitySelected && !live}" style="color: var(--sidebar_text--color, #fff); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; text-transform: uppercase;">
                    <img src="/img/icons/football.png" class="modality-icon-img"> Futebol
                  </a>
                </li>
                <li class="modality-item-demo" style="flex: 0 0 auto; flex-shrink: 0;" v-if="op_ufcbox == 'Sim' || op_ufcbox == 'Ativado' || configuracoes.op_ufcbox == 'Sim' || configuracoes.op_ufcbox == 'Ativado'">
                  <a href="javascript:void(0)" @click="loadModality('Luta')" :class="{'ativo': modalitySelected == 'Luta'}" style="color: var(--sidebar_text--color, #fff); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; text-transform: uppercase;">
                    <img src="/img/icons/boxing.png" class="modality-icon-img"> Luta
                  </a>
                </li>
                <li class="modality-item-demo" style="flex: 0 0 auto; flex-shrink: 0;" v-if="op_basquete == 'Sim' || op_basquete == 'Ativado' || configuracoes.op_basquete == 'Sim' || configuracoes.op_basquete == 'Ativado'">
                  <a href="javascript:void(0)" @click="loadModality('Basquete')" :class="{'ativo': modalitySelected == 'Basquete'}" style="color: var(--sidebar_text--color, #fff); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; text-transform: uppercase;">
                    <img src="/img/icons/basketball.png" class="modality-icon-img"> Basquete
                  </a>
                </li>
                <li class="modality-item-demo" style="flex: 0 0 auto; flex-shrink: 0;" v-if="op_tenis == 'Sim' || op_tenis == 'Ativado' || configuracoes.op_tenis == 'Sim' || configuracoes.op_tenis == 'Ativado'">
                  <a href="javascript:void(0)" @click="loadModality('Tenis')" :class="{'ativo': modalitySelected == 'Tenis'}" style="color: var(--sidebar_text--color, #fff); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; text-transform: uppercase;">
                    <img src="/img/icons/tennis.png" class="modality-icon-img"> Tênis
                  </a>
                </li>
                <li class="modality-item-demo" style="border-right: none; flex: 0 0 auto; flex-shrink: 0;" v-if="op_volei == 'Sim' || op_volei == 'Ativado' || configuracoes.op_volei == 'Sim' || configuracoes.op_volei == 'Ativado'">
                  <a href="javascript:void(0)" @click="loadModality('Volei')" :class="{'ativo': modalitySelected == 'Volei'}" style="color: var(--sidebar_text--color, #fff); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; text-transform: uppercase;">
                    <img src="/img/icons/volleyball.png" class="modality-icon-img"> Vôlei
                  </a>
                </li>
              </ul>

              <!-- Header de Jogos Responsivo (Datas + Pesquisa) -->
              <div class="header-jogos-nexus">
                <!-- Abas de Datas -->
                <div class="no-scrollbar tabs-container" style="display: flex; align-items: stretch; height: 55px; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                  <div style="display: flex; align-items: stretch; gap: 0;">
                    <div @click="loadFutebol()" class="day-tab-demo" :class="{'active': hoje && !live && !modalitySelected}" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center; padding: 0 20px; border-right: 1px solid #eee;">
                      <div style="font-weight: 500; font-size: 13px; color: #111;">Hoje</div>
                      <div style="font-size: 11px; color: #888;">{{ moment().format('DD/MM') }}</div>
                    </div>
                    <div v-for="(day, index) in days.slice(1, 3)" :key="day.id" @click="searchDay(day.id, index)" class="day-tab-demo" :class="{'active': activeTabIdx === index && !hoje && !live && !modalitySelected}" style="cursor: pointer; display: flex; flex-direction: column; justify-content: center; padding: 0 20px; border-right: 1px solid #eee;">
                      <div style="font-weight: 400; font-size: 13px; color: #333; text-transform: lowercase;">{{ moment(day.day, 'DD/MM').format('dddd').split('-')[0] }}</div>
                      <div style="font-size: 11px; color: #888;">{{ day.day }}</div>
                    </div>
                    <div @click="loadVivo()" class="day-tab-demo tab-ao-vivo" :class="{'active': live}" style="cursor: pointer; display: flex; align-items: center; padding: 0 20px; color: var(--live-color, #cc3333); font-weight: 400; font-size: 12px; gap: 6px; border-right: 1px solid #eee; white-space: nowrap;">
                      AO VIVO <span class="live-circle"></span>
                    </div>
                  </div>
                </div>

                <!-- Barra de Busca -->
                <div class="search-container-nexus">
                  <div class="input-group" style="width: 100%;">
                    <input type="text" v-model="search" @keyup.enter="searchMatches()" placeholder="Pesquisar por Liga, Time, Horário" class="form-control" style="height: 38px; border-radius: 4px 0 0 4px; border: 1px solid var(--linhas--color, #ddd); border-right: none; box-shadow: none; font-size: 13px; background-color: var(--search_bar_bg--color) !important; color: var(--search_bar_text--color) !important;">
                    <span class="input-group-btn">
                      <button class="btn btn-info btn-flat" type="button" @click="searchMatches()" style="height: 38px; border-radius: 0 4px 4px 0; border: none; background: var(--search_icon_bg--color, var(--primary-color)) !important; color: #fff !important; padding: 0 15px; transition: filter 0.3s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='brightness(1)'">
                        <i class="fa fa-search"></i>
                      </button>
                    </span>
                  </div>
                </div>
              </div>

              
              <clip-loader
                :loading="loading"
                :color="color"
                :size="size"
              ></clip-loader>

              <div class="loto-container" v-if="modalitySelected == 'Quininha' || modalitySelected == 'Seninha'" style="background: var(--container_jogos--color, #fff); padding: 20px; border-radius: 8px; margin-top: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div class="loto-header" style="border-bottom: 2px solid var(--sidebar--color); padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                  <div>
                    <h3 style="margin: 0; color: var(--sidebar--color); font-weight: 800; text-transform: uppercase;">{{ modalitySelected }}</h3>
                    <p style="margin: 5px 0 0; color: #666; font-size: 13px;">Selecione suas dezenas e boa sorte!</p>
                  </div>
                  <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="form-group" style="margin-bottom: 0;">
                      <label style="font-size: 11px; display: block; color: #888;">CONCURSO</label>
                      <select class="form-control input-sm" v-model="lotoDateSelected" style="min-width: 150px;">
                        <option v-for="date in datesLoto" :key="date.date" :value="date.date">{{ date.date }} - {{ date.day }}</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-8">
                    <div class="loto-grid" style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 5px;">
                      <div v-for="num in numbersLoto" :key="num" 
                          @click="selectNumberLoto(num)"
                          class="loto-number"
                          :class="{'selected-loto': selectedNumbersLoto.includes(num)}"
                          style="aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px; transition: 0.2s;"
                          :style="selectedNumbersLoto.includes(num) ? 'background: var(--sidebar--color); color: #fff; border-color: var(--sidebar--color);' : 'background: var(--container_jogos--color, #f9f9f9); color: #333;'"
                      >
                        {{ num }}
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="loto-panel" style="background: var(--modal_bg--color, #f4f6f9); padding: 15px; border-radius: 6px; border: 1px solid #dee2e6;">
                      <h4 style="margin-top: 0; font-weight: bold; font-size: 15px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">CONFIGURAÇÃO</h4>
                      
                      <div class="form-group">
                        <label style="font-size: 12px;">MODALIDADE / COTAÇÃO</label>
                        <select class="form-control" v-model="lotoTaxaSelected">
                          <option v-for="taxa in taxasLoto" :key="taxa.id" :value="taxa">Acertar {{ taxa.dezena }} dezenas - {{ taxa.taxa }}x</option>
                        </select>
                      </div>

                      <div class="form-group">
                        <label style="font-size: 12px;">VALOR DA APOSTA (R$)</label>
                        <input type="number" class="form-control" v-model="lotoValue" :min="valor_mini_aposta || 2">
                      </div>

                      <div class="info-selecao" style="margin-top: 15px; padding: 10px; background: var(--container_jogos--color, #fff); border-radius: 4px; border-left: 4px solid var(--sidebar--color);">
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                          <span>Dezenas:</span>
                          <b :style="lotoTaxaSelected && selectedNumbersLoto.length == lotoTaxaSelected.dezena ? 'color: green' : 'color: red'">
                            {{ selectedNumbersLoto.length }} / {{ lotoTaxaSelected ? lotoTaxaSelected.dezena : '?' }}
                          </b>
                        </div>
                        <div v-if="logado" style="display: flex; justify-content: space-between; font-size: 13px; margin-top: 5px;">
                          <span>Retorno:</span>
                          <b style="color: var(--sidebar--color);">R$ {{ (lotoValue * (lotoTaxaSelected ? lotoTaxaSelected.taxa : 0)).toFixed(2) }}</b>
                        </div>
                      </div>

                      <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button class="btn btn-default btn-block" @click="surpresinhaLoto()" style="font-weight: bold; text-transform: uppercase; font-size: 11px;">
                          <i class="fa fa-magic"></i> Surpresinha
                        </button>
                        <button class="btn btn-danger btn-block" @click="selectedNumbersLoto = []" style="font-weight: bold; text-transform: uppercase; font-size: 11px;">
                          <i class="fa fa-trash"></i> Limpar
                        </button>
                      </div>

                      <button class="btn btn-success btn-block" @click="addLotoToCart()" :disabled="loading" style="margin-top: 15px; height: 45px; font-weight: 800; font-size: 14px; text-transform: uppercase; background-color: var(--container_jogos--color) !important; border: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <span v-if="loading"><i class="fa fa-refresh fa-spin"></i> ENVIANDO...</span>
                        <span v-else>{{ logado ? 'FINALIZAR APOSTA' : 'APOSTAR' }}</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="content-jogos" v-if="!['Quininha', 'Seninha'].includes(modalitySelected)" v-for="event in filterLiegues" :key="event.id">
                <h4 class="header-campeonato-matchs" style="margin-bottom: -1px; display: flex; align-items: center;">
                  <img :src="event.flag" style="width: 20px; height: 14px; margin-right: 8px; border-radius: 2px; object-fit: cover;" @error="$event.target.src = '/img/countries/trophy.svg'">
                  {{ event.league }}
                  <span v-if="live" style="margin-left: 8px; font-size: 10px; color: #fff; background: var(--live-color, #cc3333); padding: 2px 6px; border-radius: 3px; animation: pulse 1.5s infinite;">AO VIVO</span>
                </h4>
                <div class="jogo">
                  <div class="row container-lista-jogos" v-for="match in event.match" :key="match.id" :class="{'live-match-row': live}" :style="live ? 'border-left: 3px solid var(--live-color, #cc3333);' : ''">
                    <!-- Teams Info (Layout Clássico) -->
                    <div class="col-lg-6 col-md-6 col-xs-9 jogos">
                      <table style="width: 100%;">
                        <tr>
                          <td align="left" width="15%">
                            <img loading="lazy" class="team-logo-img" :src="match.logo_home || '/img/placeholders/shield.png'" :alt="match.home" @error="$event.target.src = '/img/placeholders/shield.png'">
                          </td>
                          <td align="center" width="30%" class="team-name">
                            {{ match.home }}
                          </td>
                          <td align="center" width="10%">
                            <strong v-if="live && match.score" style="color: var(--live-color, #cc3333); font-size: 16px;">{{ match.score }}</strong>
                            <strong v-else style="color: #777;">X</strong>
                          </td>
                          <td align="center" width="30%" class="team-name">
                            {{ match.away }}
                          </td>
                          <td align="right" width="15%">
                            <img loading="lazy" class="team-logo-img" :src="match.logo_away || '/img/placeholders/shield.png'" :alt="match.away" @error="$event.target.src = '/img/placeholders/shield.png'">
                          </td>
                        </tr>
                      </table>
                    </div>

                    <!-- Time -->
                    <div class="col-lg-1 col-md-1 col-xs-3 data-hora">
                      <span v-if="!live">{{ match.date | formatDate() }}</span>
                      <span v-else class="text-danger" style="font-weight: bold;">
                        <i class="fa fa-circle pisca" style="font-size: 8px;"></i>
                        {{ match.elapsed || match.time }}'
                      </span>
                    </div>

                    <!-- Odds & Actions -->
                    <div class="col-lg-5 col-md-5 col-xs-12 btn-apostas">
                      <div class="cotacoes-principais">
                        <template v-for="(odd, index) in (match.odds ? match.odds.slice(0, 3) : [])">
                          <div v-if="odd.cotacao == 0"
                            class="btn-home"
                            :key="'lock-'+odd.id"
                          >
                            <i class="fa fa-lock"></i>
                          </div>
                          <div v-if="odd.cotacao > 0"
                            class="btn-home" 
                            :class="{ selecionado: selectionsIds.includes(odd.id) }" 
                            @click="addPalpite(odd.uuid, odd.id, match.sport, match.id, odd.group_opp, odd.odd, odd.cotacao, event.league, match.date, match.home, match.away, odd.type, odd.cotacaoOriginal, match.logo_home, match.logo_away)"
                            :key="'odd-'+odd.id"
                          >
                            <strong>{{ odd.odd.charAt(0) }}</strong>
                            {{ odd.cotacao | formatCotacao() }}
                          </div>
                        </template>
                      </div>

                      <!-- Plus Markets -->
                      <div title="Mais mercados" class="plus-odd" @click="loadOdd(event.league, match, event)">
                        <i class="fa fa-plus"></i>
                        {{ match.count_odd }}
                      </div>

                      <!-- Share -->
                      <div title="Compartilhar banner" class="btn-share" @click="openShareMatch(match, event)">
                        <i class="fa fa-picture-o fa-lg"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--End content Jogos-->



          <!--Right-->
          <div class="col-md-3" id="cupom-site">
            <div class="cupom-fixed">
              <div class="box box-widget overflow-hidden" style="margin-bottom: 65px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <!-- Header -->
                <div class="ticket-title-new box-header" style="background: var(--cupom_header--color, var(--sidebar--color, #173133)) !important; padding: 10px 15px;">
                  <h3 class="box-title" style="font-size: 13px !important; font-weight: 400; color: #fff !important; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa fa-ticket"></i>
                    BILHETE ({{ selection.length }})
                  </h3> 
                  <div class="box-tools pull-right">
                    <a style="cursor: pointer; color: #ffffff !important; opacity: 1 !important;" @click="removePalpites()">
                      <i class="fa fa-trash-o" style="font-size: 16px;"></i>
                    </a>
                  </div>
                </div>

                <div class="box-body" style="padding: 12px;">
                  <!-- Selections List -->
                  <div class="box-cupon-list" v-if="selection.length > 0" style="max-height: 400px; overflow-y: auto; margin-bottom: 15px; padding: 0 5px;">
                    <template v-for="(select, index) in selection">
                      <div class="ticket-divider" v-if="index > 0" :key="'div-'+index"></div>
                      <div class="box-cupon" :key="select.uuid" style="margin-bottom: 0; padding: 0; border: none; background: transparent;">
                      <div style="background-color: var(--card_header_bg--color, var(--container_jogos--color)); color: var(--card_header_text--color, #fff); padding: 5px 10px; font-size: 12px; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa fa-trophy"></i> {{ select.league }}</span>
                        <a @click="removePalpite(select.idOdd)" style="cursor: pointer;"><i class="fa fa-trash" style="color: #ffffff !important; opacity: 1 !important;"></i></a>
                      </div>
                      <div style="padding: 8px 10px;">
                        <div style="font-size: 13px; color: #444; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                          <img v-if="select.logo_home" :src="select.logo_home" style="width: 18px; height: 18px; object-fit: contain;">
                          {{ select.home }} 
                          <span style="color: #999; font-weight: 400;">X</span>
                          {{ select.away }}
                          <img v-if="select.logo_away" :src="select.logo_away" style="width: 18px; height: 18px; object-fit: contain;">
                        </div>
                        <div style="font-size: 11px; color: #e74c3c;">{{ select.date | formatDate() }} hs</div>
                        <div style="font-size: 12px; font-weight: 700; color: #333; margin-top: 5px;">{{ select.group_opp }}</div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2px;">
                          <span style="font-size: 13px; font-weight: 700; color: var(--primary-color, #3ca569) !important;">
                            {{ select.odd }} <span v-if="select.type == 'ao-vivo'" style="font-size: 10px;">({{ select.type }})</span>
                          </span>
                          <span style="font-size: 13px; color: #333;">{{ select.cotacao | formatCotacao() }}</span>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>

                  <!-- Inputs & Actions -->
                  <div class="form-apostas-premium">
                    <div class="form-group" style="margin-bottom: 10px;">
                      <div class="input-group" style="border: 1px solid #ddd; border-radius: 0;">
                        <span class="input-group-addon" style="background: var(--container_jogos--color, #fff); border: none; color: var(--search_bar_text--color, #666);"><i class="fa fa-user"></i></span> 
                        <input type="text" placeholder="Apostador" v-model="cliente" class="form-control" style="border: none; box-shadow: none; font-size: 13px; font-weight: 300;">
                      </div> 
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                      <div class="input-group" style="border: 1px solid #ddd; border-radius: 0;">
                        <span class="input-group-addon" style="background: var(--container_jogos--color, #fff); border: none; color: var(--search_bar_text--color, #666); font-size: 12px;">R$</span> 
                        <input type="number" v-model="apostado" @input="calculaCotacao()" class="form-control" placeholder="0,00" style="border: none; box-shadow: none; font-weight: 400; font-size: 14px; color: #555;">
                      </div> 
                    </div>

                    <div class="value-btn-group" style="display: flex; gap: 1px; margin-bottom: 15px;">
                      <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 5}" @click="setValApostado(5)">5,00</button> 
                      <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 10}" @click="setValApostado(10)">10,00</button> 
                      <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 20}" @click="setValApostado(20)">20,00</button> 
                      <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 30}" @click="setValApostado(30)">30,00</button>
                      <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 50}" @click="setValApostado(50)">50,00</button>
                    </div>

                    <div class="info-ticket" style="margin-top: 10px; padding: 0 5px;">
                      <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <div style="font-size: 12px; color: #777;">Cotação</div>
                        <div style="font-size: 12px; color: #777; font-weight: 600;">Possível Retorno</div>
                      </div> 
                      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="font-weight: 700; font-size: 15px; color: #333;">{{ total_cotacao | formatCotacao() }}</div>
                        <div style="font-weight: 700; font-size: 16px; color: #3ca569 !important;">{{ retorno | formatMoeda() }}</div>
                      </div>
                      
                      <button class="btn-block btn btn-lg btn-success btnSendBet" :disabled="loadingBtn" style="background-color: var(--cupom_apostar_btn--color, var(--container_jogos--color)) !important; color: #fff !important; border: none; border-radius: 4px; padding: 10px; font-weight: 800; font-size: 16px; transition: all 0.2s; opacity: 1 !important;" @click="enviarAposta()">
                        <span v-if="loadingBtn"><i class="fa fa-refresh fa-spin"></i> ENVIANDO...</span>
                        <span v-else><i class="fa fa-ticket"></i> APOSTAR</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Banner Abaixo do Bilhete -->
            <div class="banner-below-ticket mt-1" v-if="belowTicketBanners.length > 0">
              <div v-for="(banner, index) in belowTicketBanners" :key="'btb-'+index" class="mb-2">
                <a :href="banner.link || '#'">
                  <img :src="banner.image || banner.img" style="width: 100%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                </a>
              </div>
            </div>

          </div>

          <!--Btn Apostas mobile-->

          <!--End Btn Aposta-->
        </div>
      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Modals moved to bottom -->
    <div class="modal fade" id="modal-login-disabled">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title"><i class="fa fa-lock"></i> Login</h4>
          </div>

          <div class="modal-body box box-primary">
            <div class="login-box-body">
              <div
                class="alert alert-danger alert-dismissible"
                v-if="errorLogin"
              >
                <h4><i class="icon fa fa-ban"></i> Alerta!</h4>
                {{ messageError }}
              </div>

              <div class="form-group has-feedback">
                <input
                  type="text"
                  class="form-control"
                  v-model="username"
                  placeholder="Login"
                />
                <span
                  class="glyphicon glyphicon-envelope form-control-feedback"
                ></span>
              </div>
              <div class="form-group has-feedback">
                <input
                  type="password"
                  v-model="password"
                  @keyup.enter="login()"
                  class="form-control"
                  placeholder="Senha"
                />
                <span
                  class="glyphicon glyphicon-lock form-control-feedback"
                ></span>
              </div>
              <div class="row">
                <div class="col-xs-8">
                  <div class="checkbox icheck">
                    <label>
                      <div
                        class="icheckbox_square-blue"
                        aria-checked="false"
                        aria-disabled="false"
                        style="position: relative"
                      >
                        <input
                          type="checkbox"
                          style="
                            position: absolute;
                            top: -20%;
                            left: -20%;
                            display: block;
                            width: 140%;
                            height: 140%;
                            margin: 0px;
                            padding: 0px;
                            background: rgb(255, 255, 255);
                            border: 0px;
                            opacity: 0;
                          "
                        />
                        <ins
                          class="iCheck-helper"
                          style="
                            position: absolute;
                            top: -20%;
                            left: -20%;
                            display: block;
                            width: 140%;
                            height: 140%;
                            margin: 0px;
                            padding: 0px;
                            background: rgb(255, 255, 255);
                            border: 0px;
                            opacity: 0;
                          "
                        ></ins>
                      </div>
                    </label>
                  </div>
                </div>
                <div class="col-xs-4">
                  <button
                    type="submit"
                    @click="login()"
                    class="btn btn-primary btn-block btn-flat"
                  >
                    Acessar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="modal-register-disabled" aria-modal="true" role="dialog" class="modal fade">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" style="color: #333 !important;">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              style="color: #333 !important; opacity: 1;"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title" style="color: #333 !important;"><i class="fa fa-user-plus" style="color: #333 !important;"></i> Cadastro</h4>
          </div>
          <div class="modal-body box box-primary">
            <!-- <modal-register></modal-register> -->
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modal-caixa" v-show="logado">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-university"></i> MINHA CONTA
            </h4>
          </div>

          <div class="modal-body">
            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs">
                <li class="active">
                  <a href="#tab_1" data-toggle="tab" aria-expanded="true"
                    >MEUS DADOS</a
                  >
                </li>
                <li class="" v-if="account.nivel == 'cambista'">
                  <a
                    href="#tab_2"
                    data-toggle="tab"
                    aria-expanded="false"
                    @click="loadCaixa()"
                    >MEU CAIXA</a
                  >
                </li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                  <div class="box-body box-profile">
                    <img
                      class="profile-user-img img-responsive img-circle"
                      src="/dist/img/user4-128x128.jpg"
                      alt="User profile picture"
                    />

                    <h3 class="profile-username text-center">
                      {{ account.name }}
                    </h3>

                    <p class="text-muted text-center">{{ account.nivel }}</p>

                    <ul class="list-group list-group-unbordered">
                      <li class="list-group-item">
                        <b>Login</b>
                        <a class="pull-right">{{ account.username }}</a>
                      </li>
                      <li class="list-group-item">
                        <b>Telefone</b>
                        <a class="pull-right">{{ account.phone }}</a>
                      </li>
                    </ul>
                  </div>
                </div>

                <div class="tab-pane" id="tab_2" v-if="account.nivel == 'cambista'">
                  <div class="box-body" v-if="!loadingCaixa">
                    <ul class="list-group list-group-unbordered">
                      <li class="list-group-item">
                        <b>Total Entradas</b>
                        <a class="pull-right">R$ {{ caixa.entradas }}</a>
                      </li>
                      <li class="list-group-item">
                        <b>Total Saidas</b>
                        <a class="pull-right">R$ {{ caixa.saidas }}</a>
                      </li>
                      <li class="list-group-item">
                        <b>Comissão</b>
                        <a class="pull-right">R$ {{ caixa.comissao }}</a>
                      </li>
                      <li class="list-group-item">
                        <b>Total Liquido</b>
                        <a class="pull-right">R$ {{ caixa.liquido }}</a>
                      </li>
                    </ul>
                  </div>
                  <div class="box-body text-center" v-else>
                    <clip-loader
                      :loading="loadingCaixa"
                      :color="'#3c8dbc'"
                      :size="'45px'"
                    ></clip-loader>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="modal-relatorio" v-show="logado">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-sticky-note"></i> MEUS BILHETES
            </h4>
          </div>

          <div class="modal-body">
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Início:</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input
                      type="date"
                      class="form-control pull-right"
                      v-model="data_inicio"
                    />
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Fim:</label>
                  <div class="input-group date">
                    <div class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                    </div>
                    <input
                      type="date"
                      class="form-control pull-right"
                      v-model="data_fim"
                    />
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Status:</label>
                  <select class="form-control" v-model="status">
                    <option value="">Todos</option>
                    <option value="Aberto">Aberto</option>
                    <option value="Venceu">Venceu</option>
                    <option value="Perdeu">Perdeu</option>
                    <option value="Cancelado">Cancelado</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label style="color: #fff">.</label>
                  <button
                    class="btn btn-primary btn-block"
                    @click="loadRelatorio()"
                  >
                    PESQUISAR
                  </button>
                </div>
              </div>
            </div>
            <br />
            <div class="row" v-if="!loading">
              <div class="col-md-12">
                <div class="table-responsive">
                  <table class="table no-margin">
                    <thead>
                      <tr>
                        <th>Cód</th>
                        <th>Data</th>
                        <th>Apostado</th>
                        <th>Retorno</th>
                        <th>Status</th>
                        <th>Ver</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="bilhete in relatorio" :key="bilhete.id">
                        <td>
                          <a href="javascript:void(0)">{{ bilhete.cupom }}</a>
                        </td>
                        <td>{{ bilhete.created_at | formatDate() }}</td>
                        <td>R$ {{ bilhete.valor_apostado }}</td>
                        <td>R$ {{ bilhete.valor_retorno }}</td>
                        <td>
                          <span
                            class="label"
                            :class="{
                              'label-success': bilhete.status == 'Venceu',
                              'label-danger': bilhete.status == 'Perdeu',
                              'label-warning': bilhete.status == 'Aberto',
                              'label-default': bilhete.status == 'Cancelado',
                            }"
                            >{{ bilhete.status }}</span
                          >
                        </td>
                        <td>
                          <button
                            class="btn btn-primary btn-xs"
                            @click="verBilhete(bilhete.id)"
                          >
                            <i class="fa fa-eye"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="row text-center" v-else>
              <clip-loader
                :loading="loading"
                :color="'#3c8dbc'"
                :size="'45px'"
              ></clip-loader>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>


    <div class="modal fade" id="modal-validar-pin">
      <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.4);">
          <div class="modal-header" style="background: var(--sidebar--color); color: #fff; border-radius: 12px 12px 0 0; padding: 20px;">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
              style="color: #fff; opacity: 0.8;"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title" style="font-weight: 800; letter-spacing: 1px;"><i class="fa fa-lock"></i> VALIDAR PIN</h4>
          </div>
          <div class="modal-body box box-primary" style="padding: 25px;">
            <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Informe o código do bilhete (PIN) para validar e transformar em aposta real.</p>
            <div class="form-group has-feedback" style="margin-bottom: 20px;">
              <input
                type="text"
                class="form-control"
                v-model="pin"
                placeholder="Ex: ABC123"
                style="height: 50px; font-size: 20px; font-weight: 800; text-align: center; text-transform: uppercase; border-radius: 8px; border: 2px solid #eee; background: var(--container_jogos--color, #f9f9f9);"
              />
              <span class="fa fa-key form-control-feedback" style="line-height: 50px; color: var(--sidebar--color);"></span>
            </div>
            <button class="btn btn-primary btn-block" @click="validaPin()" style="height: 50px; font-weight: 800; font-size: 16px; border-radius: 8px; background-color: var(--sidebar--color) !important; border: none; text-transform: uppercase; letter-spacing: 1px;">
              <i class="fa fa-check-circle"></i> VALIDAR AGORA
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modal-match-old-2" v-show="false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">
                <i class="fa fa-close"></i>
              </span>
            </button>
            <h4 class="modal-title">
              <i class="fa fa-soccer-ball-o"></i> MAIS OPÇÕES
            </h4>
          </div>

          <div class="modal-body">
            <div class="row" v-if="!loading_odds">
              <div class="col-md-12" v-for="group in mercados" :key="group.name">
                <div class="box box-solid box-default">
                  <div class="box-header with-border">
                    <h3 class="box-title">{{ group.name }}</h3>
                  </div>
                  <div class="box-body">
                    <div class="row">
                      <div
                        class="col-md-4"
                        v-for="odd in group.odds"
                        :key="odd.id"
                      >
                        <button
                          class="btn btn-default btn-block btn-flat"
                          style="margin-bottom: 5px"
                          :class="{'btn-primary': selectionsIds.includes(odd.id)}"
                          @click="addPalpite(odd.uuid, odd.id, match.sport, match.id, group.name, odd.odd, odd.cotacao, match.league, match.date, match.home, match.away, odd.type, odd.cotacaoOriginal, match.logo_home, match.logo_away)"
                        >
                          {{ odd.odd }} - {{ odd.cotacao | formatCotacao() }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row text-center" v-else>
              <clip-loader
                :loading="loading_odds"
                :color="'#3c8dbc'"
                :size="'45px'"
              ></clip-loader>
            </div>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>

    <footer class="main-footer" style="background: var(--footer_bg--color, #f4f6f9); padding: 30px 20px 15px; color: var(--footer_text--color, #555); border-top: 1px solid #dee2e6;">
      <div class="container-fluid">

        <!-- 4 Colunas do Rodapé -->
        <div class="row" style="margin-bottom: 25px;">

          <!-- Coluna 1: Sobre Nós -->
          <div class="col-sm-3">
            <h4 style="font-size: 15px; font-weight: 700; color: #333; margin-bottom: 10px;">Sobre Nós</h4>
            <p style="font-size: 13px; color: #777; line-height: 1.6;">{{ server.footer_sobre || 'Conte um pouco sobre sua plataforma.' }}</p>
          </div>

          <!-- Coluna 2: Aviso de Idade -->
          <div class="col-sm-3">
            <h4 style="font-size: 15px; font-weight: 700; color: #333; margin-bottom: 10px;">Aviso de Idade</h4>
            <p style="font-size: 13px; color: #777; line-height: 1.6;">
              <strong>ATENÇÃO:</strong> Este site é destinado apenas a maiores de 18 anos.
              Ao continuar navegando, você confirma que possui idade legal para participar de jogos de azar.
            </p>
          </div>

          <!-- Coluna 3: Suporte e Tratamento -->
          <div class="col-sm-3">
            <h4 style="font-size: 15px; font-weight: 700; color: #333; margin-bottom: 10px;">Suporte e Tratamento para Dependências</h4>
            <p style="font-size: 13px; color: #777; line-height: 1.6;">
              Se você ou alguém que conhece está enfrentando dificuldades com o vício em jogos de azar, existem recursos disponíveis.
              Acesse <a href="https://www.gamcare.org.uk/" target="_blank" style="color:#3c8dbc;">GamCare</a> ou
              <a href="https://www.gambleaware.co.uk/" target="_blank" style="color:#3c8dbc;">GambleAware</a> para obter suporte e tratamento.
            </p>
          </div>

          <!-- Coluna 4: Redes Sociais -->
          <div class="col-sm-3">
            <h4 style="font-size: 15px; font-weight: 700; color: #333; margin-bottom: 10px;">Redes Sociais</h4>
            <div>
              <a v-if="site_info.social_instagram" :href="site_info.social_instagram" target="_blank"
                style="display:inline-block; margin-right:10px; margin-bottom:8px; color:#e1306c; font-size:26px;">
                <i class="fa fa-instagram"></i>
              </a>
              <a v-if="site_info.social_facebook" :href="site_info.social_facebook" target="_blank"
                style="display:inline-block; margin-right:10px; margin-bottom:8px; color:#1877f2; font-size:26px;">
                <i class="fa fa-facebook-square"></i>
              </a>
              <a v-if="site_info.social_twitter" :href="site_info.social_twitter" target="_blank"
                style="display:inline-block; margin-right:10px; margin-bottom:8px; color:#1da1f2; font-size:26px;">
                <i class="fa fa-twitter-square"></i>
              </a>
              <a v-if="site_info.social_youtube" :href="site_info.social_youtube" target="_blank"
                style="display:inline-block; margin-right:10px; margin-bottom:8px; color:#ff0000; font-size:26px;">
                <i class="fa fa-youtube-play"></i>
              </a>
              <a v-if="site_info.whatsapp_number" :href="'https://wa.me/' + site_info.whatsapp_number" target="_blank"
                style="display:inline-block; margin-right:10px; margin-bottom:8px; color:#25d366; font-size:26px;">
                <i class="fa fa-whatsapp"></i>
              </a>
              <p v-if="!site_info.social_instagram && !site_info.social_facebook && !site_info.social_twitter && !site_info.social_youtube && !site_info.whatsapp_number"
                style="font-size: 13px; color: #888;">
                Configure as redes sociais no painel Admin.
              </p>
            </div>
          </div>

        </div>

        <!-- Logos de Responsabilidade -->
        <div class="row text-center" style="margin-bottom: 15px; opacity: 0.75;">
          <div class="col-md-12">
            <a href="https://www.gamcare.org.uk/" target="_blank">
              <img src="/img/gamcare-footer.png" alt="GamCare" style="height: 32px; margin: 4px 12px; filter: grayscale(30%);">
            </a>
            <a href="https://www.gambleaware.org/" target="_blank">
              <img src="/img/gambleaware-logo.png" alt="BeGambleAware" style="height: 28px; margin: 4px 12px; filter: grayscale(30%);">
            </a>
            <img src="/img/more18.png" alt="18+" style="height: 32px; margin: 4px 12px;">
            <a href="https://www.gamblersanonymous.org.uk/" target="_blank">
              <img src="/img/gamblers-anonymous-logo.png" alt="Gamblers Anonymous" style="height: 32px; margin: 4px 12px; filter: grayscale(30%);">
            </a>
            <img src="/img/pix-106.png" alt="PIX" style="height: 32px; margin: 4px 12px; filter: grayscale(30%);">
          </div>
        </div>

        <hr style="border-top: 1px solid #ddd; margin: 15px 0;">

        <!-- Copyright e Versão -->
        <div class="row">
          <div class="col-sm-6" style="font-size: 13px;">
            <strong>Copyright &copy; {{ server.year }} <a href="/" style="color: var(--container_jogos--color);">{{ server.logo }}</a>.</strong> Todos os direitos reservados.
          </div>
          <div class="col-sm-6 text-right" style="font-size: 12px; color: #888;">
            <div class="pull-right hidden-xs">
              <b>Versão</b> 2.1.0
            </div>
          </div>
        </div>

      </div>
    </footer>

        <!-- MODAL CUPOM DE APOSTAS - MOBILE -->
    <div class="modal fade" id="modal-cupon" tabindex="-1" role="dialog">
      <div class="modal-dialog" style="margin: 10px auto; max-width: 500px;">
        <div class="modal-content" style="background: var(--modal_bg--color, var(--background--color, #fff));">
          <div class="modal-header" style="background: var(--container_jogos--color, #f4f4f4); padding: 10px 15px; border-bottom: 1px solid #ddd;">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#333; opacity:1;">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" style="color:#333; font-weight: 700;">
              <i class="fa fa-ticket"></i> Bilhete ({{ selection.length }})
            </h4>
          </div>

          <div class="modal-body" style="padding: 10px; background: var(--modal_bg--color, var(--background--color, #fff));">

            <!-- CAMPO APOSTADOR -->
            <div class="input-group" style="margin-bottom: 8px;">
              <span class="input-group-addon"><i class="fa fa-user"></i></span>
              <input
                type="text"
                class="form-control"
                placeholder="Apostador"
                v-model="cliente"
              />
            </div>

            <!-- CAMPO VALOR -->
            <div class="input-group" style="margin-bottom: 8px;">
              <span class="input-group-addon">R$</span>
              <input
                type="number"
                class="form-control"
                placeholder="Valor Apostado"
                v-model="apostado"
                @keyup="calculaCotacao()"
                min="0"
                step="0.50"
              />
            </div>

            <!-- BOTÕES DE VALOR RÁPIDO -->
            <div class="value-btn-group" style="display: flex; gap: 1px; margin-bottom: 15px;">
              <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 5}" @click="setValApostado(5)">5,00</button> 
              <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 10}" @click="setValApostado(10)">10,00</button> 
              <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 20}" @click="setValApostado(20)">20,00</button> 
              <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 30}" @click="setValApostado(30)">30,00</button>
              <button class="btn-valor" style="flex: 1; border: none; border-radius: 0; padding: 8px 0; font-size: 10px; font-weight: 400; color: #fff; " :class="{active: apostado == 50}" @click="setValApostado(50)">50,00</button>
            </div>

            <!-- COTAÇÃO E RETORNO -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding: 4px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
              <span style="font-size: 13px;">
                Cotação <br>
                <strong style="font-size: 18px;">{{ total_cotacao | formatCotacao() }}</strong>
              </span>
              <span style="font-size: 13px; text-align: right;">
                Possível Retorno <br>
                <strong style="font-size: 18px; color: #3ca569 !important;">{{ retorno | formatMoeda() }}</strong>
              </span>
            </div>

            <!-- BOTÃO APOSTAR -->
            <button
              class="btn btn-block btn-lg"
              style="background-color: var(--cupom_apostar_btn--color, #3ca569) !important; color: #ffffff !important; font-weight: 800; font-size: 16px; border-radius: 6px; margin-bottom: 12px;"
              @click="enviarAposta()"
              :disabled="loadingBtn"
            >
              <i class="fa fa-ticket"></i> Apostar
            </button>

            <!-- LOADING -->
            <div class="loadSendBet" style="text-align: center; display: none;">
              <i class="fa fa-refresh fa-spin"></i> Validando Aposta...
            </div>

            <hr style="border-style: dashed; margin: 8px 0;">

            <!-- LISTA DE PALPITES -->
            <template v-for="(select, index) in selection">
              <div class="ticket-divider" v-if="index > 0" :key="'m-div-'+index"></div>
              <div :key="select.id" style="padding: 8px 0; margin-bottom: 0;">
              <div style="background: var(--card_header_bg--color, var(--container_jogos--color)); color: var(--card_header_text--color, #fff); padding: 5px 8px; border-radius: 3px; margin-bottom: 5px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; font-weight: 700; color: #ffffff !important;">
                  <i class="fa fa-trophy"></i> {{ select.league }}
                </span>
                <span @click="removePalpite(select.idOdd)" style="cursor: pointer;">
                  <i class="fa fa-trash" style="color: #ffffff !important; opacity: 1 !important;"></i>
                </span>
              </div>
              <div style="padding: 0 5px;">
                <div style="font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                  <img v-if="select.logo_home" :src="select.logo_home" style="width: 22px; height: 22px; object-fit: contain;">
                  {{ select.home }} 
                  <span style="color: #999; font-weight: 400; font-size: 12px;">X</span> 
                  {{ select.away }}
                  <img v-if="select.logo_away" :src="select.logo_away" style="width: 22px; height: 22px; object-fit: contain;">
                </div>
                <div style="color: #e74c3c; font-size: 12px;">{{ select.date | formatDate() }} hs</div>
                <div style="font-size: 13px;">{{ select.sport }}</div>
                <div style="font-size: 13px; color: #333;"><b>{{ select.group_opp }}</b></div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                  <span style="color: #3ca569 !important; font-weight: 700; font-size: 13px;">
                    <span v-if="select.type == 'ao-vivo'" style="color: #3ca569 !important;">{{ select.odd }} ({{ select.type }})</span>
                    <span v-else style="color: #3ca569 !important;">{{ select.odd }}</span>
                  </span>
                  <span style="font-weight: 700; font-size: 15px;">{{ select.cotacao | formatCotacao() }}</span>
                </div>
              </div>
            </div>
          </template>

          </div><!-- /.modal-body -->
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div>
    <!-- /MODAL CUPOM -->

    <!-- MODAL SHARE BANNER -->
    <div class="modal fade" id="modal-share" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content" style="background: var(--cupom_header--color, var(--sidebar--color, #173133)); color: #fff; border: 1px solid var(--container_jogos--color);">
          <div class="modal-header" style="border-bottom: 1px solid #333; padding: 12px 15px;">
            <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 1;">&times;</button>
            <h4 class="modal-title" style="font-size: 15px;"><i class="fa fa-picture-o"></i> Salve ou copie essa imagem, e compartilhe!</h4>
          </div>
          <div class="modal-body text-center" style="padding: 15px; max-height: 75vh; overflow-y: auto;">
            <div v-if="loading_share" style="padding: 60px 0;">
              <i class="fa fa-refresh fa-spin fa-3x" style="color: var(--container_jogos--color);"></i>
              <p style="margin-top: 15px; font-weight: 500; color: #aaa;">Gerando banner personalizado...</p>
            </div>
            <div v-else>
              <img v-if="image_share" :src="image_share" style="width: 100%; max-width: 360px; border-radius: 8px; box-shadow: 0 8px 25px rgba(0,0,0,0.6);">
              <div v-else class="alert alert-warning" style="background: rgba(255,193,7,0.1); border-color: #ffc107; color: #ffc107;">
                Não foi possível gerar o banner para este jogo.
              </div>
              
              <div class="share-actions" v-if="image_share" style="margin-top: 18px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button class="btn" style="background: var(--container_jogos--color); color: #fff; font-weight: 700; padding: 10px 20px; border-radius: 6px;" @click="downloadBanner()">
                  <i class="fa fa-download"></i> Baixar Imagem
                </button>
                <button class="btn" style="background: #25D366; color: #fff; font-weight: 700; padding: 10px 20px; border-radius: 6px;" @click="shareImageWhatsapp()">
                  <i class="fa fa-whatsapp"></i> WhatsApp
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL REGULAMENTO PREMIUM -->
    <div class="modal fade" id="modal-regulamento" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" aria-label="Close" class="close">
              <span aria-hidden="true"><i class="fa fa-close"></i></span>
            </button>
            <h4 class="modal-title"><i class="fa fa-star" style="color: #060606 !important;"></i> Regulamento</h4>
          </div>
          <div class="modal-body box box-primary" style="max-height: 70vh; overflow-y: auto;">
            <div v-if="!regulamento" style="text-align: center; padding: 40px; color: #64748b;">
              <i class="fa fa-refresh fa-spin fa-2x" style="color: #3c8dbc; margin-bottom: 15px;"></i>
              <p style="font-size: 14px;">Carregando...</p>
            </div>
            <div v-else v-html="regulamento" class="regulamento-content-demo"></div>
          </div>
          <div class="modal-footer" style="text-align: center; border-top: 1px solid #f4f4f4;">
            <button type="button" data-dismiss="modal" @click="formRegister.termos = true" class="btn btn-success btn-lg" style="font-weight: 700; padding: 10px 60px; border-radius: 4px; text-transform: uppercase;">
              <i class="fa fa-check"></i> LI E ACEITO
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL MAIS MERCADOS -->
    <div class="modal fade" id="modal-match" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border: none; border-radius: 4px; overflow: hidden; background: #eaebec;">
          
          <!-- Header Customizado (Sem a classe modal-header para evitar conflito com flexbox do Bootstrap) -->
          <div style="background-color: #2b323a; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; border-radius: 4px 4px 0 0;">
            <h4 style="font-weight: 500; color: #ffffff; margin: 0; font-size: 16px; display: flex; align-items: center;">
              <i class="fa fa-trophy" style="margin-right: 10px; color: #ffffff; font-size: 16px;"></i> {{ match.league || liga }}
            </h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #ffffff; opacity: 1; text-shadow: none; font-size: 20px; font-weight: bold; margin: 0; outline: none; line-height: 1; padding: 0; float: none;">
              <span aria-hidden="true"><i class="fa fa-times"></i></span>
            </button>
          </div>
          
          <!-- Banner Principal -->
          <div style="background-image: url('/img/login_bg.png'); background-size: cover; background-position: center; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.6) 100%);"></div>

            <div style="position: relative; z-index: 1; padding: 35px 20px 25px 20px; text-align: center; color: #fff;">
              
              <div style="display: flex; justify-content: center; align-items: center; gap: 40px; margin-bottom: 25px;">
                <!-- Casa -->
                <div style="flex: 1; text-align: right; display: flex; align-items: center; justify-content: flex-end; gap: 20px;">
                  <h4 style="margin: 0; font-size: 24px; font-weight: 300; letter-spacing: -0.5px;">{{ match.home }}</h4>
                  <img v-if="match.logo_home" :src="match.logo_home" style="width: 75px; height: 75px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));">
                </div>
                
                <!-- Separador -->
                <div style="display: flex; align-items: center; justify-content: center;">
                  <span style="font-size: 28px; font-weight: 100; color: rgba(255,255,255,0.4); font-family: sans-serif;">X</span>
                </div>

                <!-- Fora -->
                <div style="flex: 1; text-align: left; display: flex; align-items: center; justify-content: flex-start; gap: 20px;">
                  <img v-if="match.logo_away" :src="match.logo_away" style="width: 75px; height: 75px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));">
                  <h4 style="margin: 0; font-size: 24px; font-weight: 300; letter-spacing: -0.5px;">{{ match.away }}</h4>
                </div>
              </div>
              
              <!-- Badge Data/Hora -->
              <div style="display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.05); padding: 8px 20px; border-radius: 30px; font-size: 13px; font-weight: 300; border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(4px);">
                <span v-if="!live" style="display: flex; align-items: center; gap: 8px;">
                  <i class="fa fa-calendar-check-o" style="color: rgba(255,255,255,0.6);"></i> {{ match.date | formatDate() }}
                </span>
                <span v-if="live" class="label label-danger pisca" style="background-color: #ff3333 !important; border-radius: 20px; padding: 3px 12px; font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">
                  <i class="fa fa-circle" style="font-size: 8px; margin-right: 5px;"></i> Ao Vivo {{ match.time ? '- ' + match.time + "'" : '' }}
                </span>
              </div>

            </div>
          </div>
          
          <!-- Mercados -->
          <div class="modal-body" style="background-color: #eaebec; padding: 15px; max-height: 65vh; overflow-y: auto; position: relative;">
            
            <!-- Modal Info Mercado (Overlay) -->
            <div v-if="showMarketInfo" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
              <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 400px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                <div style="background: #1e4b82; color: #fff; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                  <h4 style="margin: 0; font-size: 16px; font-weight: bold;"><i class="fa fa-info-circle"></i> Como funciona?</h4>
                  <button @click="showMarketInfo = false" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
                </div>
                <div style="padding: 20px;">
                  <p style="font-weight: bold; color: #333; margin-bottom: 10px;">{{ selectedMarketName }}</p>
                  <p style="color: #666; font-size: 14px; line-height: 1.5;">
                    {{ getMarketDescription(selectedMarketName) }}
                  </p>
                  <div style="text-align: right; margin-top: 20px;">
                    <button @click="showMarketInfo = false" style="background: #f39c12; color: #fff; border: none; padding: 8px 25px; border-radius: 4px; font-weight: bold; cursor: pointer;">Entendi</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Barra de Pesquisa -->
            <div style="margin-bottom: 15px; position: relative;">
              <i class="fa fa-search" style="position: absolute; left: 15px; top: 12px; color: #888;"></i>
              <input type="text" v-model="searchMercado" placeholder="Buscar mercado..." 
                     style="width: 100%; padding: 10px 15px 10px 40px; border: 1px solid #ddd; border-radius: 25px; outline: none; background: #fff; font-size: 14px;">
            </div>

            <div v-if="loading_odds" style="padding: 40px; text-align: center;">
              <i class="fa fa-refresh fa-spin fa-3x" style="color: var(--container_jogos--color, #3c8dbc);"></i>
              <p style="margin-top: 15px; color: #666;">Carregando cotações...</p>
            </div>
            
            <div v-else>
              <div v-for="(mercado, mIndex) in mercados" :key="mIndex" v-show="!searchMercado || translateLabel(mercado.name).toLowerCase().includes(searchMercado.toLowerCase()) || mercado.odds.some(o => translateLabel(o.odd).toLowerCase().includes(searchMercado.toLowerCase()))" style="margin-bottom: 15px;">
                <!-- Header Mercado -->
                <div @click="toggleMarket(mercado.name)" style="background-color: var(--botao_aposta--color, #3ca569); color: #fff; padding: 10px 15px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; cursor: pointer; transition: opacity 0.2s;">
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <i :class="['fa', isMarketCollapsed(mercado.name) ? 'fa-chevron-right' : 'fa-chevron-down']" style="font-size: 12px; opacity: 0.7; width: 14px;"></i>
                    <span style="font-weight: bold; font-size: 14px; text-shadow: 1px 1px 1px rgba(0,0,0,0.1);">{{ translateLabel(mercado.name) }}</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">
                      {{ mercado.odds.length }}
                    </span>
                    <i class="fa fa-info-circle" @click.stop="selectedMarketName = translateLabel(mercado.name); showMarketInfo = true" style="font-size: 18px; cursor: pointer; opacity: 0.8; hover: opacity 1;"></i>
                  </div>
                </div>
                
                <!-- Odds Grid -->
                <div v-if="!isMarketCollapsed(mercado.name)" class="row" style="margin-left: -4px; margin-right: -4px;">
                  <div class="col-md-4 col-sm-6 col-xs-12" v-for="(odd, oIndex) in mercado.odds" :key="oIndex" v-show="!searchMercado || translateLabel(mercado.name).toLowerCase().includes(searchMercado.toLowerCase()) || translateLabel(odd.odd).toLowerCase().includes(searchMercado.toLowerCase())" style="padding-left: 4px; padding-right: 4px; margin-bottom: 8px;">
                    
                    <div class="market-option" 
                         data-dismiss="modal"
                         @click="odd.cotacao > 0 ? addPalpite(odd.uuid, odd.id, match.sport, match.id || match.event_id, odd.group_opp, odd.odd, odd.cotacao, match.league || liga, match.date, match.home, match.away, odd.type, odd.cotacaoOriginal, match.logo_home, match.logo_away) : null"
                         :style="isPalpiteActive(odd.id) ? 'background: #ffffff; border: 1px solid var(--btn_selecionado-color, #23a73d); border-radius: 3px; display: flex; justify-content: space-between; align-items: stretch; cursor: pointer; overflow: hidden; height: 38px; transition: all 0.2s;' : 'background: #ffffff; border: 1px solid #dcdcdc; border-radius: 3px; display: flex; justify-content: space-between; align-items: stretch; cursor: pointer; overflow: hidden; height: 38px; transition: all 0.2s;'">
                      
                      <!-- Lado Esquerdo (Nome) -->
                      <div style="padding: 0 10px; display: flex; align-items: center; font-size: 13px; color: #444; flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ translateLabel(odd.odd) }}
                      </div>
                      
                      <!-- Lado Direito (Valor) -->
                      <div :style="isPalpiteActive(odd.id) ? 'background: var(--btn_selecionado-color, #23a73d); color: #fff; padding: 0 12px; font-weight: bold; font-size: 13px; display: flex; align-items: center; justify-content: center; min-width: 55px; border-left: 1px solid var(--btn_selecionado-color, #23a73d);' : 'background: #2a323b; color: #fff; padding: 0 12px; font-weight: bold; font-size: 13px; display: flex; align-items: center; justify-content: center; min-width: 55px; border-left: 1px solid #dcdcdc;'">
                        <span v-if="odd.cotacao > 0">{{ odd.cotacao | formatCotacao() }}</span>
                        <i v-else class="fa fa-lock"></i>
                      </div>
                      
                    </div>
                  </div>
                </div>
                
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>

  </div> <!-- FIM DA WRAPPER -->
</template>

<style>
@media only screen and (max-device-width: 480px) {
  .imgTeams {
    width: 20px;
    height: 20px;
  }
}

 
.logo-lg {
  font-family: "Antipasto", Helvetica;
  font-size: 27px;
  font-weight: bold;
  letter-spacing: 5px;
}

.logo-mini {
  font-family: "Antipasto", Helvetica;
  font-size: 30px;
  font-weight: bold;
  letter-spacing: 3px;
}

.carousel-slide{
  margin: auto;
  max-width: 100%;
  min-width: 100%;
}

.timeMatch {
  color: #222d32;
  font-size: 20px;
}

.real-time {
  background-image: url("~/img/soccer.jpg");
  background-size: 100% 100%;
  color: #dcdcdc;
  margin-bottom: 20px;
  width: 100%;
  height: auto;
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  text-align: center;
}
.placar {
  width: 100%;
  height: auto;
  padding: 6px;
  background-color: rgba(0, 0, 0, 0.5);
}
.score-real-time {
  font-size: 20px;
}

.center-loader {
  width: 100%;
  height: auto;
  padding: 4px;
  text-align: center !important;
}

.tabela-class-home {
  background: #3c8dbc;
  color: #fff;
}
.bilhetes-content {
  width: 100%;
  height: auto;
  padding: 8px;
  background: var(--cupom_body_bg--color, #fff);
  border-radius: 7px;
}

.pin {
  font-size: 25px;
}
.dados-logado p {
  margin-bottom: 0px;
  margin-top: 0px;
}

.relatorio {
  width: 100%;
  height: auto;
  padding: 4px;
  margin-bottom: 15px;
}
.header-print-share {
  width: 100%;
  height: 50px;
  background: #ffe4b5;
}

.share-float {
  float: right;
}

.pre-aposta-resul {
  padding: 8px;
  text-align: center;
  background: #333333;
  color: #dcdcdc;
}

.btn-valor-mobile {
  text-align: center;
}
.imgTeams {
  width: 40px;
  height: 40px;
}

.regras {
  width: 100%;
  height: auto;
  /* text-align: justify; */
  font-size: 12px;
  margin-top: 20px;
}

.send-whats {
  color: #008d4c;
  font-size: 40px;
}
.send-print {
  color: #333333;
  font-size: 40px;
  margin-right: 15px;
  cursor: pointer;
}

.busca-time {
  margin-bottom: 10px;
  margin-top: 10px;
  width: 100%;
  margin-right: 10px;
  height: 35px;
  padding: 12px;
  border: none;
  border-radius: 3px;
  color: #000;
}

.titulo-grupo {
  width: 100%;
  height: auto;
  font-size: 20px;
  page-break-after: 10px;
  background: #1d0053;
  color: #fff;
  text-align: center;
}

.odd-match-plus {
  width: 100%;
  height: auto;
  color: #222d32;
  float: left;
  margin-top: 10px;
  font-size: 15px;
  padding: 4px;
  border-bottom: 1px solid #dcdcdc;
}

.odd-match-plus-left {
  float: left;
}

.btns-pricin {
  text-align: center;
}

.odd-match-plus-right {
  float: right;
  background: #07505e;
  color: #fff;
  border: 1px solid #fafafa;
  padding: 8px;
  width: 60px;
  height: 40px;
  text-align: center;
  cursor: pointer;
}

.odd-match-plus-right-selecionado {
  float: right;
  background: #d60000;
  color: #000;
  padding: 8px;
  width: 60px;
  height: 40px;
  text-align: center;
  cursor: pointer;
  border: 1px solid #000;
}

.match-odd-data {
  color: #014586;
}
#cod {
  text-align: center;
}

.body-bilhete {
  background: #f8ecc2;
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
  border-bottom: 1px #000 dashed;
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
  height: auto;
  margin-bottom: 10px;
  border-bottom: 1px #000 dashed;
  font-size: 13px;
  padding-bottom: 5px;
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
  border-top: 1px #000 dashed;
  border-bottom: 1px #000 dashed;
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
  background: #00c0ef;
  color: #fff;
}

.Perdeu {
  background: #ff0000;
  color: #fff;
}
.Ganhou {
  background: #008d4c;
  color: #fff;
}
.Cancelado {
  background: #e69222;
  color: #fff;
}

.loadSendBet {
  visibility: hidden;
  font-size: 25px;
}

/*Efeito piscas
  */

@keyframes pisca {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
  80% {
    opacity: 0;
  }
}
.pisca {
  -webkit-animation: pisca 0.75s linear infinite;
  -moz-animation: pisca 0.75s linear infinite;
  -ms-animation: pisca 0.75s linear infinite;
  -o-animation: pisca 0.75s linear infinite;
  animation: pisca 0.75s linear infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}

.live-match-row {
  background: linear-gradient(90deg, rgba(204, 51, 51, 0.03) 0%, transparent 100%);
  border-left: 3px solid #cc3333 !important;
  transition: background 0.3s ease;
}
.live-match-row:hover {
  background: linear-gradient(90deg, rgba(204, 51, 51, 0.08) 0%, transparent 100%);
}

/* UPDATE 28/07 - 13h - por: João */

.modal-bilhete {
  max-height: 100vh !important;
}

.tempo {
  width: 100%;
  border: 1px solid #eee;
  text-align: center;
}

.icon,
.sc1,
.sc2,
.sc3 {
  display: inline-block;
}

.info,
.sc1,
.sc2,
.sc3,
.tempo {
  vertical-align: middle;
  box-sizing: border-box;
}

.overflow-handle,
.sc1,
.sc2,
.sc3 {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.info,
.sc1,
.sc2,
.sc3,
.score,
.scoreContainer,
.scoreSpan,
.tempo,
.tempoSpan {
  box-sizing: border-box;
}

.scoreContainer {
  width: 100%;
  max-width: 640px;
  height: 280px;
  border: 1px solid #eee;
  font-family: verdana !important;
  margin: 0 auto;
}

.scoreContainer.tenis {
  height: 340px !important;
}

.score {
  width: 100%;
  height: 50px;
  border: 1px solid #eee;
}

.tempo {
  height: 30px;
}

.info {
  height: 200px;
}

.info.soccer {
  /* background: url(../img/widget_aovivo/soccer.jpg); */
  background-size: 100% 100%;
}

.info.basquete {
  /* background: url(../img/widget_aovivo/basquete.jpg); */
  background-size: 100% 100%;
}

.info.hockey {
  /* background: url(../img/widget_aovivo/hockey.jpg); */
  background-size: 100% 100%;
}

.info.tenis {
  /* background: url(../img/widget_aovivo/tenis.jpg); */
  background-size: 100% 100%;
}

.info.volei {
  /* background: url(../img/widget_aovivo/volei.jpg); */
  background-size: 100% 100%;
}

.icon {
  background: url("~/img/icons.svg") no-repeat;
  width: 12px;
  height: 12px;
  margin: 0 auto;
}

.left {
  text-align: left !important;
}

.cell-soccer,
.cell-volei,
.sc1,
.sc2,
.sc3 {
  text-align: center;
}

.icon.corner {
  background-position: -12px -12px;
}

.icon.yellow-card {
  background-position: -73px 0;
}

.icon.red-card {
  background-position: -48px 0;
}

.icon.yellow-ball {
  background-position: 0 0;
}

.tableInfo {
  margin: 0px auto;
  border: 1px solid #444;
  height: 100px;
  width: 95%;
  border-collapse: collapse;
}

.tableInfo th {
  padding-top: 5px;
  padding-bottom: 5px;
}

.table-header {
  background-color: #333;
  color: #fff;
  opacity: 0.95;
  border: 0;
}

.table-row {
  background-color: #444;
  color: #fff;
  opacity: 0.95;
  border-top: 1px solid #444;
  border-bottom: 1px solid #444;
}

.cell-soccer,
.cell-volei {
  width: 25px;
  border: 0;
}

.cell-basquete,
.cell-hockey,
.cell-tenis {
  width: 30px;
  text-align: center;
  border: 0;
}

.sc1 {
  width: 39%;
  height: 50px;
}

.sc2 {
  width: 20%;
  height: 48px;
  background-color: #b6041a;
  color: #fff;
}

.sc3 {
  width: 39%;
  height: 50px;
  float: right;
}

.scoreSpan {
  line-height: 50px;
}

.placar-soccer,
.placar-tenis {
  font-size: 30px;
}

.placar-basquete {
  font-size: 20px;
}

.placar-volei {
  font-size: 25px;
}

.placar-hockey {
  font-size: 30px;
}

.tempoSpan {
  line-height: 30px;
}

.padding-10 {
  padding-left: 10px;
}

@media only screen and (max-width: 480px) {
  .scoreContainer {
    font-size: 12px !important;
  }
  .placar-soccer,
  .placar-tenis {
    font-size: 20px;
  }
  .placar-basquete {
    font-size: 13px;
  }
  .placar-hockey {
    font-size: 20px;
  }
  .placar-volei {
    font-size: 16px;
  }
}

.table-row td {
  color: white !important;
}
</style>

<script>
export default {
  props: ["site_info", "account", "configuracoes"],
  beforeCreate: function () {
    this.token = localStorage.getItem("token");
  },
  created() {
    window.moment.locale("pt-br");
    this.carregarCSS();
    this.loadServer();
    this.noJogos = false;
    this.color = "#3C8DBC";
    this.loadLeagues();
    this.loadLeaguesMain();
    this.loadMatchHoje();
    this.loadMatchHojeMain();
    // this.loadMatchAmanha();
    // this.loadMatchDepoisAmanha();
    this.loadRealtime();
    //this.getLive();
    this.loadLimites();
    this.loadDay();
    //this.loadRealtime();
    this.banners = window.banners;
    this.hoje = true;
    this.live = false;
    this.amanha = false;
    this.afetTerTomorow = false;
    this.errorLogin = false;
    this.jogosView = true;
    this.bilheteView = false;

    //Verifica usuário logado
    if (localStorage.getItem("token") == null) {
      this.logar = true;
      this.logout = false;
      this.logado = false;
    } else if (localStorage.getItem("token") != null) {
      this.logar = false;
      this.logout = true;
      this.userLogado();
      this.logado = true;
    }
    this.name = localStorage.getItem("nome");
    this.nivel = localStorage.getItem("nivel");
    // this.verificaRegulamento();

    // Fallback: se qualquer chamada falhar ou demorar demais, libera a tela de carregamento após 3s
    setTimeout(() => {
      this.loading = false;
    }, 3000);
    // this.date1  = moment(new Date()).format('YYYY-MM-DD')
    // this.date2  = moment(new Date()).format('YYYY-MM-DD')

    // console.log('relatório', Object.values(this.relatorio).length);
  },
  mounted() {
    if (window.innerHeight > window.innerWidth) {
      $("#modal-bilhete").css("max-height", "100vh");
      $("#modal-bilhete").css("overflow-y", "auto");
    }
    this.startAutoplay();
    this._clockInterval = setInterval(() => {
      this.currentTime = new Date();
    }, 1000);

    // Auto-refresh das partidas a cada 30s para capturar jogos ao vivo
    this._matchRefreshInterval = setInterval(() => {
      if (this.hoje && !this.live) {
        this.loadMatchHoje();
      }
    }, 30000);

    // Auto-refresh para AO VIVO a cada 15s (mais rapido para dados ao vivo)
    this._liveRefreshInterval = setInterval(() => {
      if (this.live) {
        this.loadVivo();
      }
    }, 15000);

    // Modal stacking issues are now natively handled by custom.css overrides
    // (.modal z-index: 100000, .modal-backdrop z-index: 99999, .wrapper z-index: auto)
    $(document).on('shown.bs.modal', '.modal', function () {
      $(this).addClass('in show'); // Força a exibição suave sem bugar o Vue
    });
  },
  beforeDestroy() {
    this.stopAutoplay();
    if (this._clockInterval) {
      clearInterval(this._clockInterval);
    }
    if (this._matchRefreshInterval) {
      clearInterval(this._matchRefreshInterval);
    }
    if (this._liveRefreshInterval) {
      clearInterval(this._liveRefreshInterval);
    }
  },
  data() {
    return {
      formRegister: {
        nome: '',
        username: '',
        password: '',
        password_confirmation: '',
        cpf: '',
        telefone: '',
        dia: '',
        mes: '',
        ano: '',
        email: '',
        termos: false,
        pix_key: '',
        pix_key_type: 'CPF'
      },
      withdrawalAmount: 0,
      loadingWithdrawal: false,
      depositAmount: 20,
      pixData: {
        pix_id: '',
        qr_code: '',
        qr_code_base64: '',
        payment_id: ''
      },
      loading_share: false,
      image_share: '',


      loadingPix: false,
      moment: window.moment,
      notEvent: false,
      link: "",
      openedCountries: [],
      leagues: [],
      liga: "",
      leagues_main: [],
      busca: "",
      search: "",
      search_time: "",
      alert_pesquisa: false,
      loading: false,
      loadingBtn: false,
      loadingCaixa: false,
      loading_odds: false,
      regulamento: "",
      events: [],
      events_main: [],
      pre_bet_id: null,
      carouselInterval: null,
      liaga: "",
      match: {},
      mercados: [],
      palpites: [],
      selecionados: [],
      selecionadosLive: [],
      selection: [],
      apostado: "",
      total_cotacao: 0,
      qtd_palpites: 0,
      cliente: "",
      cupom_pre_aposta: "",
      cupom: "",
      bilhetes: [],
      bilhetesLogado: [],
      //Valores finais
      retorno: 0,
      retornoCambista: 0,
      url: "",
      days: [],
      events_hoje: [],
      events_amanha: [],
      searchMercado: "",
      showMarketInfo: false,
      selectedMarketName: "",
      collapsedMarkets: [],
      events_depois_amanha: [],
      events_vivo: [],
      events_all: [],
      live: "",
      hoje: "",
      amanha: "",
      afetTerTomorow: "",
      eventsAll: "",
      tipoAposta: "",
      noJogos: "",

      activeDay: null,
      futebol: true,

      //Login
      text_btn_login: "Logar",
      nivel: "",
      name: "",
      type_user: "",
      password: "",
      username: "",
      token: "",
      messageError: "",
      errorLogin: "",
      logar: "",
      logout: "",
      logado: "",
      linkPrint: "",
      pin: "",
      date1: "",
      date2: "",
      data_inicio: window.moment().format('YYYY-MM-DD'),
      data_fim: window.moment().format('YYYY-MM-DD'),

      caixaUser: {},
      limitesUser: {},
      relatorio: {},
      promoCode: '',
      loadingBonus: false,
      bonusData: null,
      withdrawalHistory: [],
      formPassword: {
        password: '',
        password_confirmation: ''
      },
      loadingPassword: false,

      //loader
      color: "3C8DBC",
      size: "100px",
      margin: "auto",

      //Valores Limites
      max_cotacao: "",
      mini_cotacao: "",
      aposta_ativa: "",
      premio_max: "",
      max_jogos_bilehte: "",
      mini_jogos_bilhete: "",
      valor_max_aposta: "",
      valor_mini_aposta: "",
      op_ufcbox: "Sim",
      op_basquete: "Sim",
      op_tenis: "Sim",
      op_volei: "Sim",
      op_futebol: "Sim",
      op_quininha: "Não",
      op_seninha: "Não",
      futebol_ao_vivo: "",
      jogosView: "",
      bilheteView: "",
      vivo: "",
      leagueOp: "",
      //server
      server: {
        host: "",
        logo: "",
        logoMini: "",
        year: "",
        linkApp: "",
        footer_sobre: "",
      },
      //App
      banners: [],

      matchSelected: 0,
      modalitySelected: "",
      use_bonus: false,
      currentTime: new Date(),
      activeTabIdx: null,
      bilhete_print: {},

      // Loto state
      numbersLoto: [],
      selectedNumbersLoto: [],
      taxasLoto: [],
      datesLoto: [],
      lotoDateSelected: "",
      lotoTaxaSelected: null,
      lotoType: "",
      lotoValue: 10, // Default value
      loadingLoto: false,
    };
  },
  filters: {
    formatDate(date) {
      return moment(date).format("DD/MM HH:mm");
    },
    formatDateHome(date) {
      return moment(date).format("DD/MM");
    },
    formatDateShort(date) {
      return moment(date).format("DD/MM/YY HH:mm");
    },
    formatTime(date) {
      return moment(date).format("HH:mm");
    },
    formatCotacao(num) {
      if (num === null || num === undefined || isNaN(parseFloat(num))) {
        return '0,00';
      }
      var numero = parseFloat(num).toFixed(2).split(".");
      numero[0] = numero[0].split(/(?=(?:...)*$)/).join(".");
      return numero.join(",");
    },
    formatMoeda(numero) {
      if (numero === null || numero === undefined || isNaN(parseFloat(numero))) {
        return "R$ 0,00";
      }
      return (
        "R$ " +
        parseFloat(numero)
          .toFixed(2)
          .replace(".", ",")
          .replace(/(\d)(?=(\d{3})+\,)/g, "$1.")
      );
    },
    verificaOdd(id) {},
    is_img(img) {
      let file = "https://assets.b365api.com/images/team/m/" + img + ".png";
      var img = new Image();
      img.src = file;

      return img.width;
      img.onload = function () {};
      img.onerror = function () {};
    },
  },
  watch: {
    selecionados(valorAnterios, valor) {
      this.selecionados;
    },
  },
  computed: {
    mainBanners() {
      return this.banners.filter(b => !b.position || b.position === 'home_main');
    },
    belowTicketBanners() {
      return this.banners.filter(b => b.position === 'below_ticket');
    },
    sidebarBanners() {
      return this.banners.filter(b => b.position === 'sidebar');
    },
    themeVars() {
      // Retorna as cores do tema baseadas nas variáveis CSS do root
      // Isso permite que o Vue use as mesmas cores definidas nos arquivos CSS
      return {
        primary: 'var(--container_jogos--color, #23a73d)',
        secondary: 'var(--btn--color, var(--container_jogos--color))',
        selected: 'var(--btn_selecionado-color, #23a73d)',
        sidebar: 'var(--sidebar--color, var(--sidebar--color))',
        cef: 'var(--odd_button_bg--color, #1a202c)'
      };
    },
    selectionsIds() {
      if (!this.selection) return [];
      return this.selection.map(s => s.idOdd);
    },
    filteredFeaturedGames() {
      if (!this.events_main || !Array.isArray(this.events_main)) return [];
      
      // Se não houver modalidade selecionada (Home/Futebol), mostra todos
      if (!this.modalitySelected) return this.events_main;
      
      // Mapeamento de normalização para bater com o campo 'sport' da API
      const modalityMap = {
        'Basquete': 'Basquete',
        'Volei': 'Volei',
        'Luta': 'Luta',
        'Tenis': 'Tenis',
        'Futebol': 'Futebol'
      };

      const normalizedModality = modalityMap[this.modalitySelected] || this.modalitySelected;

      return this.events_main.filter(match => {
        const sport = match.sport || 'Futebol';
        return sport.toLowerCase() === normalizedModality.toLowerCase();
      });
    },
    filterLiegues() {
      if (this.search != "") {
        return this.events.filter((x) =>
          x.match.some((g) =>
            g.confronto.toLowerCase().includes(this.search.toLowerCase())
          )
        );
      } else {
        return this.events.filter((league) => {
          return league.league
            .toLowerCase()
            .includes(this.search.toLowerCase());
        });
      }
    },
    groupedLeaguesMain() {
      if (!this.leagues_main) return [];
      const groups = {};
      this.leagues_main.forEach(l => {
        const cc = (l.cc || 'intl').toLowerCase();
        if (!groups[cc]) {
          groups[cc] = { cc: cc, name: this.getCountryName(cc), leagues: [] };
        }
        groups[cc].leagues.push(l);
      });
      return Object.values(groups).sort((a, b) => {
        if (a.cc === 'br') return -1;
        if (b.cc === 'br') return 1;
        return a.name.localeCompare(b.name);
      });
    },
    groupedLeaguesOthers() {
      // A API agora retorna os dados já agrupados por país (formato demo)
      // [{country, cc, leagues: [{sport, cc, league}]}]
      if (!this.leagues || !Array.isArray(this.leagues)) return [];
      // Se já vem agrupado do backend (tem propriedade 'country')
      if (this.leagues.length > 0 && this.leagues[0] && this.leagues[0].country) {
        return this.leagues;
      }
      // Fallback: se por algum motivo vier formato antigo (flat)
      const groups = {};
      this.leagues.forEach(l => {
        if (typeof l.id === 'string' && l.id.startsWith('m')) return; // ignora manuais
        const cc = (l.cc || 'intl').toLowerCase();
        if (!groups[cc]) {
          groups[cc] = { cc: cc, country: this.getCountryName(cc), name: this.getCountryName(cc), leagues: [] };
        }
        groups[cc].leagues.push(l);
      });
      return Object.values(groups).sort((a, b) => {
        if (a.cc === 'br') return -1;
        if (b.cc === 'br') return 1;
        return (a.country || a.name || '').localeCompare(b.country || b.name || '');
      });
    },
    manualLeagues() {
      if (!this.leagues_main || !Array.isArray(this.leagues_main)) return [];
      return this.leagues_main.filter(l => typeof l.id === 'string' && l.id.startsWith('m'));
    },
    filteredLeaguesMain() {
      if (!this.leagues_main || !Array.isArray(this.leagues_main)) return [];
      return this.leagues_main.filter(l => !(typeof l.id === 'string' && l.id.startsWith('m')));
    }
  },
  methods: {
    getCountryName(cc) {
      const names = {
        'br': 'Brasil', 'ar': 'Argentina', 'gb': 'Inglaterra', 'es': 'Espanha',
        'it': 'Itália', 'de': 'Alemanha', 'fr': 'França', 'pt': 'Portugal',
        'us': 'EUA', 'nl': 'Holanda', 'be': 'Bélgica', 'tr': 'Turquia', 'intl': 'Internacional'
      };
      return names[cc.toLowerCase()] || cc.toUpperCase();
    },
    toggleCountry(cc) {
      if (this.openedCountries.includes(cc)) {
        this.openedCountries = this.openedCountries.filter(c => c !== cc);
      } else {
        this.openedCountries.push(cc);
      }
    },
    carregarCSS() {
      if (!document.getElementById("font-poppins")) {
        const link = document.createElement("link");
        link.id = "font-poppins";
        link.rel = "stylesheet";
        link.href =
          "https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap";
        document.head.appendChild(link);
      }
    },
    copyToClipboard(text) {
      if (!text) return;
      
      const showCopied = () => {
        Swal.fire({
          title: 'Copiado!',
          text: 'Código copiado para a área de transferência!',
          icon: 'success',
          toast: true,
          position: 'top',
          showConfirmButton: false,
          timer: 2500,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.style.zIndex = '300000';
            const container = toast.closest('.swal2-container');
            if (container) container.style.zIndex = '300000';
          }
        });
      };

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
          showCopied();
        }).catch(err => {
          this.fallbackCopy(text, showCopied);
        });
      } else {
        this.fallbackCopy(text, showCopied);
      }
    },
    fallbackCopy(text, callback) {
      const el = document.createElement('textarea');
      el.value = text;
      el.setAttribute('readonly', '');
      el.style.position = 'fixed';
      el.style.left = '-9999px';
      el.style.top = '0';
      document.body.appendChild(el);
      const selected = document.getSelection().rangeCount > 0 ? document.getSelection().getRangeAt(0) : false;
      el.select();
      el.setSelectionRange(0, 99999);
      document.execCommand('copy');
      document.body.removeChild(el);
      if (selected) {
        document.getSelection().removeAllRanges();
        document.getSelection().addRange(selected);
      }
      if (callback) callback();
      else this.showAlert('Código copiado para a área de transferência!', 'Sucesso', 'success');
    },
    showAlert(text, title = "Atenção", icon = "warning") {
      Swal.fire({
        title: title,
        text: text,
        icon: icon,
        confirmButtonText: "OK",
        confirmButtonColor: (getComputedStyle(document.documentElement).getPropertyValue('--sidebar--color').trim() || getComputedStyle(document.documentElement).getPropertyValue('--container_jogos--color').trim() || '#1aa6d0'),
        didOpen: () => {
          const container = document.querySelector('.swal2-container');
          if (container) container.style.zIndex = '300000';
        }
      });
    },
    loadBanners() {
      if (window.banners && window.banners.length > 0) {
        this.banners = window.banners;
      } else {
        axios
          .get("/api/get-banners")
          .then((response) => {
            this.banners = response.data;
          })
          .catch((error) => {
            console.log("Erro ao carregar banners:", error);
          });
      }
    },
    loadServer() {
      if (this.site_info) {
        this.server.host = this.site_info.domain || window.location.host;
        this.server.logo = this.site_info.first_name || this.site_info.complete_name;
        this.server.logo_img = this.site_info.logo_url;
        this.server.logoMini = this.site_info.first_name ? this.site_info.first_name.substring(0, 2).toUpperCase() : (this.site_info.first_letter + this.site_info.second_letter);
        this.server.year = new Date().getFullYear();
        this.server.linkApp = this.site_info.apk_name;

        // Adiciona o Sobre Nós dinâmico
        if (this.configuracoes && this.configuracoes.length > 0) {
          this.server.footer_sobre = this.configuracoes[0].about_us;
        } else if (this.site_info.configuracoes && this.site_info.configuracoes.length > 0) {
          this.server.footer_sobre = this.site_info.configuracoes[0].about_us;
        } else if (this.site_info.about_us) {
          this.server.footer_sobre = this.site_info.about_us;
        }
      } else {
        this.server.host = process.env.MIX_ECHO_HOST;
        this.server.logo = process.env.MIX_LOGO;
        this.server.logoMini = process.env.MIX_LOGO_MINI;
        this.server.year = process.env.MIX_YEAR;
        this.server.linkApp = process.env.MIX_LINK_APP;
      }
    },
    printJogos(id) {
      this.link =
        "whatsapp://send?text=" + window.location.href + "api/bilhete/" + id;
      this.linkPrint = window.location.href + "api/bilhete/" + id;
      window.open(
        this.linkPrint,
        "1462709629777",
        "width=360,height=screen.height,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=0,left=0,top=0"
      );
      return false;
    },

    scrollToCupom() {
      const el = document.getElementById("cupom-site");
      if (el) {
        el.scrollIntoView({ behavior: "smooth" });
      } else {
        // Fallback para mobile se o ID não for encontrado ou estiver oculto
        window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
      }
    },

    load_login() {
      $("#modal-login").modal("show");
    },
    load_register() {
      $("#modal-register").modal("show");
    },
    submitRegister() {
      if (!this.formRegister.termos) {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Você precisa aceitar os termos de uso!', type: 'error' });
        return;
      }
      if (this.formRegister.password !== this.formRegister.password_confirmation) {
        this.$notify({ group: 'foo', title: 'Erro', text: 'As senhas não conferem!', type: 'error' });
        return;
      }
      
      this.$notify({ group: 'foo', title: 'Aguarde', text: 'Criando sua conta...', type: 'info' });
      
      axios.post("/api/register", this.formRegister)
        .then((response) => {
          if (response.data.status === 'success') {
            this.$notify({ group: 'foo', title: 'Sucesso', text: 'Conta criada com sucesso!', type: 'success' });
            $("#modal-register").modal("hide");
            
            // Login automático
            localStorage.setItem("token", response.data.token || ''); 
            localStorage.setItem("nome", response.data.user.name);
            localStorage.setItem("nivel", response.data.user.nivel || 'cliente');
            
            setTimeout(() => {
              document.location.reload(true);
            }, 1500);
          }
        })
        .catch((error) => {
          let message = "Erro ao cadastrar. Verifique os dados.";
          if (error.response && error.response.data && error.response.data.errors) {
            message = Object.values(error.response.data.errors)[0][0];
          }
          this.showAlert(message, "Erro no Cadastro", "error");
        });
    },
    load_deposit() {
      $("#modal-deposit").modal("show");
    },
    submitPix() {
      if (this.depositAmount < 1) {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Valor mínimo R$ 1,00', type: 'error' });
        return;
      }

      this.loadingPix = true;
      this.$notify({ group: 'foo', title: 'Aguarde', text: 'Gerando seu PIX...', type: 'info' });

      axios.post("/api/deposit/pix", { amount: this.depositAmount })
      .then((response) => {
        if (response.data.status === 'success') {
          this.pixData = response.data;
          $("#modal-pix-display").modal("show");
          $("#modal-deposit").modal("hide");
        }
      })
      .catch((error) => {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Erro ao gerar pagamento. Tente novamente.', type: 'error' });
      })
      .finally(() => {
        this.loadingPix = false;
      });
    },
    copyPix() {
      if (!this.pixData.qr_code) return;
      
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(this.pixData.qr_code).then(() => {
          this.$notify({ group: 'foo', title: 'Copiado', text: 'Código PIX copiado com sucesso!', type: 'success' });
        }).catch(err => {
          this.fallbackCopyPix();
        });
      } else {
        this.fallbackCopyPix();
      }
    },
    fallbackCopyPix() {
      const el = document.createElement('textarea');
      el.value = this.pixData.qr_code;
      el.setAttribute('readonly', '');
      el.style.position = 'fixed';
      el.style.left = '-9999px';
      el.style.top = '0';
      document.body.appendChild(el);
      el.select();
      el.setSelectionRange(0, 99999);
      document.execCommand('copy');
      document.body.removeChild(el);
      this.$notify({ group: 'foo', title: 'Copiado', text: 'Código PIX copiado com sucesso!', type: 'success' });
    },
    load_withdrawal() {
      $("#modal-withdrawal").modal("show");
    },
    submitWithdrawal() {
      if (this.withdrawalAmount < 20) {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Valor mínimo R$ 20,00', type: 'error' });
        return;
      }

      this.loadingWithdrawal = true;
      axios.post("/api/withdrawal/request", { amount: this.withdrawalAmount })
      .then((response) => {
        if (response.data.status === 'success') {
          this.$notify({ group: 'foo', title: 'Sucesso', text: response.data.message, type: 'success' });
          $("#modal-withdrawal").modal("hide");
          this.userLogado(); // Atualiza saldo
        }
      })
      .catch((error) => {
        let message = "Erro ao solicitar saque.";
        if (error.response && error.response.data && error.response.data.message) {
          message = error.response.data.message;
        }
        this.$notify({ group: 'foo', title: 'Erro', text: message, type: 'error' });
      })
      .finally(() => {
        this.loadingWithdrawal = false;
      });
    },
    load_bonus() {
      $("#modal-bonus").modal("show");
      this.getBonusStatus();
    },
    // getBonusStatus() movido para definição única (mais abaixo)
    applyPromoCode() {
      if (this.promoCode == "") {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Informe um código!', type: 'error' });
        return;
      }

      this.loadingBonus = true;
      axios.post("/api/bonus/apply", { code: this.promoCode })
      .then((response) => {
        if (response.data.status === 'success') {
          this.$notify({ group: 'foo', title: 'Sucesso', text: response.data.message, type: 'success' });
          this.promoCode = '';
          this.getBonusStatus();
          this.userLogado();
        }
      })
      .catch((error) => {
        let message = "Erro ao ativar código.";
        if (error.response && error.response.data && error.response.data.message) {
          message = error.response.data.message;
        }
        this.$notify({ group: 'foo', title: 'Erro', text: message, type: 'error' });
      })
      .finally(() => {
        this.loadingBonus = false;
      });
    },
    sair() {
      localStorage.removeItem("token");
      localStorage.removeItem("nome");
      localStorage.removeItem("nivel");
      this.logar = true;
      this.logout = false;
      document.location.reload(true);
    },
    login() {
      if (this.username == "" || this.password == "") {
        this.showAlert("Preencha todos os campos!", "Atenção", "warning");
        return;
      }
      axios
        .post("/api/login", {
          username: this.username,
          password: this.password,
        })
        .then((response) => {
          this.text_btn_login = "Aguarde...";

          if (
            response.data.user.nivel == "adm" ||
            response.data.user.nivel == "gerente"
          ) {
            axios
              .post("/login", {
                username: this.username,
                password: this.password,
              })
              .then((response) => {
                window.location.href = "/admin/home";
                this.logar = false;
                this.logout = true;
              })
              .catch((error) => {
                console.log(error);
                this.showAlert("Acesso negado! Verifique suas credenciais.", "Erro", "error");
              })
              .finally(() => {});
            return;
          } //else
          if (
            response.data.user.nivel == "cambista" ||
            response.data.user.nivel == "cliente"
          ) {
            localStorage.setItem("token", response.data.token);
            localStorage.setItem("nome", response.data.user.name);
            localStorage.setItem("nivel", response.data.user.nivel);

            document.location.reload(true);
          }
        })
        .catch((error) => {
          console.log(error);
          this.text_btn_login = "Entrar";
          let msg = "Usuário ou senha inválidos!";
          if (error.response && error.response.data && error.response.data.message) {
            msg = error.response.data.message;
          }
          this.showAlert(msg, "Erro no Login", "error");
        })
        .finally(() => {});
    },
    userLogado() {
      axios
        .get("/api/user-logado")
        .then((response) => {
          this.caixaUser = response.data;
          // Garantir que nivel e nome estejam atualizados
          if (response.data.nivel) {
            this.nivel = response.data.nivel;
            localStorage.setItem("nivel", response.data.nivel);
          }
          if (response.data.name) {
            this.name = response.data.name;
            localStorage.setItem("nome", response.data.name);
          }

          if (this.logado) {
            this.getBonusStatus();
            this.getWithdrawalHistory();
          }
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {});
    },
    loadMeusCambistas() {
      // Futura implementação: Abrir modal ou página de lista de cambistas
      this.$notify({ group: 'foo', title: 'Info', text: 'Módulo de Gestão de Cambistas em desenvolvimento.', type: 'info' });
    },
    loadRelatorioGeral() {
      this.$notify({ group: 'foo', title: 'Info', text: 'Módulo de Relatórios Consolidados em desenvolvimento.', type: 'info' });
    },
    loadMinhaConta() {
      $("#modal-account").modal("show");
    },
    loadValidarPin() {
      $("#modal-validar-pin").modal("show");
    },
    loadMeusClientes() {
      this.$notify({ group: 'foo', title: 'Info', text: 'Módulo de Gestão de Clientes em desenvolvimento.', type: 'info' });
    },
    changePassword() {
      if (this.formPassword.password.length < 6) {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Senha muito curta!', type: 'error' });
        return;
      }
      if (this.formPassword.password != this.formPassword.password_confirmation) {
        this.$notify({ group: 'foo', title: 'Erro', text: 'As senhas não coincidem!', type: 'error' });
        return;
      }

      this.loadingPassword = true;
      axios.post("/api/change-password", this.formPassword)
      .then((response) => {
        this.$notify({ group: 'foo', title: 'Sucesso', text: 'Senha alterada!', type: 'success' });
        this.formPassword.password = '';
        this.formPassword.password_confirmation = '';
        $("#modal-account").modal("hide");
      })
      .catch((error) => {
        this.$notify({ group: 'foo', title: 'Erro', text: 'Erro ao alterar senha.', type: 'error' });
      })
      .finally(() => {
        this.loadingPassword = false;
      });
    },
    // getWithdrawalHistory() movido para definição única (mais abaixo)
    getBonusStatus() {
      axios.get("/api/bonus/my", {
        headers: { Authorization: "Bearer " + localStorage.getItem("token") }
      })
      .then((response) => {
        this.bonusData = response.data;
      })
      .catch((error) => {
        console.log("Erro ao buscar bônus", error);
      });
    },
    getWithdrawalHistory() {
      axios.get("/api/withdrawals", {
        headers: { Authorization: "Bearer " + localStorage.getItem("token") }
      })
      .then((response) => {
        this.withdrawalHistory = response.data;
      })
      .catch((error) => {
        console.log("Erro ao buscar saques", error);
      });
    },

    loadRealtime() {
      window.Echo.channel("live-futebol-hoje").listen(
        "LiveHojeFutebol",
        (e) => {
          if (this.vivo) {
            this.events_hoje = e;
            if (this.hoje && !this.leagueOp) {
              this.events = e;
            }
          }
        }
      );

      window.Echo.channel("live-futebol-amanha").listen(
        "LiveAmanhaFutebol",
        (e) => {
          this.events_amanha = e;
          if (this.amanha && !this.leagueOp) {
            this.events = e;
            this.calculaCotacao();
          }
        }
      );

      window.Echo.channel("live-futebol-after").listen(
        "LiveAfeterTomorowFutebol",
        (e) => {
          this.events_depois_amanha = e;
          if (this.afetTerTomorow && !this.leagueOp) {
            this.events = e;
            this.calculaCotacao();
          }
        }
      );

      window.Echo.channel("live-futebol-live").listen("LiveFutebol", (e) => {
        // console.log('live ', e)
        this.events_vivo = e;
        if (this.live && !this.leagueOp) {
          this.events = e;
          this.calculaCotacao();
        }
      });

      //Load dias da semana
      window.Echo.channel("load-day").listen("LoadDayEnvent", (data) => {
        this.days = data;
      });

      //Load ligas
      window.Echo.channel("load-league").listen("LoadLigas", (data) => {
        this.leagues = data;
      });

      window.Echo.channel("load-league-main").listen(
        "LoadLigasMain",
        (data) => {
          this.leagues_main = data;
        }
      );

      //Configurações real time
      window.Echo.channel("load-configurations").listen(
        "LoadConfiguration",

        (data) => {
          this.limitesUser = data;
          this.max_cotacao = data.cotacao_max_bilhete;
          this.mini_cotacao = data.cotacao_mini_bilhete;
          this.aposta_ativa = data.aposta_ativa;
          this.premio_max = data.premio_max;
          this.max_jogos_bilehte = data.quantidade_jogos_max_bilhete;
          this.mini_jogos_bilhete = data.quantidade_jogos_mini_bilhete;
          this.valor_max_aposta = data.valor_max_aposta;
          this.valor_mini_aposta = data.valor_mini_aposta;
          this.op_ufcbox = data.op_ufcbox;
          this.op_basquete = data.op_basquete;
          this.op_tenis = data.op_tenis;
          this.op_volei = data.op_volei;
          this.op_futebol = data.op_futebol;
          this.op_quininha = data.op_quininha;
          this.op_seninha = data.op_seninha;

          if (data.futebol_ao_vivo == "Sim") {
            this.vivo = true;
          } else {
            this.vivo = false;
            this.live = false;
          }
        }
      );
      window.Echo.channel("refreshmatch").listen("LoadRefreshOdd", (data) => {
        for (let i = 0; i < data.length; i++) {
          // console.log(data[i].event_id)
          if (data[i].event_id === this.matchSelected) {
            // this.match = {}
            // this.mercados = []
            // console.log('match selected', data[i], 'mercados', data[i].mercados)
            this.match = data[i];
            this.mercados = data[i].mercados;
          }
        }
      });

      // window.Echo.channel("match-load").listen("LoadMatchLiveScore", data => {

      //       //  console.log('score live',data);
      //     if (data.id == this.match.id && this.live) {
      //       this.match = data;
      //     //console.log('igual')
      //     }

      // });

      //Load match carregada
    },
    viewValidarPin() {
      $("#modal-validar-pin").modal("show");
    },
    validaPin() {
      this.selection = this.selecionados;
      if (this.pin == "") {
        this.errorLogin = true;
        this.messageError = "Preencha o campo com um PIN!";
        return;
      }

      // 🚀 SE CAMBISTA LOGADO: VALIDAÇÃO DIRETA (Converte PIN em Aposta Real)
      if (this.logado && ['cambista', 'vendedor', 'admin', 'adm'].includes(this.account.nivel)) {
         this.loading = true;
         axios.post("/api/valida-cod", { codigo: this.pin }, {
            headers: { Authorization: "Bearer " + localStorage.getItem("token") }
         })
         .then(response => {
            if (response.data.success || response.data.id) {
               const bilhete = response.data.bilhete || response.data;
               $("#modal-validar-pin").modal("hide");
               this.pin = "";
               this.bilhetes.push(bilhete);
               this.bilhete_print = bilhete;
               $("#modal-bilhete").modal("show");
               this.$notify({ group: 'foo', title: 'Sucesso', text: 'PIN Validado com Sucesso!', type: 'success' });
               this.userLogado();
            } else {
               this.showAlert(response.data.message || "Erro ao validar PIN");
            }
         })
         .catch(err => {
            let msg = "PIN Inválido, expirado ou já validado.";
            if (err.response && err.response.data && err.response.data.message) {
               msg = err.response.data.message;
            }
            this.showAlert(msg);
         })
         .finally(() => { this.loading = false; });
         return;
      }

      // 🔍 SE NÃO LOGADO: APENAS CARREGA O PIN NO CARRINHO (Preview)
      axios
        .post("/api/print-bilhete-get-cod-site", { cupom: this.pin })
        .then((response) => {
          // console.log("aposta", response); // Removido em auditoria
          $("#modal-validar-pin").modal("hide");
          this.pin = "";
          
          // 🚀 SE FOR APENAS CONSULTA: MOSTRA O BILHETE (Conforme pedido: "tem que ter tudo")
          if (response.data && response.data.length > 0) {
            const bilheteData = response.data[0];
            this.bilhetes = [bilheteData];
            this.bilhete_print = bilheteData;
            $("#modal-bilhete").modal("show");
            
            // Também carrega no carrinho para facilitar re-aposta
            this.pre_bet_id = bilheteData.id;
            this.apostado = bilheteData.valor_apostado;
            this.cliente = bilheteData.cliente;
            this.selection = bilheteData.palpites;
            this.calculaCotacao();
          } else {
            this.showAlert("Bilhete não encontrado!");
          }
        })
        .catch((error) => {
          console.log("erro", error);

          if (error.response && error.response.status == 404) {
            $("#modal-validar-pin").modal("hide");
            this.showAlert("Bilhete não encontrado!");
            return;
          }
        })
        .finally(() => {});
    },
    loadDay() {
      axios.get("/api/dias-futebol").then((response) => {
        this.days = response.data;
      });
    },
    allMatchs() {
      this.live = false;
      this.eventsAll = true;
      //this.events = this.events_all;
      this.hoje = false;
      this.amanha = false;
      this.afetTerTomorow = false;
      this.selection = this.selecionados;
      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/all-matchs";
      } else {
        rota = "/api/site-all-matchs";
      }
      axios
        .get(rota)
        .then((response) => {
          this.events_all = response.data;
          this.events = this.events_all;
        })
        .catch((error) => {
          console.log(error);
        })

        .finally(() => {
          this.loading = false;
          return;
        });
    },
    toggleMarket(name) {
      if (this.collapsedMarkets.includes(name)) {
        this.collapsedMarkets = this.collapsedMarkets.filter(m => m !== name);
      } else {
        this.collapsedMarkets.push(name);
      }
    },
    isMarketCollapsed(name) {
      return this.collapsedMarkets.includes(name);
    },
    getMarketDescription(name) {
      const descriptions = {
        "Vencedor do Encontro": "Para ganhar, o cliente precisa acertar qual time vence a partida ao final dos 90 minutos mais acréscimos. Empate perde, salvo quando indicado no mercado.",
        "Both Teams Score": "Para ganhar, o cliente deve escolher SIM ou NÃO. Se SIM, ambos os times devem marcar ao menos 1 gol cada durante os 90 minutos mais acréscimos. Se NÃO, pelo menos um dos times não pode marcar gol.",
        "Ambos Marcam": "Para ganhar, o cliente deve escolher SIM ou NÃO. Se SIM, ambos os times devem marcar ao menos 1 gol cada durante os 90 minutos mais acréscimos. Se NÃO, pelo menos um dos times não pode marcar gol.",
        "Total de Gols 2.5": "Para ganhar o cliente precisa acertar o total de gols da partida somando as duas equipes. Acima de 2.5 (3 gols ou mais), Abaixo de 2.5 (2 gols ou menos).",
        "Total de Gols": "Aposta no total de gols marcados por ambas as equipes. Você deve prever se o total será acima ou abaixo do limite estabelecido.",
        "Handicap": "Vantagem ou desvantagem de gols dada a uma equipe para equilibrar as chances de aposta.",
        "Placar Exato": "Você deve prever o resultado exato da partida ao final do tempo regulamentar.",
        "Resultado no Intervalo": "Previsão de quem estará vencendo (ou se haverá empate) ao final do primeiro tempo."
      };
      
      // Busca por nome exato ou tenta encontrar se o nome traduzido contém a chave
      if (descriptions[name]) return descriptions[name];
      
      const found = Object.keys(descriptions).find(key => name.toLowerCase().includes(key.toLowerCase()));
      return found ? descriptions[found] : "Este mercado permite apostar em diferentes resultados da partida. Selecione a opção desejada e adicione ao bilhete.";
    },
    loadRelatorio() {
      $("#modal-relatorio").modal("show");
    },
    sendRelatorio() {
      if (this.date1 != "" && this.date2 != "") {
        this.loadingCaixa = true;
        axios
          .post("/api/relatorio-cambista", {
            date1: this.date1,
            date2: this.date2,
          })
          .then((response) => {
            if (response.data[0] != undefined) {
              this.loadingCaixa = false;
              this.relatorio = response.data[0];
            } else {
              this.loadingCaixa = false;
              this.relatorio = {};
              return;
            }
          })
          .catch((error) => {
            this.loadingCaixa = false;
            if (error.response && error.response.status == 404) {
              return;
            }
          })
          .finally(() => {});
      } else {
      }
    },
    isPalpiteActive(uuid) {
      if (!this.selectionsIds) return false;
      return this.selectionsIds.includes(uuid);
    },
    formatCotacao(num) {
      if (!num) return '0,00';
      var cot = parseFloat(num);
      return cot.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
    searchDay(data, idx = null) {
      this.activeDay = data;
      this.activeTabIdx = idx; // Novo: rastreia pelo índice para evitar duplicidade
      this.selection = this.selecionados;
      this.leagueOp = false;
      this.jogosView = true;
      this.bilheteView = false;
      this.live = false;
      
      this.hoje = false;
      this.amanha = false;
      this.afetTerTomorow = false;

      if (data == 0) {
        this.events = this.events_hoje;
        this.hoje = true;
        this.loadMatchHoje();
      } else if (data == 1) {
        this.events = this.events_amanha;
        this.amanha = true;
        this.loadMatchAmanha();
      } else if (data == 2) {
        this.events = this.events_depois_amanha;
        this.afetTerTomorow = true;
        this.loadMatchDepoisAmanha();
      }

      this.calculaCotacao();
    },
    loadFutebol() {
      this.futebol = true;
      this.modalitySelected = "";
      this.leagueOp = false;
      this.events = this.events_hoje;
      this.live = false;
      this.hoje = true;
      this.amanha = false;
      this.afetTerTomorow = false;
      this.activeDay = 0;
      this.activeTabIdx = null; // Reset index
      this.jogosView = true;
      this.bilheteView = false;
      this.selection = this.selecionados;
      this.loadMatchHoje();
      this.calculaCotacao();
    },
    loadVivo() {
      this.activeDay = null;
      this.activeTabIdx = null; // Reset index
      this.futebol = false;
      this.modalitySelected = "";
      this.selection = this.selecionadosLive;
      this.leagueOp = false;
      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      this.live = true;
      this.hoje = false;
      this.amanha = false;
      this.afetTerTomorow = false;
      axios
        .get("/api/site-live-futebol")
        .then((response) => {
          this.events_vivo = response.data;
          this.events = this.events_vivo;
          if (this.events.length === 0) {
            console.log(this.notEvent);
          }
        })
        .catch((error) => {
          console.log(error);
        })

        .finally(() => {
          this.loading = false;

          for (var j = 0; j < this.selection.length; j++) {
            $(
              "div[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("selecionado");
            $(
              "span[taxaJogo=" +
                this.selection[j].partida +
                "][taxa=" +
                this.selection[j].idOdd +
                "]"
            ).addClass("odd-match-plus-right-selecionado");
          }
          this.calculaCotacao();
          return;
        });
    },
    clique() {},
    is_img(img) {
      let file = "https://assets.b365api.com/images/team/m/" + img + ".png";
      var img = new Image();
      img.src = file;

      return img.width;
    },
    loadModality(modality) {
      this.modalitySelected = modality;
      this.leagueOp = false;
      this.jogosView = true;
      this.bilheteView = false;
      this.loading = true;
      this.live = false;
      this.hoje = false;
      this.amanha = false;
      this.afetTerTomorow = false;
      this.events = [];
      
      axios.get("/api/site-partidas-modalidade/" + modality)
        .then((response) => {
          this.events = response.data;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadQuininha() {
      this.lotoType = "Quininha";
      this.modalitySelected = "Quininha";
      this.loading = true;
      this.selectedNumbersLoto = [];
      this.lotoTaxaSelected = null;
      this.numbersLoto = Array.from({length: 80}, (_, i) => (i + 1).toString().padStart(2, '0'));
      this.lotoValue = this.valor_mini_aposta || 2;

      axios.all([
        axios.get("/api/taxas-quina"),
        axios.get("/api/concursos-quina")
      ]).then(axios.spread((taxas, concursos) => {
        this.taxasLoto = taxas.data;
        this.datesLoto = concursos.data;
        if (this.datesLoto.length > 0) {
          this.lotoDateSelected = this.datesLoto[0].date;
        }
        if (this.taxasLoto.length > 0) {
          this.lotoTaxaSelected = this.taxasLoto[0];
        }
      })).catch(err => {
        console.log("Erro ao carregar Quininha", err);
      }).finally(() => {
        this.loading = false;
      });
    },
    loadSeninha() {
      this.lotoType = "Seninha";
      this.modalitySelected = "Seninha";
      this.loading = true;
      this.selectedNumbersLoto = [];
      this.lotoTaxaSelected = null;
      this.numbersLoto = Array.from({length: 60}, (_, i) => (i + 1).toString().padStart(2, '0'));
      this.lotoValue = this.valor_mini_aposta || 2;

      axios.all([
        axios.get("/api/taxas-sena"),
        axios.get("/api/concursos-sena")
      ]).then(axios.spread((taxas, concursos) => {
        // Mapeia "dezena" (do backend) para "dezenas" (esperado pelo frontend)
        this.taxasLoto = taxas.data.map(t => { 
          t.dezenas = t.dezena || t.dezenas; 
          return t; 
        });
        this.datesLoto = concursos.data;
        if (this.datesLoto.length > 0) {
          this.lotoDateSelected = this.datesLoto[0].date;
        }
        if (this.taxasLoto.length > 0) {
          this.lotoTaxaSelected = this.taxasLoto[0];
        }
      })).catch(err => {
        console.log("Erro ao carregar Seninha", err);
      }).finally(() => {
        this.loading = false;
      });
    },
    selectNumberLoto(num) {
      const index = this.selectedNumbersLoto.indexOf(num);
      if (index > -1) {
        this.selectedNumbersLoto.splice(index, 1);
      } else {
        if (this.lotoTaxaSelected && this.selectedNumbersLoto.length >= this.lotoTaxaSelected.dezena) {
           this.$notify({ group: 'foo', title: 'Aviso', text: 'Limite de dezenas atingido.', type: 'warn' });
           return;
        }
        this.selectedNumbersLoto.push(num);
      }
    },
    surpresinhaLoto() {
      if (!this.lotoTaxaSelected) {
        this.$notify({ group: 'foo', title: 'Aviso', text: 'Selecione uma cotação.', type: 'warn' });
        return;
      }
      this.selectedNumbersLoto = [];
      let available = [...this.numbersLoto];
      for (let i = 0; i < this.lotoTaxaSelected.dezena; i++) {
        let randomIndex = Math.floor(Math.random() * available.length);
        this.selectedNumbersLoto.push(available.splice(randomIndex, 1)[0]);
      }
      this.selectedNumbersLoto.sort();
    },
    addLotoToCart() {
       if (!this.lotoTaxaSelected) {
          this.$notify({ group: 'foo', title: 'Erro', text: 'Selecione uma cotação.', type: 'error' });
          return;
       }
       if (this.selectedNumbersLoto.length < this.lotoTaxaSelected.dezena) {
          this.$notify({ group: 'foo', title: 'Erro', text: 'Faltam ' + (this.lotoTaxaSelected.dezena - this.selectedNumbersLoto.length) + ' dezenas.', type: 'error' });
          return;
       }
       
       let selectedDateObj = this.datesLoto.find(d => d.date == this.lotoDateSelected);
       let concursoStr = selectedDateObj ? `${selectedDateObj.date} - ${selectedDateObj.day}` : this.lotoDateSelected;

       const aposta = {
         modalidade: 'Loto',
         tipo: this.lotoType,
         concurso: concursoStr,
         palpites: this.selectedNumbersLoto,
         total_palpites: this.selectedNumbersLoto.length,
         valor_apostado: this.lotoValue,
         retorno_possivel: (this.lotoValue * this.lotoTaxaSelected.taxa).toFixed(2),
         cotacao: this.lotoTaxaSelected.taxa,
         cliente: this.cliente || 'Cliente'
       };

       this.loading = true;
       const config = this.logado ? { headers: { Authorization: "Bearer " + localStorage.getItem("token") } } : {};
       const rota = this.logado ? "/api/send-aposta" : "/api/send-pre-aposta";

       axios.post(rota, aposta, config)
       .then(response => {
          if (response.data.status == "error") {
             this.showAlert(response.data.message || "Erro ao processar aposta");
             return;
          }

          // Se a resposta for uma aposta real (cambista), abre o bilhete
          if (this.logado && ['cambista', 'vendedor', 'admin', 'adm'].includes(this.account.nivel)) {
             this.bilhetes = [response.data]; // Garante que é um array para o template
             this.bilhete_print = response.data;
             $("#modal-bilhete").modal("show");
             this.$notify({ group: 'foo', title: 'Sucesso', text: 'Aposta Loto realizada!', type: 'success' });
          } else {
             // Se for PIN (cliente site), abre o modal de pré-aposta
             this.cupom_pre_aposta = response.data.cupom;
             this.link = "https://api.whatsapp.com/send?text=" + encodeURIComponent("Fiz uma pré-aposta Loto, valide meu PIN: " + response.data.cupom);
             $("#modal-pre-aposta").modal("show");
          }
          this.selectedNumbersLoto = [];
          this.userLogado();
       })
       .catch(err => {
          let msg = "Erro ao enviar aposta loto.";
          if (err.response && err.response.data && err.response.data.message) {
            msg = err.response.data.message;
          }
          this.$notify({ group: 'foo', title: 'Erro', text: msg, type: 'error' });
       })
       .finally(() => {
          this.loading = false;
       });
    },
    searchMatches() {
      if (this.search.length < 3) return;
      this.loading = true;
      this.jogosView = true;
      this.bilheteView = false;
      axios.get("/api/site-partidas-search/" + this.search)
        .then((response) => {
          this.events = response.data;
        })
        .catch((error) => {
          console.log(error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadLimites() {
      //toogle menu
      $(document).on("click", ".sidebar-menu", function () {
        $("body").removeClass("sidebar-open");
      });

      axios.get("/api/list-limites").then((response) => {
        this.limitesUser = response.data[0];

        if (this.limitesUser.futebol_ao_vivo == "Sim") {
          this.vivo = true;
        } else {
          this.vivo = false;
        }

        this.max_cotacao = response.data[0]["cotacao_max_bilhete"];
        this.mini_cotacao = response.data[0]["cotacao_mini_bilhete"];
        this.aposta_ativa = response.data[0]["aposta_ativa"];
        this.premio_max = response.data[0]["premio_max"];
        this.max_jogos_bilehte =
          response.data[0]["quantidade_jogos_max_bilhete"];
        this.mini_jogos_bilhete =
          response.data[0]["quantidade_jogos_mini_bilhete"];
        this.valor_max_aposta = response.data[0]["valor_max_aposta"];
        this.valor_mini_aposta = response.data[0]["valor_mini_aposta"];
        this.op_ufcbox = response.data[0]["op_ufcbox"];
        this.op_basquete = response.data[0]["op_basquete"];
        this.op_tenis = response.data[0]["op_tenis"];
        this.op_volei = response.data[0]["op_volei"];
        this.op_futebol = response.data[0]["op_futebol"];
        this.op_quininha = response.data[0]["op_quininha"];
        this.op_seninha = response.data[0]["op_seninha"];
        this.futebol_ao_vivo = response.data[0]["futebol_ao_vivo"];
      });
    },
    loadCaixa() {
      $("#modal-caixa").modal("show");
    },

    loadRegulamento() {
      $("#modal-regulamento").modal("show");
      axios.get("/api/regulamento").then((response) => {
        this.regulamento = response.data[0]["regulamento"];
      });
    },
    loadBilhetes() {
      this.loading = true;
      this.jogosView = false;
      this.bilheteView = true;
      this.bilhetesLogado = [];
      axios
        .get("/api/bilhetes")
        .then((response) => {
          this.loading = false;
          this.bilhetesLogado = response.data;
        })
        .catch((error) => {
          this.loading = false;
          console.log("erro", error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    viewBilhete(id) {
      if (!id || id === 'undefined' || id === null) return;
      $("#modal-bilhete").modal("show");
      this.bilhetes = [];
      axios
        .get("/api/print-bilhete-id/" + id)
        .then((response) => {
          this.link =
            "whatsapp://send?text=" +
            window.location.href +
            "api/bilhete/" +
            id;
          this.bilhetes = response.data;
          this.bilhete_print = response.data;
        })
        .catch((err) => {
          console.log(err);
        })
        .finally(() => {});
    },
    maskSensitiveData(value) {
      if (!value) return '';
      // Mask CPF if it looks like a CPF (11 digits or formatted)
      const numeric = value.replace(/\D/g, '');
      if (numeric.length === 11) {
        return `***.${numeric.substr(3, 3)}.${numeric.substr(6, 3)}-**`;
      }
      // For PIX emails or generic masking
      if (value.includes('@')) {
        const parts = value.split('@');
        return `${parts[0].substr(0, 3)}***@${parts[1]}`;
      }
      // Mask PIX phone or random key
      if (value.length > 5) {
        return `***${value.substr(3, value.length - 6)}***`;
      }
      return '***';
    },
    alterarBilhete(id, bilhete) {
      var r = confirm("Deseja realmente excluir o bilhete?");
      if (r == true) {
        let value = "Aberto";

        axios
          .post("/api/cancela-bilhete/" + id)
          .then((response) => {
            this.$notify({
              group: "foo",
              title: "Sucesso!",
              text: "Bilhete cancelado  com sucesso!",
              type: "success",
              duration: 3000,
              speed: 1000,
            });
            bilhete.status = "Cancelado";
            this.userLogado();
          })
          .catch((error) => {
            this.$notify({
              group: "foo",
              title: "Erro!",
              text: "Erro ao cancelar o bilhete!",
              type: "error",
              duration: 3000,
              speed: 1000,
            });
          })
          .finally(() => {});
      } else {
        return;
      }
    },

    pesquisaBilhetes(date) {
      this.bilhetesLogado = [];
      this.loading = true;
      axios
        .post("/api/search-bilhetes", { date: date })
        .then((response) => {
          this.loading = false;
          this.bilhetesLogado = response.data;
        })
        .catch((error) => {
          this.loading = false;
          console.log("erro", error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    loadLeagues() {
      axios
        .get("/api/site-list-leagues")
        .then((response) => {
          this.leagues = response.data;
        })
        .catch(() => {})
        .finally(() => {});
    },
    loadLeaguesMain() {
      axios
        .get("/api/site-list-leagues-main")
        .then((response) => {
          //console.log(response);
          this.leagues_main = response.data;
        })
        .catch(() => {})
        .finally(() => {});
    },
    scrollCarousel(direction) {
      const container = this.$refs.carouselScroller;
      if (container) {
        const card = container.querySelector('.carousel-card');
        if (!card) return;

        const cardWidth = card.offsetWidth;
        const style = window.getComputedStyle(container);
        const gap = parseInt(style.columnGap) || parseInt(style.gap) || 12;
        
        const scrollAmount = (cardWidth + gap) * direction;
        const newPos = container.scrollLeft + scrollAmount;
        
        // Loop back to start/end
        if (direction > 0 && container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) {
          container.scrollTo({ left: 0, behavior: 'smooth' });
        } else if (direction < 0 && container.scrollLeft <= 10) {
          container.scrollTo({ left: container.scrollWidth, behavior: 'smooth' });
        } else {
          container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
      }
    },
    startAutoplay() {
      this.stopAutoplay();
      this.carouselInterval = setInterval(() => {
        this.scrollCarousel(1);
      }, 5000);
    },
    stopAutoplay() {
      if (this.carouselInterval) {
        clearInterval(this.carouselInterval);
      }
    },
    getTimeRemaining(dateString) {
      if (!dateString) return "";
      const end = new Date(dateString);
      const diff = end - this.currentTime;
      
      if (diff <= 0) return "Iniciado";
      
      const totalMinutes = Math.floor(diff / (1000 * 60));
      const hours = Math.floor(totalMinutes / 60);
      const minutes = totalMinutes % 60;
      const seconds = Math.floor((diff % (1000 * 60)) / 1000);
      
      // Se faltar mais de 5 minutos, mostra apenas Horas e Minutos (menos distração)
      if (totalMinutes > 5) {
        if (hours > 0) {
          return `${hours}h ${minutes.toString().padStart(2, '0')}m`;
        }
        return `${minutes} min`;
      }
      
      // Se faltar menos de 5 minutos, mostra a contagem agressiva de segundos
      return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    },
    loadMatchHojeMain() {
      this.events_main = [];
      axios
        .get("/api/get-featured-matches")
        .then((response) => {
          this.events_main = response.data;
          this.$nextTick(() => {
            this.startAutoplay();
          });
        })
        .catch((error) => {
          console.log(error);
        });
    },
    loadMatchHoje() {
      this.selection = this.selecionados;
      this.hoje = true;
      this.live = false;
      this.activeDay = null;

      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/partidas-home";
      } else {
        rota = "/api/site-partidas-home";
      }
      axios
        .get(rota)
        .then((response) => {
          this.events_hoje = response.data;
          this.events = this.events_hoje;
          if (response.data.length == 0) {
            this.noJogos = true;
          }
        })
        .catch((error) => {
          console.log(error);
        })

        .finally(() => {
          this.loading = false;
          return;
        });
    },

    loadMatchAmanha() {
      this.selection = this.selecionados;

      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/partidas-amanha";
      } else {
        rota = "/api/site-partidas-amanha";
      }

      axios
        .get(rota)
        .then((response) => {
          this.events_amanha = response.data;
          this.events = response.data;
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;
          return;
        });
    },
    loadMatchDepoisAmanha() {
      this.selection = this.selecionados;
      this.jogosView = true;
      this.bilheteView = false;
      this.events = [];
      this.loading = true;
      let rota = "";
      if (localStorage.getItem("token") != null) {
        rota = "/api/partidas-depois-amanha";
      } else {
        rota = "/api/site-partidas-depois-amanha";
      }
      axios
        .get(rota)
        .then((response) => {
          this.events_depois_amanha = response.data;
          this.events = response.data;
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;
          return;
        });
    },

    seachLeague(name) {
      this.selection = this.selecionados;

      this.leagueOp = true;
      this.jogosView = true;
      this.bilheteView = false;
      this.live = false;
      this.events = [];
      this.loading = true;
      let rota;
      if (localStorage.getItem("token") != null) {
        rota = "/api/search-league";
      } else {
        rota = "/api/site-search-league";
      }

      axios
        .post(rota, { league: name })
        .then((response) => {
          this.events = response.data;
        })
        .catch(() => {})
        .finally(() => {
          this.loading = false;
          return;
        });
    },
    btnValorApostado(valor) {
      this.apostado = valor;
      this.calculaCotacao();
    },
    loadOdd(league, match, matchLive) {
      this.matchSelected = match.event_id || match.id;

      if (this.live) {
        var rota = "/api/site-list-odds-live/";
      } else {
        var rota = "/api/site-list-odds/";
      }
      this.mercados = [];
      this.match = match;
      this.liga = league;
      this.loading_odds = true;
      $("#modal-match").modal("show");
      axios
        .get(rota + this.match.id)
        .then((response) => {
          if (this.live) {
            this.mercados = response.data[0].mercados;
          } else {
            this.mercados = response.data;
          }
        })
        .catch(() => {})
        .finally(() => {
          this.loading_odds = false;
          return;
        });
    },
    async openShareMatch(match, event) {
      this.image_share = '';
      this.loading_share = true;
      $("#modal-share").modal("show");

      try {
        // Prepara os dados para o BannerEngine (formato nativo IHUB)
        const data = {
          siteLogo: this.server.logo_path ? (window.location.origin + '/' + this.server.logo_path) : (window.location.origin + '/img/logo.png'),
          title: 'FAÇA SUA APOSTA',
          league: event.league || 'Futebol Profissional',
          teamALogo: match.logo_home || (window.location.origin + '/img/no-logo.png'),
          teamBLogo: match.logo_away || (window.location.origin + '/img/no-logo.png'),
          teamA: match.home,
          teamB: match.away,
          dateTime: match.date || '',
          oddHome: match.odds && match.odds[0] ? match.odds[0].cotacao : '-',
          oddDraw: match.odds && match.odds[1] ? match.odds[1].cotacao : '-',
          oddAway: match.odds && match.odds[2] ? match.odds[2].cotacao : '-',
          siteUrl: window.location.host.toUpperCase(),
          instagram: this.server.social_instagram || '@ihub_bets'
        };

        const tpl = {
          accentColor: getComputedStyle(document.documentElement).getPropertyValue('--container_jogos--color').trim() || '#1aa6d0'
        };


        // Aguarda o carregamento do BannerEngine se necessário
        if (typeof window.BannerEngine === 'undefined') {
            throw new Error("BannerEngine não carregado");
        }

        const engine = (typeof window.BannerEngine === 'object') ? window.BannerEngine : new window.BannerEngine();
        const canvas = await engine.generateStory(data, tpl);
        this.image_share = canvas.toDataURL('image/png');

      } catch (error) {
        console.error("Erro ao gerar banner com BannerEngine:", error);
        this.image_share = null;
      } finally {
        this.loading_share = false;
      }
    },
    shareImageWhatsapp() {
      if (!this.image_share) return;
      let text = "Confira as odds para este jogo! 🚀";
      // No WhatsApp, enviamos o link se for URL, ou apenas o texto se for DataURL (já que não dá pra enviar blob direto via API simples)
      let url = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text + " " + (this.image_share.startsWith('data:') ? '' : this.image_share));
      window.open(url, "_blank");
    },
    downloadBanner() {
      if (!this.image_share) return;
      const link = document.createElement('a');
      link.href = this.image_share;
      link.download = 'banner-jogo.png';
      link.click();
    },
    translateLabel(label) {
      if (!label) return '';
      const translations = {
        'Fulltime Result': 'Resultado Final',
        'Match Goals': 'Gols na Partida',
        'Double Chance': 'Dupla Hipótese',
        'Draw No Bet': 'Empate Anula Aposta',
        'Both Teams to Score': 'Ambos Marcam',
        'Home': 'Casa',
        'Away': 'Fora',
        'Draw': 'Empate',
        'Over': 'Acima de',
        'Under': 'Abaixo de',
        'Yes': 'Sim',
        'No': 'Não',
        'Handicap Result': 'Resultado Handicap'
      };
      return translations[label] || label;
    },
    //odd.id, match.event_id, odd.odd, odd.cotacao, event.league, match.date, match.home, match.away
    addPalpite(
      uuid,
      idOdd,
      sport,
      partida,
      group_opp,
      odd,
      cotacao,
      league,
      date,
      home,
      away,
      type,
      cotacaoOriginal,
      logo_home = null,
      logo_away = null
    ) {
      if (this.live == true && localStorage.getItem("token") == null) {
        this.$notify({ group: 'foo', title: 'Aviso', text: 'Você precisa estar logado para apostar ao vivo!', type: 'warn' });
        return;
      }

      if (cotacao == 0) {
        return;
      }

      var newSelect = {
        uuid: uuid,
        idOdd: idOdd,
        partida: partida,
        odd: odd,
        palpite: odd, // Para compatibilidade com BilheteRealtime.vue
        cotacao: cotacao,
        league: league,
        date: date,
        match_temp: date, // Para compatibilidade com BilheteRealtime.vue
        home: home,
        away: away,
        logo_home: logo_home,
        logo_away: logo_away,
        sport: sport,
        group_opp: group_opp,
        type: type,
        is_live: this.live,
        selected: true,
        cotacaoOriginal: cotacaoOriginal,
      };

      // Verifica se a partida já está no cupom
      const index = this.selection.findIndex(s => s.partida === partida);

      if (index !== -1) {
        // Se for a mesma odd, remove
        if (this.selection[index].idOdd === idOdd) {
          this.selection.splice(index, 1);
        } else {
          // Se for outra odd da mesma partida, substitui
          this.selection.splice(index, 1, newSelect);
        }
      } else {
        // Se a partida não está no cupom, adiciona
        this.selection.push(newSelect);
      }

      this.calculaCotacao();
    },
    calculaCotacao() {
      this.total_cotacao = 1;
      this.retorno = 0;

      if (this.selection.length === 0) {
        this.total_cotacao = 0;
        return;
      }

      for (var i = 0; i < this.selection.length; i++) {
        this.total_cotacao = this.total_cotacao * this.selection[i].cotacao;
      }

      if (this.total_cotacao > this.max_cotacao) {
        this.total_cotacao = this.max_cotacao;
      }

      if (this.apostado > 0) {
        var premioEstimado = this.apostado * this.total_cotacao;
        var premioMaximoPermitido = this.max_cotacao * this.apostado;
        
        if (isNaN(premioEstimado) || !isFinite(premioEstimado)) {
            premioEstimado = 0;
        }

        this.retorno = premioEstimado;

        if (this.retorno > premioMaximoPermitido) {
          this.retorno = premioMaximoPermitido;
        }

        if (this.retorno > this.premio_max) {
          this.retorno = this.premio_max;
        }

        const valComission =
          this.retorno * (this.limitesUser.comissao_premio / 100);
        this.retornoCambista = this.retorno - valComission;
      }
    },
    removePalpite(id) {
      const index = this.selection.findIndex(s => s.idOdd == id);
      if (index !== -1) {
        this.selection.splice(index, 1);
        this.calculaCotacao();
      }
    },

    //Aqui
    removePalpites() {
      this.selection = [];
      this.calculaCotacao();
    },
    zeraPalpites(palpites) {
      if (!palpites || !palpites.length) return;
      for (var i = 0; i < palpites.length; i++) {
        $("div[taxaJogo=" + palpites[i].partida + "]").removeClass(
          "selecionado"
        );
        $("span[taxaJogo=" + palpites[i].partida + "]").removeClass(
          "odd-match-plus-right-selecionado"
        );
      }
    },
    mostraPalpites() {
      $('.modal').modal('hide');
      setTimeout(() => {
        $("#modal-cupon").modal("show");
      }, 200);
    },

    setValApostado(val) {
      this.apostado = val;
      this.calculaCotacao();
    },

    enviarAposta() {
      this.bilhetes = [];
      if (this.cliente == "") {
        this.showAlert("Insira um nome no campo cliente!");
        return;
      }
      if (this.apostado < this.valor_mini_aposta) {
        this.showAlert("Informe o valor da aposta!");
        return;
      }
      if (this.apostado > this.valor_max_aposta) {
        this.showAlert("Valor apostado superior ao permitido!");
        return;
      }
      if (this.selection.length < this.mini_jogos_bilhete) {
        this.showAlert("Verifique a quantidade minima de jogos no bilhete!");
        return;
      }

      if (this.use_bonus && !this.live) {
        this.showAlert("O saldo de bônus só pode ser usado em jogos AO VIVO!");
        this.use_bonus = false;
        return;
      }

      if (localStorage.getItem("token") != null) {
        if (parseFloat(this.apostado) > parseFloat(this.caixaUser.balance)) {
          this.showAlert("Saldo insuficiente para esta aposta!");
          return;
        }
      }

      if (this.total_cotacao <= this.mini_cotacao) {
        this.showAlert("Valor de cotação não permitido!");
        return;
      }

      if (this.selection.length > this.max_jogos_bilehte) {
        this.showAlert(
          `Quantidade permitida de jogos é de: (${this.max_jogos_bilehte})`
        );
        return;
      }

      let rota = "";
      let config = {};
      
      if (localStorage.getItem("token") != null) {
        rota = "/api/user-online-send-bet";
        config = { headers: { Authorization: "Bearer " + localStorage.getItem("token") } };
      } else {
        rota = "/api/send-pre-aposta";
      }

      this.loadingBtn = true;
      
      axios
        .post(rota, {
          valor_apostado: this.apostado,
          retorno_possivel: this.retorno,
          retorno_cambista: this.retornoCambista,
          cliente: this.cliente,
          total_palpites: this.selection.length,
          cotacao: this.total_cotacao,
          palpites: this.selection,
          use_bonus: this.use_bonus,
          pre_bet_id: this.pre_bet_id,
        }, config)
        .then((response) => {
          if (response.data.status == "error") {
            this.showAlert(response.data.message || response.data.status);
            this.loadingBtn = false;
            return;
          }

          if (localStorage.getItem("token") != null) {
            $("#modal-cupon").modal("hide");
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            this.bilhetes.push(response.data);
            this.bilhete_print = response.data;
            setTimeout(() => {
              $("#modal-bilhete").modal("show");
            }, 300);
            this.userLogado();
            this.link = "whatsapp://send?text=" + window.location.href + "api/bilhete/" + response.data.id;
          } else {
            // 🚀 PIN-BASED SYSTEM: Para convidados, mostra APENAS o modal de PIN
            this.cupom_pre_aposta = response.data.cupom;
            this.link = "https://api.whatsapp.com/send?text=" + encodeURIComponent("Fiz uma pré-aposta, valide meu PIN: " + response.data.cupom);
            $("#modal-cupon").modal("hide");
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            setTimeout(() => {
              $("#modal-pre-aposta").modal("show");
            }, 300);
          }

          // Limpa os campos (mesmo do antigo)
          this.loadingBtn = false;
          this.apostado = 0;
          this.retorno = 0;
          this.cliente = "";
          this.total_cotacao = 1;
          this.removePalpites();
        })
        .catch((error) => {
          let message = "Erro ao enviar aposta!";
          if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
          }
          this.showAlert(message);
          this.loadingBtn = false;
        })
        .finally(() => {});
    },
    enviarApostaLive() {
      this.bilhetes = [];
      if (this.cliente == "") {
        this.showAlert("Insira um nome no campo cliente!");
        return;
      }
      if (this.total_cotacao <= this.mini_cotacao) {
        this.showAlert("Valor de cotação não permitido!");
        return;
      }
      if (this.apostado < this.valor_mini_aposta) {
        this.showAlert("Informe o valor da aposta!");
        return;
      }
      if (this.apostado > this.valor_max_aposta) {
        this.showAlert("Valor apostado superior ao permitido!");
        return;
      }
      if (this.selection.length < this.mini_jogos_bilhete) {
        this.showAlert("Verifique a quantidade minima de jogos no bilhete!");
        return;
      }

      if (localStorage.getItem("token") != null) {
        let saldoDisponivel = this.use_bonus ? parseFloat(this.caixaUser.balance_bonus) : parseFloat(this.caixaUser.balance);
        if (parseFloat(this.apostado) > saldoDisponivel) {
          this.showAlert("Saldo insuficiente para esta aposta!");
          return;
        }
      }

      if (this.selection.length > this.max_jogos_bilehte) {
        this.showAlert("Verifique a quantidade máxima de jogos no bilhete!");
        return;
      }

      let rota = "";
      let config = {};
      
      if (localStorage.getItem("token") != null) {
        rota = "/api/user-online-send-bet";
        config = { headers: { Authorization: "Bearer " + localStorage.getItem("token") } };
      } else {
        rota = "/api/send-pre-aposta";
      }

      this.loadingBtn = true;

      axios
        .post(rota, {
          valor_apostado: this.apostado,
          retorno_possivel: this.retorno,
          cliente: this.cliente,
          total_palpites: this.selection.length,
          cotacao: this.total_cotacao,
          palpites: this.selection,
          use_bonus: this.use_bonus,
          pre_bet_id: this.pre_bet_id,
        }, config)
        .then((response) => {
          // ═══ FLUXO EXATO DO SISTEMA ANTIGO (Geral_bkp.vue:3380-3406) ═══
          if (localStorage.getItem("token") != null) {
             $("#modal-cupon").modal("hide");
             this.bilhetes.push(response.data);
             this.bilhete_print = response.data;
             $("#modal-bilhete").modal("show");
             this.userLogado();
             this.link = "whatsapp://send?text=" + encodeURIComponent("Confira meu bilhete no " + this.server.logo.split(" - ")[0] + ": " + window.location.origin + "/acompanhar?c=" + response.data.cupom);
          } else {
            // 🚀 PIN-BASED SYSTEM: Para convidados, mostra APENAS o modal de PIN
            this.cupom_pre_aposta = response.data.cupom;
            this.link = "https://api.whatsapp.com/send?text=" + encodeURIComponent("Fiz uma pré-aposta, valide meu PIN: " + response.data.cupom);
            $("#modal-cupon").modal("hide");
            $("#modal-pre-aposta").modal("show");
          }

          this.loadingBtn = false;
          this.apostado = 0;
          this.retorno = 0;
          this.cliente = "";
          this.pre_bet_id = null; // Reseta o ID do PIN
          this.total_cotacao = 1;
          this.removePalpites(this.selection);
        })
        .catch((error) => {
          let message = "Erro ao enviar aposta!";
          if (error.response && error.response.data && error.response.data.message) {
            message = error.response.data.message;
          }
          this.showAlert(message);
          this.loadingBtn = false;
        })
        .finally(() => {});
    },
    searchBilhete() {
      this.bilhetes = [];
      if (this.cupom == "") {
        this.showAlert("Preencha um PIN");
        return;
      }
      axios
        .post("/api/print-bilhete-cod", { cupom: this.cupom })
        .then((response) => {
          $("#modal-bilhete").modal("show");
          this.bilhetes = response.data; // Removido os colchetes extras
          this.bilhete_print = response.data[0];
          this.link = "whatsapp://send?text=" + encodeURIComponent("Confira meu bilhete no " + this.server.logo.split(" - ")[0] + ": " + window.location.origin + "/acompanhar?c=" + (response.data.cupom || response.data.codigo_bilhete));
        })
        .catch((error) => {
          console.log("erro", error);
          if (error.response.status == 404) {
            $("#modal-bilhete").modal("hide");
            this.showAlert("Bilhete não encontrado!");
            return;
          }
        })
    },
    printJogos(id) {
      if (!id) return;
      window.open("/print-bilhete/" + id, "_blank", "width=400,height=600");
    },
    downloadTicketImage(cupom) {
      const element = document.getElementById("printable-ticket");
      if (!element) return;

      // Load html2canvas if not present
      if (typeof html2canvas === "undefined") {
        const script = document.createElement("script");
        script.src =
          "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js";
        script.onload = () => this.generateImage(element, cupom);
        document.head.appendChild(script);
      } else {
        this.generateImage(element, cupom);
      }
    },
    generateImage(element, cupom) {
      html2canvas(element, {
        backgroundColor: "#F8ECC2",
        scale: 2, // Melhor qualidade
        logging: false,
        useCORS: true,
      }).then((canvas) => {
        const link = document.createElement("a");
        link.download = `bilhete-${cupom}.png`;
        link.href = canvas.toDataURL("image/png");
        link.click();
      });
    },
  },
};
</script>

<style>


.box-title, .header-campeonato-matchs, .btn-home-nexus, .cupon-confronto, button {
    font-family: 'Poppins', sans-serif !important;
}

.day-tab-demo strong {
    font-family: 'Poppins', sans-serif !important;
}

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

/* AJUSTES FINOS PREMIUM - IHUB V2.1.0 */
body {
    font-family: 'Poppins', sans-serif !important;
    font-weight: 400;
    font-size: 13px;
}

.box-title, .header-campeonato-matchs, .btn-home-nexus, .cupon-confronto, button {
    font-family: 'Poppins', sans-serif !important;
}

.menu-jogos {
    background: var(--sidebar--color) !important; /* Cor exata do demo */
    display: flex !important;
    padding: 0 !important;
    margin: 0 !important;
    margin-bottom: 0 !important;
    list-style: none;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100% !important;
    visibility: visible !important;
}

.header-jogos-nexus {
    display: block !important;
    width: 100% !important;
    margin-top: 0 !important;
    position: relative !important;
    z-index: 10 !important;
    background: transparent;
    border-radius: 0 0 4px 4px;
    overflow: hidden;
}

@media (min-width: 992px) {
    .header-jogos-nexus {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        height: 55px !important;
    }

    .tabs-container {
        flex: 1 !important;
        border-bottom: none !important;
    }

    .search-container-nexus {
        width: 350px !important;
        margin-top: 0 !important;
        padding-right: 15px !important;
        background: transparent !important;
    }
}

.search-container-nexus {
    padding: 8px 15px;
    background: var(--search_bar_bg--color, #fff);
}


.btn-navigator.ativo {
    border-bottom: 3px solid var(--modalidade_ativa--color, var(--primary-color));
}

.modality-item-demo a.ativo {
    background-color: var(--modalidade_ativa--color, var(--primary-color)) !important;
    color: #fff !important;
}

.day-tab.active, .day-tab-demo.active {
    background: var(--modalidade_ativa--color, var(--primary-color)) !important;
}

.day-tab.active a, .day-tab.active span, .day-tab.active strong {
    color: #fff !important;
}

.fa-trophy, .fa-star {
    color: #ffffff !important;
    filter: drop-shadow(0 0 2px rgba(255, 193, 7, 0.4));
}

.live-circle {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #e74c3c;
    border-radius: 50%;
    margin-left: 6px;
    box-shadow: 0 0 8px rgba(231, 76, 60, 0.6);
    animation: pulse-live 1.2s infinite ease-in-out;
}

@keyframes pulse-live {
    0% { transform: scale(0.8); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.8); opacity: 0.5; }
}

.sidebar-menu li a span {
    font-size: 13px !important;
    font-weight: 500 !important;
}

.sidebar-menu .header {
    font-size: 11px !important;
    font-weight: 600 !important;
    letter-spacing: 0.5px;
    padding: 15px 15px 10px 15px !important;
    background: transparent !important;
    color: #888 !important;
}

.sidebar-menu li a i {
    width: 20px;
    text-align: center;
    margin-right: 10px;
    font-size: 14px;
}

.treeview-menu li a span {
    font-size: 12px !important;
    font-weight: 400 !important;
}

.menu-jogos li {
    flex: 0 0 auto !important;
    text-align: center;
}

.menu-jogos li a {
    display: flex !important;
    align-items: center;
    gap: 8px;
    padding: 15px 20px !important;
    color: #fff !important;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
    text-decoration: none !important;
    transition: background 0.3s;
}




.menu-jogos li a:hover {
    background: rgba(255,255,255,0.1);
}

/* Abas de Datas */
\.nav-tabs-custom {
    background: var(--container_jogos--color, #fff);
    margin-bottom: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

#tabs-mobile.nav-tabs {
    border-bottom: none !important;
    display: flex;
    overflow-x: auto;
}

#tabs-mobile li a {
    border: none !important;
    padding: 10px 15px !important;
    color: #4a5568 !important;
    font-size: 12px;
}

#tabs-mobile li.active a {
    color: #111 !important;
    border-bottom: 2px solid var(--primary-color) !important; /* Verde Demo Indicator */
    background: transparent !important;
}

.header-campeonato-matchs {
    width: 100%;
    height: auto;
    padding: 8px 15px;
    background: var(--card_header_bg--color) !important; /* Verde exato do demo */
    color: var(--card_header_text--color, #FFF) !important;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 0px;
}


.btn-cyan {
    background-color: #23b5e1 !important;
    color: #fff !important;
    border: none !important;
}

/* Menu de Esportes (Igual ao Demo) */
.menu-jogos {
    display: flex !important;
    background: var(--sidebar--color, #111) !important; /* Fundo preto sólido */
    padding: 0 !important;
    margin: 0 0 15px 0 !important;
    list-style: none;
    width: 100% !important;
    border-radius: 4px;
    overflow: hidden;
}

.menu-jogos li {
    flex: 1 !important; /* Distribui igualmente */
    text-align: center !important;
    border-right: 1px solid #222 !important;
    float: none !important;
}

.menu-jogos li:last-child {
    border-right: none !important;
}

.menu-jogos li a {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px 5px;
    color: #fff !important;
    text-decoration: none;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    transition: all 0.2s;
    height: 100%;
}

.menu-jogos li.ativo {
    background: var(--primary-color, #23a73d) !important; /* Verde do Demo */
}

/* Abas de Datas (Estilo Clean do Demo) */
.nav-tabs-custom {
    background: transparent !important;
    border: none !important;
    margin-bottom: 20px;
}

#tabs-mobile {
    display: flex;
    border: none !important;
    gap: 20px;
    padding-left: 10px;
}

#tabs-mobile li {
    margin: 0;
}

#tabs-mobile li a {
    background: transparent !important;
    border: none !important;
    color: #666 !important;
    padding: 5px 0;
    font-size: 13px;
    text-transform: none;
    font-weight: 400;
}

#tabs-mobile li a strong {
    font-size: 14px;
    display: block;
    color: #333;
}

#tabs-mobile li.active a {
    color: var(--container_jogos--color, #23a73d) !important;
    border-bottom: 3px solid var(--container_jogos--color, #23a73d) !important;
    background: transparent !important;
}

#tabs-mobile li.active a strong {
    color: var(--container_jogos--color, #23a73d);
}

/* Busca */
.input-group-search {
    padding: 0 15px;
}

.input-group-search input {
    border-radius: 4px 0 0 4px !important;
    border: 1px solid #ddd;
    box-shadow: none;
}

/* Ajuste SVGs */
/* Ajuste SVGs */
.menu-jogos svg {
    margin-right: 8px;
    width: 16px;
    height: 16px;
}

/* LISTA DE JOGOS - Using custon.css styles (demo parity) */
/* All overrides removed to match demo.mybetserver.com exactly */

/* CUPOM STYLE FOTO 4 */
.cupom-fixed {
    position: relative;
    z-index: 10;
}

.ticket-title-new {
    background: #1a202c !important;
    color: #fff !important;
    padding: 12px 15px !important;
    border-radius: 4px 4px 0 0;
}

\.box-cupon {
    background: var(--cupom_body_bg--color, #fff);
    margin-bottom: 10px;
    padding: 0;
    border: 1px solid #eee;
    border-radius: 4px;
    position: relative;
    overflow: hidden;
}

.ticket-cut {
    height: 6px;
    background-image: radial-gradient(#f4f4f4 2px, transparent 2px);
    background-size: 8px 8px;
    background-position: 0 0;
    background-repeat: repeat-x;
    position: absolute;
    top: -4px;
    width: 100%;
}

.header-campeonato-cupon {
    background: var(--container_jogos--color);
    color: #fff;
    margin: 0;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.delete-palpite-cupon {
    cursor: pointer;
    font-size: 14px;
}

.cupon-confronto {
    padding: 0 !important;
    font-weight: 400;
    font-size: 14px;
    color: #444;
    list-style: none;
    text-align: left !important;
}

.cupon-data {
    padding: 0;
    font-size: 11px;
    color: #e74c3c;
    list-style: none;
    text-align: left !important;
}

.box-cupon li {
    padding: 2px 10px;
    font-size: 12px;
    list-style: none;
}

.cupon-left {
    color: var(--container_jogos--color, #23a73d);
    font-weight: 800;
}

.cupon-right {
    float: right;
    font-weight: 700;
    color: #333;
}

\.retorno-badge {
    background: var(--container_jogos--color, #fff);
    border: 1px solid var(--primary-color);
    color: var(--container_jogos--color);
    padding: 4px 10px;
    border-radius: 4px;
    font-weight: 800;
    font-size: 16px;
    display: inline-block;
}

.value-btn-group .btn-valor {
    background: var(--cupom_valor_btn--color, var(--container_jogos--color)) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    padding: 5px 2px !important;
}

.value-btn-group .btn-valor:hover,
.value-btn-group .btn-valor.active {
    background: var(--cupom_valor_btn_hover--color, var(--btn_salvar_hover--color)) !important;
}

.odd-match-plus-right.selecionado {
    background: var(--btn_selecionado-color, #23a73d) !important;
    border: none !important;
    color: #fff !important;
}

/* Pisca live */
.pisca {
    animation: pisca-animation 1s steps(5, start) infinite;
}

@keyframes pisca-animation {
    to { visibility: hidden; }
}



/* RESPONSIVIDADE MOBILE */
@media (max-width: 768px) {
    .menu-jogos li a {
        padding: 12px 10px !important;
        font-size: 10px !important;
    }
    
    .day-tab-demo {
        padding: 0 15px !important;
        min-width: 85px;
    }

    .modality-item-demo {
        flex: 0 0 auto !important;
        min-width: auto !important;
    }
}

/* BASE MODALIDADES (MOBILE FIRST) */
.modality-item-demo {
    flex: 0 0 auto !important;
    flex-shrink: 0 !important;
    text-align: center;
    min-width: auto !important;
    width: auto !important;
}

.modality-item-demo a {
    padding: 10px 6px !important;
    font-size: 10px !important;
    gap: 3px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.modality-icon-img {
    width: 14px !important;
    height: 14px !important;
}

/* DESKTOP EXCLUSIVE - AJUSTE FINO PREMIUM */
@media (min-width: 992px) {
    .modality-item-demo {
        border-right: 1px solid rgba(255,255,255,0.1) !important;
    }
    
    .modality-item-demo a {
        padding: 10px 22px !important;
        font-size: 13.5px !important;
        gap: 10px !important;
    }

    .modality-icon-img {
        width: 18px !important;
        height: 18px !important;
    }
}

.modality-item-demo a.ativo {
    background: var(--modalidade_ativa--color, var(--primary-color)) !important;
    color: #fff !important;
}

.logo-lg {
    font-family: "Antipasto", Helvetica !important;
    font-size: 20px !important;
    font-weight: bold !important;
    letter-spacing: 1px !important;
}

.btn-acessar-demo {
    background-color: var(--sidebar--color) !important;
    border-color: var(--sidebar--color) !important;
    color: #fff !important;
    border-radius: 4px !important;
    padding: 6px 12px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    box-shadow: none !important;
    transform: none !important;
}
.btn-acessar-demo:hover, .btn-acessar-demo:focus, .btn-acessar-demo:active {
    background-color: var(--sidebar--color) !important;
    color: #fff !important;
    box-shadow: none !important;
    transform: none !important;
}

.btn-cadastrar-demo {
    background-color: var(--container_jogos--color) !important;
    border-color: var(--container_jogos--color) !important;
    color: #fff !important;
    border-radius: 4px !important;
    padding: 6px 12px !important;
    font-weight: 400 !important;
    line-height: 1 !important;
    box-shadow: none !important;
    transform: none !important;
}
.btn-cadastrar-demo:hover, .btn-cadastrar-demo:focus, .btn-cadastrar-demo:active {
    background-color: var(--btn_salvar_hover--color, var(--container_jogos--color)) !important;
    color: #fff !important;
    box-shadow: none !important;
    transform: none !important;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.day-tab-demo.active {
    border-top: 3px solid var(--primary-color) !important;
    background: var(--card_bg--color, #fff) !important;
}

.live-circle {
    display: inline-block;
    width: 7px;
    height: 7px;
    background: #ff0000;
    border-radius: 50%;
    margin-left: 5px;
    animation: pulse-live 1s infinite;
}

@keyframes pulse-live {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
    100% { opacity: 1; transform: scale(1); }
}

.sidebar-menu .header {
    background: var(--sidebar_header--color, var(--primary-color)) !important;
    color: var(--sidebar_header_text--color, #FFF) !important;
    padding: 10px 15px !important;
    font-size: 11px !important;
    font-weight: 500 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

/* Reset agressivo de sombras e bordas para evitar 'sobras' */
.events-carousel-container, 
.events-carousel-container *,
#carouselbanners,
#carouselbanners *,
.row, 
.col-lg-12, 
.col-md-9 {
    box-shadow: none !important;
    text-shadow: none !important;
}

/* JOGOS EM DESTAQUE - PREMIUM CARDS */
.events-carousel-container {
    margin-bottom: 2px;
    padding: 0 0px;
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    outline: none !important;
}

.events-carousel-container * {
    box-shadow: none !important;
    border: none !important;
    outline: none !important;
}

.carousel-card, .carousel-card * {
    /* Permitir sombras apenas nos cards e seus elementos internos se necessário */
}

.carousel-card {
    box-shadow: none !important;
    border-radius: 12px !important;
    overflow: hidden !important;
}

#carouselbanners, #carouselbanners * {
    box-shadow: none !important;
    border: none !important;
}

.featured-title-nexus {
    font-size: 14px;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
}



.carousel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--destaque_header_bg--color, #23a73d) !important; /* Dinâmico */
    padding: 10px 15px;
    border-radius: 0 !important;
    color: var(--destaque_header_text--color, #fff) !important;
    box-shadow: none !important;
    border: none !important;
    outline: none !important;
    position: relative;
    margin-top: 0;
}

.carousel-header::before, .carousel-header::after {
    display: none !important; /* Remove sobras de pseudo-elementos do AdminLTE */
}

.carousel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 10px;
}

.carousel-title-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.carousel-controls {
    display: flex;
    gap: 10px;
}

.control-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.control-btn:hover {
    background: rgba(255,255,255,0.25);
}

.carousel-scroller-wrapper {
    display: flex;
    overflow-x: auto;
    gap: 12px;
    padding: 15px 0;
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    padding-right: 0px;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.carousel-card {
    flex: 0 0 calc((100% - 24px) / 3);
    min-width: 200px;
    height: 200px;
    scroll-snap-align: start;
    scroll-snap-stop: always;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 0; /* Removido para o topo ocupar 100% */
}

.card-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    z-index: 0;
}

.card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.85) 100%);
    z-index: 1;
}

.card-top-info {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 12px;
    background: rgba(87, 87, 87, 0.12); /* rgb(87 87 87 / 12%) */
    backdrop-filter: blur(1px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    white-space: nowrap;
    overflow: hidden;
}

.info-league {
    font-size: 9px; /* Um pouco menor para caber na linha */
    color: #fff;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.2px;
    flex: 1;
    overflow: hidden;
}

.info-league span {
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-league-flag {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.info-right {
    display: flex;
    gap: 8px;
    font-size: 8px;
    color: rgba(255,255,255,0.5);
    font-weight: 600;
    align-items: center;
}

.info-time {
    color: #fff !important;
    font-size: 10px;
    font-weight: 800;
}

.info-time i {
    color: rgba(255,255,255,0.5);
    font-size: 8px;
    margin-right: 3px;
    display: inline-block !important;
}

.card-competitors {
    position: relative;
    z-index: 2;
    margin-top: 10px;
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.competitor-logos {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-right: 12px;
}

.team-logo-img {
    width: 35px;
    height: 35px;
    object-fit: contain;
    flex-shrink: 0;
}

@media only screen and (max-device-width: 480px) {
    .team-logo-img {
        width: 22px;
        height: 22px;
    }
}

.logo-wrapper {
    width: 32px;
    height: 32px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    backdrop-filter: blur(4px);
}

.logo-wrapper img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.competitor-names {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.competitor-name {
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.carousel-card .offer-badge {
    background: var(--destaque_btn_bg--color, var(--primary-color)) !important;
    color: var(--destaque_btn_text--color, #fff) !important;
    border: none;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.card-markets {
    position: relative;
    z-index: 2;
    display: flex;
    gap: 10px;
    padding: 15px;
}

.market-btn {
    flex: 1;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 6px 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    color: #fff;
    backdrop-filter: blur(8px);
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.1);
}

.market-btn:hover:not(.disabled-market) {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.disabled-market {
    opacity: 0.5;
    cursor: not-allowed;
    background: rgba(255, 255, 255, 0.03) !important;
}

.market-btn.active {
    background: var(--btn_selecionado-color, #23a73d) !important;
    border-color: var(--btn_selecionado-color, #23a73d) !important;
}

.market-indicator {
    font-size: 8px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.6);
    font-weight: 700;
    margin-bottom: 1px;
    letter-spacing: 0.3px;
}

.market-btn.active .market-indicator {
    color: rgba(255,255,255,0.8);
}

.market-value {
    font-size: 13px;
    font-weight: 800;
    color: #fff;
}

.fa-trophy {
    color: #ffffff !important;
    filter: drop-shadow(0 0 2px rgba(255, 193, 7, 0.2));
}

.fa-star {
    color: #ffffff !important;
}

/* Telas muito grandes - 4 cards por linha */
@media (min-width: 1500px) {
    .carousel-card {
        flex: 0 0 calc((100% - 36px) / 4) !important;
        min-width: calc((100% - 36px) / 4) !important;
    }
}

/* Mobile Specific Adjustments for 2-Card View */
@media (max-width: 768px) {
    .carousel-scroller-wrapper {
        gap: 10px;
        padding: 10px 0;
    }

    .carousel-card {
        flex: 0 0 calc((100% - 10px) / 2);
        min-width: 165px;
        height: 165px; /* Altura reduzida para mobile */
    }

    .card-top-info {
        padding: 4px 8px;
    }

    .info-league {
        font-size: 8px;
        gap: 4px;
    }

    .info-league-flag {
        width: 12px;
        height: 12px;
    }

    .info-right {
        font-size: 7px;
        gap: 4px;
    }

    .info-time {
        font-size: 9px;
    }

    .card-competitors {
        margin-top: 6px;
        padding: 0 10px;
    }

    .competitor-logos {
        margin-right: 8px;
        gap: 2px;
    }

    .logo-wrapper {
        width: 24px;
        height: 24px;
        padding: 3px;
    }

    .competitor-name {
        font-size: 11px;
    }

    .card-markets {
        padding: 10px;
        gap: 5px;
    }

    .market-btn {
        padding: 3px 2px;
    }

    .market-indicator {
        font-size: 7px;
    }

    .market-value {
        font-size: 10px;
    }

    .offer-badge {
        padding: 2px 8px;
        font-size: 8px;
    }
}

.info-time-row {
    display: flex;
    align-items: center;
    gap: 12px; /* Mais espaço entre hora e cronômetro */
    margin-bottom: 2px;
}

.info-countdown {
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    background: rgba(255, 255, 255, 0.15);
    padding: 1px 6px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
    font-family: 'Inter', sans-serif;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.info-countdown i {
    font-size: 9px;
    color: #ffd700;
}

.card-top-info span, .card-top-info .info-right, .card-top-info .info-league {
    color: #ffffff !important; /* Texto branco nas info do topo */
}
</style>



