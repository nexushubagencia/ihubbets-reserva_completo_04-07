<template>
  <div class="container-fluid">
    <div class="row pt-3">
      <div class="col-12">
        <h3 class="mb-4"><i class="fas fa-home me-2"></i> Página Inicial <small class="text-muted" style="font-size: 0.6em">2.0.0</small></h3>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
      <div class="col-md-7">
        <div class="card card-success card-outline">
          <div class="card-header border-0 bg-success text-white py-1">
            <h3 class="card-title"><i class="fas fa-handshake me-2"></i> Caixa dos Cambistas</h3>
          </div>
          <div class="card-body p-4">
            <div class="row g-4">
              <!-- Bilhete Icon Box -->
              <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box bg-info me-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-ticket-alt fa-2x"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Bilhete</div>
                    <div class="h5 mb-0 fw-bold">{{ stats.quantidade || 0 }}</div>
                  </div>
                </div>
              </div>

              <!-- Entradas Icon Box -->
              <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box bg-success me-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Entradas</div>
                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.entradas) }}</div>
                  </div>
                </div>
              </div>

              <!-- Em Aberto -->
              <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box bg-warning me-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-hourglass-half fa-2x text-white"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Em Aberto</div>
                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.entradas_abertas) }}</div>
                  </div>
                </div>
              </div>

              <!-- Saídas -->
              <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box bg-danger me-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-trophy fa-2x"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Saídas</div>
                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.saidas) }}</div>
                  </div>
                </div>
              </div>

              <!-- Comissões -->
              <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box bg-dark me-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-handshake fa-2x"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Comissões</div>
                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.comissoes) }}</div>
                  </div>
                </div>
              </div>

              <!-- Lançamentos -->
              <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box bg-primary me-3 rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="fas fa-exchange-alt fa-2x"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Lançamentos</div>
                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.lancamentos) }}</div>
                  </div>
                </div>
              </div>

               <!-- Saldo -->
               <div class="col-md-6 mb-3">
                <div class="info-card-custom d-flex align-items-center p-3 rounded shadow-sm">
                  <div class="icon-box me-3 rounded d-flex align-items-center justify-content-center" 
                       :class="stats.total >= 0 ? 'bg-success' : 'bg-danger'"
                       style="width: 60px; height: 60px;">
                    <i class="fas fa-chart-line fa-2x"></i>
                  </div>
                  <div>
                    <div class="text-muted fw-bold small">Saldo</div>
                    <div class="h5 mb-0 fw-bold">{{ formatCurrency(stats.total) }}</div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Chart Column -->
      <div class="col-md-5">
        <div class="card card-success card-outline h-100">
           <div class="card-header border-0 bg-success text-white py-1">
            <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i> Estatísticas Cambistas</h3>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center">
             <div class="text-center text-muted">
               <i class="fas fa-chart-area fa-5x mb-3 opacity-25"></i>
               <p>Gráfico de desempenho será carregado aqui</p>
             </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.info-card-custom {
  background-color: #f8f9fa;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
}
.info-card-custom:hover {
  transform: translateY(-2px);
  background-color: #fff;
}
.icon-box {
  color: white;
}
</style>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
  quantidade: 0,
  entradas: 0,
  entradas_abertas: 0,
  saidas: 0,
  comissoes: 0,
  lancamentos: 0,
  total: 0
});

const formatCurrency = (value) => {
  if (value === undefined || value === null) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

const loadStats = async () => {
  try {
    const response = await axios.get('/admin/relatorio-home');
    stats.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error);
  }
};

onMounted(() => {
  loadStats();
});
</script>
