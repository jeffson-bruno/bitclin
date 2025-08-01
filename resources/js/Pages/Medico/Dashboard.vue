<template>
  <div class="p-6 space-y-6">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-800">👨‍⚕️ Painel do Médico</h1>
      <button
        @click="logout"
        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
      >
        Sair
      </button>
    </div>

    <!-- Cards lado a lado -->
   <div class="flex flex-col md:flex-row gap-6">
    <!-- Pacientes Agendados -->
    <div class="bg-white p-6 rounded shadow flex-1">
  <h2 class="text-lg font-semibold mb-4">📋 Pacientes Agendados</h2>

  <div
    v-if="pacientesAgendados.length"
    class="max-h-[400px] overflow-y-auto pr-2"
  >
    <div
      v-for="paciente in pacientesAgendados"
      :key="paciente.id"
      class="border rounded p-4 mb-4 shadow-sm"
    >
      <div class="flex justify-between items-center mb-2">
        <div>
          <p class="text-lg font-bold">{{ paciente.nome }}</p>
          <p class="text-sm text-gray-600">
            Idade: {{ paciente.idade }} |
            Estado Civil: {{ paciente.estado_civil }} |
            Data da Consulta: {{ formatarData(paciente.data) }}
          </p>
          <p class="text-sm text-gray-500" v-if="paciente.senha">
            Senha: <span class="font-semibold">{{ paciente.senha }}</span>
          </p>
        </div>

        <div class="flex flex-col gap-2">
          <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded"
            @click="chamarSenha(paciente)"
          >
            Chamar Senha
          </button>
          <button
            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
            @click="iniciarAtendimento(paciente)"
          >
            Iniciar Atendimento
          </button>
        </div>
      </div>
    </div>
  </div>
  <p v-else class="text-gray-500 text-sm">Nenhum paciente agendado para hoje.</p>
</div>

  <!-- Pacientes Atendidos -->
  <div class="bg-white p-6 rounded shadow flex-1">
    <h2 class="text-lg font-semibold mb-4">✅ Pacientes Atendidos</h2>
    <div
      v-if="pacientesAtendidos.length"
      class="max-h-[400px] overflow-y-auto pr-2"
    >
      <ul class="space-y-2">
        <li
          v-for="paciente in pacientesAtendidos"
          :key="paciente.id"
          class="text-green-700 font-medium"
        >
          {{ paciente.nome }}
        </li>
      </ul>
    </div>
    <p v-else class="text-gray-500 text-sm">Nenhum paciente atendido ainda.</p>
  </div>
</div>


    <!-- TOAST VISUAL -->
    <Toast ref="globalToast" />
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { useToast } from '@/Composables/useToast'
import Toast from '@/Components/Toast.vue'
import { toastRef } from '@/Composables/useGlobalToast'

const { success, error } = useToast()
const globalToast = ref(null)

onMounted(() => {
  toastRef.value = globalToast.value
})

// Props
const props = defineProps({
  pacientesAgendados: {
    type: Array,
    default: () => []
  },
  pacientesAtendidos: {
    type: Array,
    default: () => []
  }
})


// Ações
const chamarSenha = async (paciente) => {
  try {
    const response = await axios.post(`/medico/chamar-senha/${paciente.id}`)
    response.data.success ? success(response.data.mensagem) : error(response.data.mensagem)
  } catch (err) {
    error(err.response?.data?.erro || 'Erro ao chamar a senha.')
  }
}

const iniciarAtendimento = (paciente) => {
  router.get(`/medico/atendimento/${paciente.id}`)
}

function formatarData(data) {
  if (!data) return '—'
  const [ano, mes, dia] = data.split('-')
  return `${dia}/${mes}/${ano}`
}

function logout() {
  router.post('/logout')
}
</script>

