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

    <!-- Lista de Pacientes -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-lg font-semibold mb-4">📋 Pacientes Agendados para Hoje</h2>

      <div v-if="pacientes.length">
        <div
          v-for="paciente in pacientes"
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

            <div class="flex gap-2">
              <button
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded"
                @click="chamarSenha(paciente)"
              >
                Chamar Senha
              </button>
              <button
                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm"
                @click="emitirReceita(paciente)"
              >
                Receita
              </button>
              <button
                class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm"
                @click="emitirAtestado(paciente)"
              >
                Atestado
              </button>
              <button
                class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm"
                @click="solicitarExames(paciente)"
              >
                Exames
              </button>
              <button
                class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm"
                @click="criarProntuario(paciente)"
              >
                Prontuário
              </button>
            </div>
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500 text-sm">Nenhum paciente agendado para hoje.</p>
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

//Chamar senha Paciente
const chamarSenha = async (paciente) => {
  try {
    const response = await axios.get(`/teste-chamar/${paciente.id}`)
    if (response.data.success) {
      success(response.data.mensagem)
    } else {
      error(response.data.mensagem)
    }
  } catch (err) {
    error('Erro ao chamar a senha.')
  }
}

// Props
const { pacientes } = defineProps({
  pacientes: {
    type: Array,
    default: () => []
  }
})

// Utilitários
function formatarData(data) {
  if (!data) return '—'
  const [ano, mes, dia] = data.split('-')
  return `${dia}/${mes}/${ano}`
}

function logout() {
  router.post('/logout')
}

// Ações do médico
function emitirReceita(paciente) { console.log('Emitir receita para:', paciente) }
function emitirAtestado(paciente) { console.log('Emitir atestado para:', paciente) }
function solicitarExames(paciente) { console.log('Solicitar exames para:', paciente) }
function criarProntuario(paciente) { console.log('Criar prontuário para:', paciente) }
</script>
