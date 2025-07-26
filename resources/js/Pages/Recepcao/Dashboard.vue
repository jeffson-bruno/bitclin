<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Área da Recepção</h2>
    </template>

    <div class="py-6 px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card Consultas de Hoje (modularizado) -->
        <CardConsultasHoje />

        <!-- Aqui virá depois o novo card de Exames de Hoje -->
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

const showModalHorarios = ref(false)
const horariosDoDia = ref([])
const dataSelecionada = ref('')

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

</script>
