<template>
  <div class="card card-dark card-outline">
    <div class="card-header bg-dark text-white border-0">
      <h3 class="card-title"><i class="fas fa-percentage me-2 text-warning"></i> Gerenciar Cotações Globais</h3>
    </div>
    <div class="card-body">
      <div class="row mb-4">
        <div class="col-md-3">
          <label class="form-label fw-bold">Filtrar por Esporte</label>
          <select v-model="selectedSport" class="form-select" @change="loadMarkets">
            <option value="football">Futebol</option>
            <option value="basketball">Basquete</option>
            <option value="mma">MMA / UFC</option>
            <option value="tennis">Tênis</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Nome do Mercado</th>
              <th width="150px">Ajuste (%)</th>
              <th width="150px">Status</th>
              <th width="100px" class="text-center">Ação</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="market in markets" :key="market.id">
              <td><strong>{{ market.name }}</strong></td>
              <td>
                <div class="input-group input-group-sm">
                  <input type="number" step="0.5" v-model="market.adjustment" class="form-control text-center">
                  <span class="input-group-text">%</span>
                </div>
              </td>
              <td>
                <select v-model="market.status" class="form-select form-select-sm" :class="market.status == 1 ? 'text-success' : 'text-danger'">
                  <option value="1">Ativo</option>
                  <option value="0">Bloqueado</option>
                </select>
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-success px-3" @click="saveMarket(market)">
                  <i class="fas fa-save"></i>
                </button>
              </td>
            </tr>
            <tr v-if="markets.length === 0">
               <td colspan="4" class="text-center py-4 text-muted">Aguardando dados da API ou esporte sem mercados...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const selectedSport = ref('football');
const markets = ref([
    { id: 1, name: 'Vencedor do Encontro', adjustment: 0, status: 1 },
    { id: 2, name: 'Total de Gols (Over/Under)', adjustment: 0, status: 1 },
    { id: 3, name: 'Ambos Marcam', adjustment: 0, status: 1 },
    { id: 4, name: 'Dupla Chance', adjustment: 0, status: 1 },
    { id: 5, name: 'Handicap Asiático', adjustment: 0, status: 1 },
]);

const loadMarkets = async () => {
  try {
     const response = await axios.get('/api/admin/global-markets', { params: { sport: selectedSport.value } });
     if (response.data.length > 0) {
        // Mapear para o formato do componente
     }
  } catch (error) {
    console.error('Erro ao buscar mercados');
  }
};

const saveMarket = async (market) => {
  try {
    await axios.post('/api/admin/global-markets', {
      market_name: market.name,
      sport: selectedSport.value,
      adjustment_percent: market.adjustment,
      status: market.status
    });
    alert('Mercado atualizado com sucesso!');
  } catch (error) {
    alert('Erro ao salvar ajuste.');
  }
};

onMounted(() => {
  // loadMarkets();
});
</script>
