<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ModalPacientesConsultaHoje from '@/Components/ModalPacientesConsultaHoje.vue'
import ModalPacientesExameSemana from '@/Components/ModalPacientesExameSemana.vue'

import { ref } from 'vue'
import axios from 'axios'

const showModalConsultas = ref(false)
const pacientesConsultaHojeList = ref([])

const showModalExames = ref(false)
const pacientesExamesSemanaList = ref([])

defineProps({
  title: String,
  pacientesTotal: Number,
  consultasNoMes: Number,
  despesasHoje: {
    type: Array,
    default: () => []
  },
  medicosHoje: {
    type: Array,
    default: () => [] 
  },
  pacientesConsultaHoje: Number,
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
      <h3 class="text-gray-500 text-sm uppercase mb-2">Consultas Hoje</h3>

      <div v-if="medicosHoje.length">
        <ul class="space-y-1">
          <li
            v-for="(medico, index) in medicosHoje"
            :key="index"
            class="text-blue-700 font-semibold"
          >
            {{ medico }}
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
        <p class="text-3xl font-bold text-purple-600 mt-2">{{ pacientesExamesSemanaList.length }}</p>
        <Button 
          @click="abrirModalExames"
          class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
        >
          Visualizar Exames
        </Button>
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

