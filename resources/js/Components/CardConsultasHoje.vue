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
            <input
              type="checkbox"
              v-model="consulta.presente"
              @change="registrarPresenca(consulta)"
              class="mr-2"
            />
            <span class="text-sm">Presente</span>
          </label>
          <button
            class="text-sm text-blue-600 hover:underline"
            @click="abrirModalSenha(consulta)"
          >
            🖨️ Imprimir Senha
          </button>
        </div>
      </li>
    </ul>

    <p v-else class="text-gray-500 text-center mt-4">Nenhuma consulta para hoje encontrada.</p>
  </div>

  <!-- Modal para gerar senha -->
  <ModalSenha
    :mostrar="mostrarModalSenha"
    :tipo-inicial="tipoSenha"
    @confirmar="confirmarGeracaoSenha"
    @cancelar="mostrarModalSenha = false"
  />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import ModalSenha from '@/Components/ModalSenha.vue'

// Estado da lista de consultas
const consultas = ref([])
const busca = ref('')

// Filtro por nome do paciente
const consultasFiltradas = computed(() => {
  return consultas.value.filter(c =>
    c.paciente.toLowerCase().includes(busca.value.toLowerCase())
  )
})

// Formata data YYYY-MM-DD para DD/MM/YYYY
function formatarData(data) {
  const [ano, mes, dia] = data.split('-')
  return `${dia}/${mes}/${ano}`
}

// ---------------------------
// Modal de Senha
// ---------------------------
const mostrarModalSenha = ref(false)
const tipoSenha = ref('convencional')
const pacienteSelecionado = ref(null)

function abrirModalSenha(paciente) {
  pacienteSelecionado.value = paciente
  tipoSenha.value = 'convencional'
  mostrarModalSenha.value = true
}

async function confirmarGeracaoSenha(tipo) {
  tipoSenha.value = tipo
  try {
    const { data } = await axios.post('/senhas', {
      paciente_id: pacienteSelecionado.value.id,
      tipo: tipoSenha.value
    })
    window.open(`/senhas/imprimir/${data.senha.id}`, '_blank')
    mostrarModalSenha.value = false
  } catch (e) {
    alert('Erro ao gerar senha.')
  }
}

// ---------------------------
// Buscar consultas do dia
// ---------------------------
onMounted(async () => {
  try {
    const { data } = await axios.get('/recepcao/consultas-hoje')
    consultas.value = data.map(c => ({ ...c, presente: false }))
  } catch (error) {
    console.error('Erro ao carregar consultas de hoje:', error)
  }
})

async function registrarPresenca(consulta) {
  try {
    await axios.post(`/recepcao/presenca/${consulta.id}`)
    consulta.presente = true
  } catch (e) {
    alert('Erro ao registrar presença.')
  }
}

</script>
