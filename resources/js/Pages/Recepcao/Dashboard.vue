<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Área da Recepção</h2>
    </template>

    <div class="py-6 px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 📋 Coluna Esquerda: Agendamentos Realizados -->
        <div class="bg-white p-4 rounded-xl shadow max-h-[500px] overflow-y-auto">
          <h3 class="text-md font-semibold mb-4">📋 Agendamentos Realizados</h3>

          <input
            v-model="busca"
            type="text"
            placeholder="Buscar por nome do paciente..."
            class="w-full mb-3 p-2 border border-gray-300 rounded"
          />

          <ul v-if="agendamentosFiltrados.length > 0" class="space-y-3">
            <li
              v-for="agendamento in agendamentosFiltrados"
              :key="agendamento.id"
              class="p-3 border rounded shadow-sm"
            >
              <p class="font-semibold">{{ agendamento.paciente }}</p>
              <p class="text-sm text-gray-600">
                <span class="font-medium">{{ agendamento.tipo }}:</span>
                {{ agendamento.tipo === 'Consulta' ? agendamento.medico : agendamento.exame }}
              </p>
              <p class="text-sm text-gray-500">Data: {{ formatarData(agendamento.data) }}</p>
            </li>
          </ul>

          <p v-else class="text-gray-500 text-center mt-4">Nenhum agendamento encontrado.</p>
        </div>

        <!-- 🗓️ Coluna Direita: Calendário com Horários Médicos (futuramente) -->
        <!-- Podemos adicionar aqui o calendário depois, caso deseje -->

      </div>
    </div>

    <!-- Modal de Horários por Dia -->
    <ModalHorariosMedico
      :show="showModalHorarios"
      :horarios="horariosDoDia"
      :dataSelecionada="dataSelecionada"
      @close="showModalHorarios = false"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalHorariosMedico from '@/Components/ModalHorariosMedico.vue'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const agendamentos = ref([])
const busca = ref('')
const showModalHorarios = ref(false)
const horariosDoDia = ref([])
const dataSelecionada = ref('')

// Computa os agendamentos da semana filtrados pela busca
const agendamentosFiltrados = computed(() => {
  return agendamentos.value.filter(a =>
    a.paciente.toLowerCase().includes(busca.value.toLowerCase())
  )
})

// Função utilitária para formatar data
function formatarData(data) {
  return new Date(data).toLocaleDateString('pt-BR')
}

// Carrega os agendamentos da semana
onMounted(async () => {
  try {
    const { data } = await axios.get('/recepcao/agendamentos-semana')
    agendamentos.value = data
  } catch (error) {
    console.error('Erro ao carregar agendamentos:', error)
  }
})
</script>
