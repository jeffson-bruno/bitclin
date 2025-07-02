<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Área da Recepção</h2>
    </template>

    <div class="py-6 px-4">
      <h3 class="text-lg font-bold mb-4">📅 Datas de Consultas</h3>

      <!-- Calendário aqui -->
      <FullCalendar
        :events="consultas"
        :options="calendarOptions"
        class="mb-6 bg-white p-4 rounded shadow"
      />

      <!-- Lista de agendamentos -->
      <div class="bg-white p-4 rounded shadow">
        <h4 class="text-md font-semibold mb-2">📋 Agendamentos Realizados</h4>
        <ul v-if="agendamentos.length > 0">
          <li v-for="agendamento in agendamentos" :key="agendamento.id">
            {{ agendamento.paciente }} - {{ agendamento.data }} - {{ agendamento.medico }}
          </li>
        </ul>
        <p v-else class="text-gray-500">Nenhum agendamento encontrado.</p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Eventos do calendário (consultas)
const consultas = ref([])

// Lista dos agendamentos
const agendamentos = ref([])

const calendarOptions = {
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: 'pt-br',
  height: 500,
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/recepcao/consultas-e-agendamentos')
    consultas.value = data.consultas
    agendamentos.value = data.agendamentos
  } catch (e) {
    console.error('Erro ao carregar dados da recepção:', e)
  }
})
</script>
