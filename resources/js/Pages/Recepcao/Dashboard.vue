<template>
  <AuthenticatedLayout>
      <template #header>
        <h2 class="text-xl font-semibold text-gray-800">Área da Recepção</h2>
      </template>

      <div class="py-6 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Coluna da direita: Agendamentos -->
          <div class="bg-white p-4 rounded shadow h-full max-h-[500px] overflow-y-auto">
            <h3 class="text-md font-semibold mb-4">📋  Agendamentos Realizados</h3>

            <div class="mb-4">
              <input
                v-model="busca"
                type="text"
                placeholder="Buscar por nome do paciente..."
                class="w-full p-2 border border-gray-300 rounded"
              />
            </div>

            <ul v-if="agendamentosFiltrados.length > 0">
              <li
                v-for="agendamento in agendamentosFiltrados"
                :key="agendamento.id"
                class="mb-2 border-b pb-2"
              >
                <strong>{{ agendamento.paciente }}</strong><br>
                {{ agendamento.tipo }} - {{ agendamento.data }}<br>
                {{ agendamento.medico || agendamento.exame }}
              </li>
            </ul>
            <p v-else class="text-gray-500">Nenhum agendamento encontrado.</p>
          </div>

        </div>
      </div>

  </AuthenticatedLayout>

  <ModalHorariosMedico
    :show="showModalHorarios"
    :horarios="horariosDoDia"
    :dataSelecionada="dataSelecionada"
    @close="showModalHorarios = false"
  />

</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import ModalHorariosMedico from '@/Components/ModalHorariosMedico.vue'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// Eventos do calendário (consultas)
const consultas = ref([])

// Lista completa dos agendamentos
const agendamentos = ref([])

// Campo de busca
const busca = ref('')

const showModalHorarios = ref(false)
const horariosDoDia = ref([])
const dataSelecionada = ref('')


// Agendamentos filtrados com base no nome do paciente
const agendamentosFiltrados = computed(() => {
  return agendamentos.value.filter(a =>
    a.paciente.toLowerCase().includes(busca.value.toLowerCase())
  )
})


const calendarOptions = {
  plugins: [dayGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  locale: 'pt-br',
  height: 500,
  eventClick: async (info) => {
    const data = info.event.startStr
    dataSelecionada.value = new Date(data).toLocaleDateString('pt-BR')

    try {
      const response = await axios.get(`/api/recepcao/horarios-medicos?data=${data}`)
      horariosDoDia.value = response.data
      showModalHorarios.value = true
    } catch (error) {
      console.error('Erro ao buscar horários:', error)
    }
  }
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
