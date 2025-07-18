<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Datas de Consultas</h2>
    </template>

    <div class="py-6 px-4">
      <FullCalendar
        :events="consultas"
        :options="calendarOptions"
        class="bg-white p-4 rounded shadow"
      />

      <ModalHorariosMedico
        :show="mostrarModal"
        :horarios="horariosDoDia"
        @close="mostrarModal = false"
      />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import ModalHorariosMedico from '@/Components/ModalHorariosMedico.vue'

import { ref } from 'vue'
import axios from 'axios'

const consultas = ref([])
const mostrarModal = ref(false)
const horariosDoDia = ref([])

const calendarOptions = {
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: 'pt-br',
  height: 500,
  eventClick: async (info) => {
    try {
      const dia = info.event.startStr // formato: YYYY-MM-DD
      const { data } = await axios.get('/recepcao/horarios-medicos', {
        params: { data: dia }
      })
      horariosDoDia.value = data
      mostrarModal.value = true
    } catch (err) {
      console.error('Erro ao buscar horários:', err)
    }
  }
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/recepcao/consultas-e-agendamentos')
    consultas.value = data.consultas
  } catch (e) {
    console.error('Erro ao carregar eventos:', e)
  }
})
</script>
