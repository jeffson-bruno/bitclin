<template>
  <div class="bg-white p-4 rounded-xl shadow max-h-[500px] overflow-y-auto">
    <h3 class="text-md font-semibold mb-4">📋 Consultas de Hoje</h3>

    <input
      v-model="busca"
      type="text"
      placeholder="Buscar por nome do paciente..."
      class="w-full mb-3 p-2 border border-gray-300 rounded"
    />

    <ul v-if="consultasFiltradas.length > 0" class="space-y-3">
      <li
        v-for="consulta in consultasFiltradas"
        :key="consulta.id"
        class="p-3 border rounded shadow-sm flex flex-col gap-1"
      >
        <p class="font-semibold">{{ consulta.paciente }}</p>
        <p class="text-sm text-gray-600">
          <span class="font-medium">Consulta com:</span>
          {{ consulta.medico }}
        </p>
        <p class="text-sm text-gray-500">
          Data: {{ formatarData(consulta.data) }}
        </p>

        <div class="flex items-center justify-between mt-2">
          <label class="inline-flex items-center">
            <input type="checkbox" v-model="consulta.presente" class="mr-2">
            <span class="text-sm">Presente</span>
          </label>
          <button
            class="text-sm text-blue-600 hover:underline"
            @click="imprimirSenha(consulta)"
          >
            🖨️ Imprimir Senha
          </button>
        </div>
      </li>
    </ul>

    <p v-else class="text-gray-500 text-center mt-4">Nenhuma consulta para hoje encontrada.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const consultas = ref([])
const busca = ref('')

const consultasFiltradas = computed(() => {
  return consultas.value.filter(c =>
    c.paciente.toLowerCase().includes(busca.value.toLowerCase())
  )
})

function formatarData(data) {
  const [ano, mes, dia] = data.split('-')
  return `${dia}/${mes}/${ano}`
}


function imprimirSenha(consulta) {
  // Aqui você pode disparar a modal já existente passando os dados do paciente
  alert(`Senha para ${consulta.paciente} - Dr(a). ${consulta.medico}`)
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/recepcao/consultas-hoje')
    consultas.value = data.map(c => ({ ...c, presente: false }))
  } catch (error) {
    console.error('Erro ao carregar consultas de hoje:', error)
  }
})
</script>