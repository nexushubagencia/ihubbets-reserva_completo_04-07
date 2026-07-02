<template>
  <div class="card card-dark card-outline">
    <div class="card-header bg-dark text-white border-0 py-2">
      <h3 class="card-title"><i class="fas fa-users-cog me-2 text-success"></i> Gestão de {{ roleLabel }}</h3>
      <div class="card-tools">
        <button class="btn btn-success btn-sm" @click="openCreateModal">
          <i class="fas fa-plus me-1"></i> Adicionar {{ roleLabel }}
        </button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="bg-light">
            <tr>
              <th>ID</th>
              <th>Nome / Usuário</th>
              <th v-if="role === 'seller'">Gerente / Região</th>
              <th v-else>Região</th>
              <th>Saldo</th>
              <th>Comissão</th>
              <th class="text-center">Status</th>
              <th class="text-end px-4">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>#{{ user.id }}</td>
              <td>
                <div class="fw-bold">{{ user.name }}</div>
                <small class="text-muted">@{{ user.username }}</small>
              </td>
              <td v-if="role === 'seller'">
                <div v-if="user.parent">{{ user.parent.name }}</div>
                <small class="text-muted" v-if="user.region">{{ user.region.name }}</small>
                <small class="text-muted" v-else>Sem região</small>
              </td>
              <td v-else>
                 <span v-if="user.region" class="badge bg-info">{{ user.region.name }}</span>
                 <small v-else class="text-muted">Sem região</small>
              </td>
              <td class="fw-bold text-primary">R$ {{ formatMoney(user.balance) }}</td>
              <td>{{ user.commission_percent }}%</td>
              <td class="text-center">
                <span class="badge" :class="user.status ? 'bg-success' : 'bg-danger'">
                  {{ user.status ? 'Ativo' : 'Bloqueado' }}
                </span>
              </td>
              <td class="text-end px-4">
                <button @click="openEditModal(user)" class="btn btn-xs btn-primary me-2 mb-1" title="Editar"><i class="fas fa-edit"></i></button>
                <button @click="openBalanceModal(user)" class="btn btn-xs btn-success me-2 mb-1" title="Saldo"><i class="fas fa-wallet"></i></button>
                <button @click="toggleStatus(user)" :class="user.status ? 'btn-warning' : 'btn-secondary'" class="btn btn-xs me-2 mb-1" :title="user.status ? 'Bloquear' : 'Desbloquear'">
                    <i :class="user.status ? 'fa-lock' : 'fa-lock-open'" class="fas"></i>
                </button>
                <button @click="deleteColaborator(user)" class="btn btn-xs btn-danger mb-1" title="Excluir"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  role: { type: String, default: 'seller' }
});

const users = ref([]);
const roleLabel = computed(() => {
  return props.role === 'manager' ? 'Gerentes' : 'Cambistas';
});

const loadUsers = async () => {
    try {
        const endpoint = props.role === 'manager' ? '/admin/list-gerentes' : '/admin/list-cambistas';
        const response = await axios.get(endpoint);
        users.value = Array.isArray(response.data) ? response.data : (response.data.users || []);
    } catch (error) {
        console.error('Erro ao carregar colaboradores:', error);
    }
};

const formatMoney = (val) => {
  return parseFloat(val || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
};

const deleteColaborator = async (user) => {
    if (!confirm(`Tem certeza que deseja excluir ${user.name}?`)) return;
    try {
        const endpoint = props.role === 'manager' ? `/admin/deletar-gerente/${user.id}` : `/admin/deletar-cambista/${user.id}`;
        await axios.delete(endpoint);
        loadUsers();
    } catch (error) {
        alert('Erro ao excluir: ' + (error.response?.data?.error || 'Erro interno'));
    }
};

const toggleStatus = async (user) => {
    try {
        const endpoint = props.role === 'manager' ? `/admin/editar-gerente/${user.id}` : `/admin/editar-cambista/${user.id}`;
        await axios.put(endpoint, { status: !user.status });
        loadUsers();
    } catch (error) {
        alert('Erro ao alterar status');
    }
};

const openEditModal = (user) => {
    window.location.href = props.role === 'manager' ? `/admin/editar-gerente/${user.id}` : `/admin/editar-cambista/${user.id}`;
};

const openCreateModal = () => {
    window.location.href = props.role === 'manager' ? '/admin/cadastrar-gerentes' : '/admin/cadastrar-cambistas';
};

const openBalanceModal = (user) => {
     window.location.href = props.role === 'manager' ? `/admin/list-gerente-caixa/${user.id}` : `/admin/list-caixa-cambista/${user.id}`;
};

onMounted(() => {
    loadUsers();
});
</script>
