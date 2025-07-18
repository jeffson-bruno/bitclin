<style>
.fc-daygrid-day.fc-day.evento-consulta-dia {
  background-color: #fff7ed !important; /* destaque suave */
  border: 2px solid #fb923c !important; /* borda laranja */
  border-radius: 6px;
}
</style>



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
  eventDisplay: 'background', // Apenas o fundo destacado
  dayMaxEventRows: true,
  dateClick: async (info) => {
    const data = info.dateStr
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
}


onMounted(async () => {
  try {
    const response = await axios.get('/recepcao/consultas-e-agendamentos')
    //console.log('🔍 Consultas carregadas:', response.data.consultas)
    consultas.value = response.data.consultas
  } catch (e) {
    console.error('Erro ao carregar dados da recepção:', e)
  }
})
</script>
