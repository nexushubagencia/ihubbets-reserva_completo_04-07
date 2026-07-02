<template>
  <div class="card card-success card-outline">
    <div class="card-header border-0 bg-success text-white">
      <h3 class="card-title">Inserir Nova Partida Personalizada</h3>
      <div class="card-tools">
        <button class="btn btn-sm btn-light" @click="showForm = !showForm">
          <i class="fas" :class="showForm ? 'fa-minus' : 'fa-plus'"></i> {{ showForm ? 'Fechar' : 'Nova Partida' }}
        </button>
      </div>
    </div>
    
    <div v-if="showForm" class="card-body">
      <form @submit.prevent="saveMatch">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold">Time da Casa</label>
            <input v-model="form.home_team" type="text" class="form-control" placeholder="Ex: Flamengo" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Time de Fora</label>
            <input v-model="form.away_team" type="text" class="form-control" placeholder="Ex: Vasco" required>
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">Odds Casa (1)</label>
            <input v-model="form.odd_home" type="number" step="0.01" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Odds Empate (X)</label>
            <input v-model="form.odd_draw" type="number" step="0.01" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Odds Fora (2)</label>
            <input v-model="form.odd_away" type="number" step="0.01" class="form-control" required>
          </div>
          
          <div class="col-md-4">
            <label class="form-label fw-bold">Liga</label>
            <input v-model="form.league_name" type="text" class="form-control" placeholder="Ex: Brasileirão" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Esporte</label>
            <select v-model="form.sport" class="form-select">
              <option value="football">Futebol</option>
              <option value="basketball">Basquete</option>
              <option value="mma">Luta/UFC</option>
              <option value="vaquejada">Vaquejada</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Data e Hora</label>
            <input v-model="form.start_time" type="datetime-local" class="form-control" required>
          </div>
          
          <div class="col-12 mt-4 text-end">
            <button type="submit" class="btn btn-success px-4" :disabled="loading">
              <i class="fas fa-save me-2"></i> {{ loading ? 'Salvando...' : 'Salvar Partida' }}
            </button>
          </div>
        </div>
      </form>
    </div>
    
    <div class="card-body p-0 mt-3">
      <table class="table table-hover mb-0">
        <thead class="bg-light">
          <tr>
            <th>Data</th>
            <th>Partida</th>
            <th>Liga</th>
            <th>Odds (1 X 2)</th>
            <th class="text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="match in matches" :key="match.id">
            <td>{{ formatDateTime(match.start_time) }}</td>
            <td><strong>{{ match.home_team }}</strong> x <strong>{{ match.away_team }}</strong></td>
            <td><span class="badge bg-secondary">{{ match.league_name }}</span></td>
            <td>
              <span class="badge bg-info me-1">{{ match.odd_home }}</span>
              <span class="badge bg-info me-1">{{ match.odd_draw }}</span>
              <span class="badge bg-info">{{ match.odd_away }}</span>
            </td>
            <td class="text-center">
              <button class="btn btn-sm btn-danger" @click="deleteMatch(match.id)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="matches.length === 0">
            <td colspan="5" class="text-center py-4 text-muted">Nenhuma partida personalizada cadastrada.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const showForm = ref(false);
const loading = ref(false);
const matches = ref([]);

const form = ref({
  home_team: '',
  away_team: '',
  odd_home: 2.00,
  odd_draw: 3.00,
  odd_away: 2.50,
  league_name: '',
  sport: 'football',
  start_time: ''
});

const loadMatches = async () => {
  try {
    const response = await axios.get('/api/admin/personalized-matches');
    matches.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar partidas:', error);
  }
};

const saveMatch = async () => {
  loading.value = true;
  try {
    await axios.post('/api/admin/personalized-matches', form.value);
    showForm.value = false;
    await loadMatches();
    // Reset form
    form.value = { home_team: '', away_team: '', odd_home: 2.00, odd_draw: 3.00, odd_away: 2.50, league_name: '', sport: 'football', start_time: '' };
  } catch (error) {
    alert('Erro ao salvar partida personalizada.');
  } finally {
    loading.value = false;
  }
};

const deleteMatch = async (id) => {
  if (confirm('Deseja excluir esta partida?')) {
    try {
      await axios.delete(`/api/admin/personalized-matches/${id}`);
      await loadMatches();
    } catch (error) {
      alert('Erro ao excluir partida.');
    }
  }
};

const formatDateTime = (dateStr) => {
  return new Date(dateStr).toLocaleString('pt-BR');
};

onMounted(() => {
  loadMatches();
});
</script>
