<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Área da Recepção</h2>
    </template>

    <div class="py-6 px-4 space-y-6">

      <!-- Linha 1: Card de Consultas de Hoje (ocupando largura total) -->
      <div class="grid grid-cols-1">
        <CardConsultasHoje />
      </div>

      <!-- Linha 2: Cards lado a lado -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card - Médicos Atendendo Hoje -->
        <div class="bg-white p-6 rounded shadow text-center">
          <h3 class="text-gray-500 text-sm uppercase mb-4">Médicos Atendendo Hoje</h3>

          <div v-if="medicosHoje.length">
            <ul class="space-y-2">
              <li
                v-for="(medico, index) in medicosHoje"
                :key="index"
                class="text-blue-800 font-semibold"
              >
                {{ medico.nome }} <br />
                <span class="text-sm text-gray-600 font-normal">
                  {{ formatarHorario(medico.hora_inicio) }} - {{ formatarHorario(medico.hora_fim) }}
                </span>
              </li>
            </ul>
          </div>

          <p v-else class="text-gray-400">Nenhum médico atendendo hoje.</p>
        </div>

        <!-- Card - Buscar Prontuário (placeholder) -->
        <div class="bg-white p-6 rounded shadow text-center">
          <h3 class="text-gray-500 text-sm uppercase mb-4">🔍 Buscar Prontuário</h3>
            <input
              type="text"
              v-model="busca"
              @input="buscar"
              placeholder="Digite o nome do paciente"
              class="border px-3 py-2 rounded w-full mb-4"
            />

            <ul v-if="resultados.length">
              <li
                v-for="paciente in resultados"
                :key="paciente.id"
                class="mb-2 text-blue-600 hover:underline cursor-pointer"
              >
                <a :href="`/recepcao/historico-clinico/${paciente.id}`" target="_blank">
                  {{ paciente.nome }}
                </a>
              </li>
            </ul>

            <p v-else-if="busca.length > 2" class="text-gray-500">Nenhum paciente encontrado.</p>
          
        </div>

      </div>

    </div>


    <!-- Modal de horários ainda existente -->
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
import CardConsultasHoje from '@/Components/CardConsultasHoje.vue' // novo componente

import { ref } from 'vue'

import axios from 'axios'

const showModalHorarios = ref(false)
const horariosDoDia = ref([])
const dataSelecionada = ref('')

const busca = ref('')
const resultados = ref([])

const props = defineProps({
  medicosHoje: {
    type: Array,
    default: () => []
  },
})

const formatarHorario = (horario) => {
  if (!horario) return 'N/D'
  const [hora, minuto] = horario.split(':')
  return `${hora}:${minuto}`
}

const buscar = async () => {
  if (busca.value.length < 3) {
    resultados.value = []
    return
  }

  const response = await axios.get('/recepcao/buscar-paciente', {
    params: { termo: busca.value }
  })

  resultados.value = response.data
}

</script>
