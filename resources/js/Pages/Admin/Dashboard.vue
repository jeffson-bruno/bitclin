<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ModalPacientesConsultaHoje from '@/Components/ModalPacientesConsultaHoje.vue'
import ModalPacientesExameSemana from '@/Components/ModalPacientesExameSemana.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import PieChart from '@/Components/Charts/PieChart.vue'



import { ref, computed } from 'vue'
import axios from 'axios'

const showModalConsultas = ref(false)
const pacientesConsultaHojeList = ref([])

const showModalExames = ref(false)
const pacientesExamesSemanaList = ref([])




const props = defineProps({
  title: String,
  pacientesTotal: Number,
  consultasNoMes: Number,
  examesSemana: Number,
  despesasHoje: {
    type: Array,
    default: () => []
  },
  medicosHoje: {
    type: Array,
    default: () => [] 
  },
  pacientesConsultaHoje: Number,
  faturamentoDias: Array,
  lucroVsDespesas: Object
})

function abrirModalConsulta() {
  axios.get('/admin/pacientes/consultas-hoje')
    .then(res => {
      pacientesConsultaHojeList.value = res.data
      showModalConsultas.value = true  // CORRETO!
    })
    .catch(err => {
      console.error('Erro ao buscar pacientes:', err)
    })
}
function abrirModalExames() {
  axios.get('/admin/pacientes/exames-semana')
    .then(res => {
      pacientesExamesSemanaList.value = res.data
      showModalExames.value = true
    })
    .catch(err => {
      console.error('Erro ao buscar exames da semana:', err)
    })
}

const formatarHorario = (horario) => {
  if (!horario) return 'N/D'
  const [hora, minuto] = horario.split(':')
  return `${hora}:${minuto}`
}

const barChartData = computed(() => {
  return {
    labels: props.faturamentoDias.map(d => d.data),
    datasets: [{
      label: 'Faturamento',
      data: props.faturamentoDias.map(d => d.total),
      backgroundColor: '#4ade80'
    }]
  }
})

const pieChartData = {
  labels: ['Lucro', 'Despesas'],
  datasets: [{
    data: [props.lucroVsDespesas.entradas, props.lucroVsDespesas.despesas],
    backgroundColor: ['#16a34a', '#f87171']
  }]
}


</script>

<template>
  <AdminLayout>
    <template #header>
      {{ title }}
    </template>
  <!--------   ALERTA DE DESPESAS  ---------->
    <div
      v-if="despesasHoje.length"
      class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded w-full animate-pulse"
    >
      <p class="font-semibold mb-2 text-center text-lg">🚨 Despesas vencendo hoje:</p>
      <ul class="space-y-1 text-center">
        <li
          v-for="despesa in despesasHoje"
          :key="despesa.id"
          class="bg-yellow-50 inline-block px-4 py-2 rounded"
        >
          {{ despesa.nome }} - R$ {{ Number(despesa.valor).toFixed(2) }}
        </li>
      </ul>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Card 1 CONSULTAS HOJE -->
      <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-gray-500 text-sm uppercase mb-4">Consultas Hoje</h3>

        <div v-if="medicosHoje.length">
          <ul class="space-y-2">
            <li
              v-for="(medico, index) in medicosHoje"
              :key="index"
              class="text-blue-800 font-semibold"
            >
              {{ medico.nome }} <br>
              <span class="text-sm text-gray-600 font-normal">
                {{ formatarHorario(medico.hora_inicio) }} - {{ formatarHorario(medico.hora_fim) }}
              </span>
            </li>
          </ul>
        </div>

        <p v-else class="text-gray-400">Nenhum médico atendendo hoje.</p>
      </div>

      <!-- Card 2 - Pacientes Agendados Hoje (Consultas) -->
      <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-gray-500 text-sm uppercase mb-2">Pacientes Agendados Hoje</h3>
        <p class="text-3xl font-bold text-green-600 mt-2">{{ pacientesConsultaHoje }}</p>
        <button
          @click="abrirModalConsulta"
          class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
        >
          Visualizar
        </button>
      </div>
      <!-- Card 3 -->
      <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-gray-500 text-sm uppercase">Exames da Semana</h3>
        <p class="text-3xl font-bold text-purple-600 mt-2">{{ examesSemana }}</p>
        <button 
          @click="abrirModalExames"
          class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
        >
          Visualizar Exames
        </button>
      </div>
    </div>

    <!-- Gráficos -->
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
      <div class="bg-white rounded-md p-3 shadow-sm min-h-[260px]">
        <h2 class="text-xl font-semibold mb-4">Faturamento (últimos 7 dias)</h2>
        <BarChart :chartData="barChartData" :chartOptions="barChartOptions" />
      </div>

      <div class="bg-white rounded-md p-3 shadow-sm min-h-[150px]">
        <h2 class="text-xl font-semibold mb-4">Lucro vs Despesas (mês atual)</h2>
        <PieChart :chartData="pieChartData" :chartOptions="pieChartOptions" />
      </div>
    </div>

    

  </AdminLayout>

   <!-- Modal -->
    <ModalPacientesConsultaHoje
      :show="showModalConsultas"
      :pacientes="pacientesConsultaHojeList"
      @close="showModalConsultas = false"
    />
    <!-- Modal Pacientes Exames Semana -->
     <ModalPacientesExameSemana 
        :show="showModalExames" 
        :pacientes="pacientesExamesSemanaList" 
        @close="showModalExames = false" 
      />

 
</template>

