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
        :key="`${consulta.tipo ?? 'consulta'}-${consulta.id}`"
        class="p-3 border rounded shadow-sm flex flex-col gap-1"
      >
        <p class="font-semibold flex items-center gap-2">
          {{ consulta.paciente || 'N/D' }}
          <span
            class="px-2 py-0.5 text-xs rounded"
            :class="(consulta.tipo === 'retorno') ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
          >
            {{ consulta.tipo === 'retorno' ? 'Retorno' : 'Consulta' }}
          </span>
        </p>

        <p class="text-sm text-gray-600">
          <span class="font-medium">Consulta com:</span>
          {{ consulta.medico || 'Não informado' }}
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

    <p v-else class="text-gray-500 text-center mt-4">
      Nenhuma consulta para hoje encontrada.
    </p>
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

// Filtro por nome do paciente (tolerante a null)
const consultasFiltradas = computed(() => {
  const termo = (busca.value || '').toLowerCase()
  return consultas.value.filter(c =>
    ((c.paciente || '').toLowerCase()).includes(termo)
  )
})

// Formata data com tolerância a null/formatos variados
function formatarData(data) {
  if (!data) return 'Não informada'

  // já está dd/mm/aaaa
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(data)) return data

  // ISO com hora
  if (typeof data === 'string' && data.includes('T')) {
    const d = new Date(data)
    if (isNaN(d)) return 'Data inválida'
    const dia = String(d.getDate()).padStart(2, '0')
    const mes = String(d.getMonth() + 1).padStart(2, '0')
    const ano = d.getFullYear()
    return `${dia}/${mes}/${ano}`
  }

  // yyyy-mm-dd
  if (/^\d{4}-\d{2}-\d{2}$/.test(data)) {
    const [ano, mes, dia] = data.split('-')
    return `${dia}/${mes}/${ano}`
  }

  return 'Não informada'
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
      paciente_id: pacienteSelecionado.value.id, // id é sempre o paciente_id
      tipo: tipoSenha.value
    })
    window.open(`/senhas/imprimir/${data.senha.id}`, '_blank')
    mostrarModalSenha.value = false
  } catch (e) {
    alert('Erro ao gerar senha.')
  }
}

// ---------------------------
// Buscar consultas/retornos do dia
// ---------------------------
onMounted(async () => {
  try {
    const { data } = await axios.get('/recepcao/consultas-hoje')
    // Garante campos mínimos e adiciona 'presente'
    consultas.value = (Array.isArray(data) ? data : []).map(c => ({
      tipo: c.tipo || 'consulta',
      id: c.id, // usamos paciente_id para presença
      paciente: c.paciente || 'N/D',
      data: c.data || c.data_consulta || null,
      medico: c.medico || null,
      telefone: c.telefone || null,
      presente: false,
    }))
  } catch (error) {
    console.error('Erro ao carregar consultas de hoje:', error)
    consultas.value = []
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
