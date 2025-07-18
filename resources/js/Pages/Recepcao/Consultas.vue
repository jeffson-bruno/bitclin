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
        :data-selecionada="dataSelecionada"
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ModalHorariosMedico from '@/Components/ModalHorariosMedico.vue'

const consultas = ref([])
const mostrarModal = ref(false)
const horariosDoDia = ref([])
const dataSelecionada = ref('')

const handleEventClick = async (info) => {
  const data = info.event.startStr.substring(0, 10)
  dataSelecionada.value = new Date(data).toLocaleDateString('pt-BR')

  try {
    const { data: result } = await axios.get('/recepcao/horarios-medicos', {
      params: { data }
    })
    horariosDoDia.value = result
    mostrarModal.value = true
  } catch (error) {
    console.error('Erro ao buscar horários:', error)
  }
}

const calendarOptions = {
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: 'pt-br',
  height: 500,
  eventClick: handleEventClick,
  dateClick: async (info) => {
  const data = info.dateStr // Ex: 2025-07-20
  dataSelecionada.value = new Date(data).toLocaleDateString('pt-BR')

  try {
    const { data: result } = await axios.get('/recepcao/horarios-medicos', {
      params: { data }
    })

    if (result.length > 0) {
      horariosDoDia.value = result
      mostrarModal.value = true
    } else {
      horariosDoDia.value = []
      mostrarModal.value = true // Modal também abre para mostrar "nenhum horário"
    }
  } catch (error) {
    console.error('Erro ao buscar horários:', error)
  }
}

}

onMounted(async () => {
  try {
    const { data } = await axios.get('/recepcao/consultas-e-agendamentos')
    consultas.value = data.consultas
  } catch (e) {
    console.error('Erro ao carregar dados da recepção:', e)
  }
})
</script>
