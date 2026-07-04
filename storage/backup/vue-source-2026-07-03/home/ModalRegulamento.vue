<template>
  <div class="modal fade" tabindex="-1" role="dialog" ref="modalElement">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" data-dismiss="modal" aria-label="Close" class="close">
            <span aria-hidden="true"><i class="fa fa-close"></i></span>
          </button>
          <h4 class="modal-title"><i class="fa fa-star"></i> Regulamento</h4>
        </div>
        <div class="modal-body box box-primary" style="max-height: 70vh; overflow-y: auto;">
          <div v-if="!regulamento" style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fa fa-refresh fa-spin fa-2x" style="color: #3c8dbc; margin-bottom: 15px;"></i>
            <p style="font-size: 14px;">Carregando...</p>
          </div>
          <div v-else v-html="regulamento" class="regulamento-content-demo"></div>
        </div>
        <div class="modal-footer" style="text-align: center; border-top: 1px solid #f4f4f4;">
          <button type="button" @click="aceitar" class="btn btn-success btn-lg" style="font-weight: 700; padding: 10px 60px; border-radius: 4px; text-transform: uppercase;">
            <i class="fa fa-check"></i> LI E ACEITO
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalRegulamento',
  props: {
    regulamento: {
      type: String,
      default: ''
    }
  },
  methods: {
    openModal() {
      // O Vue garante que $refs.modalElement existe e o jQuery age diretamente nele
      $(this.$refs.modalElement).modal('show');
    },
    closeModal() {
      $(this.$refs.modalElement).modal('hide');
    },
    aceitar() {
      this.closeModal();
      this.$emit('aceitou');
    }
  }
}
</script>
