<template>
  <div class="container-fluid pt-3">
    <div class="row">
      <div class="col-12">
        <h3 class="mb-4"><i class="fas fa-star text-warning me-2"></i> Partidas em Destaque</h3>
        
        <div class="card card-success card-outline mb-4">
          <div class="card-header bg-success text-white border-0">
            <h3 class="card-title">Seleção de Jogos para Destaque</h3>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-4">
                <input v-model="filters.search" type="text" class="form-control" placeholder="Pesquisar time ou liga..." @input="loadGames">
              </div>
              <div class="col-md-3">
                <input v-model="filters.date" type="date" class="form-control" @change="loadGames">
              </div>
            </div>
            
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
              <table class="table table-sm table-hover align-middle">
                <thead class="bg-light sticky-top">
                  <tr>
                    <th>Data</th>
                    <th>Competição</th>
                    <th>Partida</th>
                    <th class="text-center">Ação</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="game in games" :key="game.id">
                    <td>{{ formatTime(game.start_time) }}</td>
                    <td><small class="text-muted">{{ game.league_name }}</small></td>
                    <td><strong>{{ game.home_team }}</strong> x <strong>{{ game.away_team }}</strong></td>
                    <td class="text-center">
                      <button class="btn btn-xs btn-primary bg-primary border-0" @click="pinGame(game)">
                        <i class="fas fa-plus"></i> Adicionar
                      </button>
                    </td>
                  </tr>
                  <tr v-if="games.length === 0">
                    <td colspan="4" class="text-center py-3">Carregando jogos ou nenhum jogo encontrado...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <!-- Lista de Destaques Atuais -->
        <div class="card card-dark card-outline">
          <div class="card-header bg-dark text-white border-0 py-2">
            <h3 class="card-title"><i class="fas fa-list me-2"></i> Jogos Atualmente em Destaque (Carrossel Home)</h3>
          </div>
          <div class="card-body p-0">
             <table class="table table-hover mb-0 align-middle">
               <thead>
                 <tr>
                    <th width="80px">Ordem</th>
                    <th>Partida</th>
                    <th>Imagem de Fundo (Caminho)</th>
                    <th width="120px">Cor Badge</th>
                    <th class="text-center" width="150px">Ações</th>
                 </tr>
               </thead>
               <tbody>
                  <tr v-for="(item, index) in featured" :key="item.id">
                    <td>
                      <input type="number" v-model="item.order" class="form-control form-control-sm text-center" @change="updateOrder(item)">
                    </td>
                    <td>
                      <div><strong>{{ item.home_team }}</strong> x <strong>{{ item.away_team }}</strong></div>
                      <small class="text-muted">{{ item.league_name }}</small>
                    </td>
                    <td>
                      <input type="text" v-model="item.background_path" class="form-control form-control-sm" placeholder="Ex: images/featured_bg.jpg">
                    </td>
                    <td>
                      <input type="color" v-model="item.badge_color" class="form-control form-control-sm p-1" style="height: 31px;">
                    </td>
                    <td class="text-center">
                      <div class="btn-group">
                        <button class="btn btn-sm btn-success" @click="saveMeta(item)" title="Salvar Alterações">
                          <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" @click="unpinGame(item.id)" title="Remover dos Destaques">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="featured.length === 0">
                    <td colspan="5" class="text-center py-4 text-muted">Ainda não há partidas selecionadas para destaque.</td>
                  </tr>
               </tbody>
             </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const games = ref([]);
const featured = ref([]);
const filters = ref({
  search: '',
  date: new Date().toISOString().split('T')[0]
});

const loadGames = async () => {
  try {
    const response = await axios.get('/api/admin/games-list', { params: filters.value });
    games.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar lista de jogos');
  }
};

const loadFeatured = async () => {
  try {
    const response = await axios.get('/api/admin/featured-matches');
    featured.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar destaques');
  }
};

const pinGame = async (game) => {
  try {
    await axios.post('/api/admin/featured-matches', {
        match_id: game.id,
        is_manual: game.type === 'manual',
    });
    loadFeatured();
  } catch (error) {
    alert('Erro ao fixar jogo.');
  }
};

const unpinGame = async (id) => {
  if(!confirm('Deseja remover este jogo dos destaques?')) return;
  try {
    await axios.delete(`/api/admin/featured-matches/${id}`);
    loadFeatured();
  } catch (error) {
    alert('Erro ao remover destaque.');
  }
};

const saveMeta = async (item) => {
  try {
    await axios.put(`/api/admin/featured-matches/${item.id}/meta`, {
        background_path: item.background_path,
        badge_color: item.badge_color
    });
    alert('Alterações salvas com sucesso!');
  } catch (error) {
    alert('Erro ao salvar metadados.');
  }
};

const updateOrder = async (item) => {
  try {
    // Reutilizando o mesmo endpoint ou criando um específico? 
    // Por simplicidade, vamos usar o de meta se ele aceitar ordem, 
    // mas o controlador atual não aceita. Vou atualizar o controlador.
    await axios.put(`/api/admin/featured-matches/${item.id}/meta`, {
        order: item.order
    });
  } catch (error) {
    console.error('Erro ao atualizar ordem');
  }
};

const formatTime = (dateStr) => {
  if(!dateStr) return '--:--';
  return new Date(dateStr).toLocaleTimeString('pt-BR', { hour: '2-2-digit', minute: '2-2-digit' });
};

onMounted(() => {
  loadGames();
  loadFeatured();
});
</script>
