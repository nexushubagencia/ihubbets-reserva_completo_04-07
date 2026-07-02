<template>
  <div class="card card-success card-outline">
    <div class="card-header bg-success text-white border-0">
      <h3 class="card-title"><i class="fas fa-layer-group me-2"></i> Gerenciar Mercados (Ativar/Bloquear)</h3>
    </div>
    <div class="card-body">
      <div class="row align-items-center mb-4">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search"></i></span>
            <input v-model="search" type="text" class="form-control border-start-0" placeholder="Buscar mercado (ex: Escanteios, Gols, Handicap)...">
          </div>
        </div>
        <div class="col-md-3">
          <select v-model="filterSport" class="form-select">
            <option value="football">Futebol</option>
            <option value="basketball">Basquete</option>
            <option value="mma">MMA / UFC</option>
          </select>
        </div>
        <div class="col-md-4 text-end">
           <button class="btn btn-outline-danger btn-sm me-2" @click="bulkAction(0)">Bloquear Tudo</button>
           <button class="btn btn-outline-success btn-sm" @click="bulkAction(1)">Ativar Tudo</button>
        </div>
      </div>

      <div class="row">
         <div v-for="market in filteredMarkets" :key="market.id" class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm border" :class="market.status ? 'border-success' : 'border-danger'">
               <div class="card-body d-flex justify-content-between align-items-center p-3">
                  <div>
                    <h6 class="mb-0 fw-bold">{{ market.name }}</h6>
                    <small class="text-muted">{{ market.sport }}</small>
                  </div>
                  <div class="form-check form-switch">
                    <input class="form-check-input custom-switch" type="checkbox" role="switch" v-model="market.status" @change="toggleMarket(market)">
                  </div>
               </div>
               <div v-if="!market.status" class="card-footer bg-danger bg-opacity-10 py-1 text-center">
                 <small class="text-danger fw-bold"><i class="fas fa-lock me-1"></i> MERCADO BLOQUEADO</small>
               </div>
            </div>
         </div>
      </div>
      
      <div v-if="filteredMarkets.length === 0" class="text-center py-5">
        <i class="fas fa-filter fa-3x text-muted mb-3"></i>
        <p class="text-muted">Nenhum mercado corresponde à sua busca.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const search = ref('');
const filterSport = ref('football');
const markets = ref([
    { id: 1, name: 'Vencedor do Encontro (1x2)', sport: 'football', status: true },
    { id: 2, name: 'Total de Gols (Over/Under)', sport: 'football', status: true },
    { id: 3, name: 'Ambos Marcam', sport: 'football', status: true },
    { id: 4, name: 'Dupla Chance', sport: 'football', status: true },
    { id: 5, name: 'Handicap Asiático', sport: 'football', status: true },
    { id: 6, name: 'Placar Exato', sport: 'football', status: false },
    { id: 7, name: 'Escanteios (Over/Under)', sport: 'football', status: true },
    { id: 8, name: 'Primeiro Time a Marcar', sport: 'football', status: true },
    { id: 9, name: 'Gols no 1º Tempo', sport: 'football', status: true },
    { id: 10, name: 'Vencedor do Set', sport: 'tennis', status: true },
]);

const filteredMarkets = computed(() => {
  return markets.value.filter(m => {
    const matchesSearch = m.name.toLowerCase().includes(search.value.toLowerCase());
    const matchesSport = m.sport === filterSport.value;
    return matchesSearch && matchesSport;
  });
});

const toggleMarket = async (market) => {
  try {
    // Integração com API
    await axios.post('/api/admin/global-markets', {
        market_name: market.name,
        sport: market.sport,
        status: market.status ? 1 : 0,
        adjustment_percent: 0 // Mantém ajuste atual
    });
  } catch (error) {
    market.status = !market.status; // Reverte se falhar
    alert('Erro ao atualizar status do mercado.');
  }
};

const bulkAction = (status) => {
  filteredMarkets.value.forEach(m => {
    m.status = status === 1;
    toggleMarket(m);
  });
};

onMounted(() => {
  // carregar via API real no futuro
});
</script>

<style scoped>
.custom-switch {
  width: 2.5em;
  height: 1.25em;
  cursor: pointer;
}
.card {
  transition: all 0.2s ease-in-out;
}
.card:hover {
  transform: translateY(-2px);
}
</style>
