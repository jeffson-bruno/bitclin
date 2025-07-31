<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r shadow p-6 space-y-4">
      <h2 class="text-xl font-bold text-gray-700 mb-6">Atendimento</h2>
      <nav class="space-y-2">
        <button @click="abrirAtestado" class="block w-full text-left px-4 py-2 bg-blue-100 hover:bg-blue-200 rounded">📝 Emitir Atestado</button>
        <button @click="abrirExames" class="block w-full text-left px-4 py-2 bg-purple-100 hover:bg-purple-200 rounded">🧪 Solicitar Exames</button>
        <button @click="abrirReceita" class="block w-full text-left px-4 py-2 bg-green-100 hover:bg-green-200 rounded">💊 Emitir Receita</button>
        <button @click="abrirProntuario" class="block w-full text-left px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">📁 Prontuário</button>
      </nav>
    </aside>

    <!-- Conteúdo principal -->
    <main class="flex-1 p-8">
      <h1 class="text-2xl font-bold mb-6">👤 Dados do Paciente</h1>

      <div class="bg-white p-6 rounded shadow space-y-3 mb-8">
        <p><strong>Nome:</strong> {{ paciente.nome }}</p>
        <p><strong>Idade:</strong> {{ calcularIdade(paciente.data_nascimento) }} anos</p>
        <p><strong>Estado Civil:</strong> {{ paciente.estado_civil }}</p>
        <p><strong>Endereço:</strong> {{ paciente.endereco }}</p>
        <p><strong>CPF:</strong> {{ paciente.cpf }}</p>
        <p><strong>Telefone:</strong> {{ paciente.telefone ?? '—' }}</p>
      </div>

      <!-- Botão para abrir modal da anamnese -->
      <button
        @click="mostrarModalAnamnese = true"
        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow mb-6"
      >
        ➕ Criar Anamnese
      </button>

      <!-- Botão para finalizar atendimento -->
      <button
        @click="finalizarAtendimento"
        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow ml-4"
      >
        ✅ Finalizar Atendimento
      </button>
    </main>

    <!-- Modais (serão criados em breve) -->
    <ModalAnamnese
      v-if="mostrarModalAnamnese"
      :paciente="paciente"
      :medico="medico"
      @close="mostrarModalAnamnese = false"
    />

    <ModalReceita
      v-if="mostrarModalReceita"
      :paciente="paciente"
      :medico="medico"
      @close="mostrarModalReceita = false"
    />

    <ModalAtestado
      v-if="mostrarModalAtestado"
      :paciente="paciente"
      :medico="medico"
      @close="mostrarModalAtestado = false"
    />

    <ModalExame
      v-if="mostrarModalExame"
      :paciente="paciente"
      :medico="medico"
      @close="mostrarModalExame = false"
    />

   <!--- <ModalProntuario
      v-if="mostrarModalProntuario"
      :paciente="paciente"
      @close="mostrarModalProntuario = false"
    /> --->
  </div>
</template>

<script setup>
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import ModalAnamnese from '@/Components/ModalAnamnese.vue'
import ModalReceita from '@/Components/ModalReceita.vue'
import ModalAtestado from '@/Components/ModalAtestado.vue'
import ModalExame from '@/Components/ModalExames.vue'
//import ModalProntuario from '@/Components/ModalProntuario.vue'

// Props
const props = defineProps({
  paciente: Object,
  medico: Object
})

// Modais de ações
const mostrarModalAnamnese = ref(false)
const mostrarModalReceita = ref(false)
const mostrarModalAtestado = ref(false)
const mostrarModalExame = ref(false)
//const mostrarModalProntuario = ref(false)

// Métodos de navegação
const abrirReceita = () => mostrarModalReceita.value = true
const abrirAtestado = () => mostrarModalAtestado.value = true
const abrirExames = () => mostrarModalExame.value = true
//const abrirProntuario = () => mostrarModalProntuario.value = true

const abrirProntuario = () => {
  router.get(`/medico/prontuario/${props.paciente.id}`)
}


const finalizarAtendimento = async () => {
  try {
    await axios.post('/medico/finalizar-atendimento', {
      paciente_id: props.paciente.id
    })

    alert('Atendimento finalizado com sucesso!')
    router.get('/medico/dashboard') // Redireciona para o dashboard
  } catch (error) {
    console.error(error)
    alert('Erro ao finalizar atendimento')
  }
}


const calcularIdade = (dataNasc) => {
  if (!dataNasc) return '—'
  const nasc = new Date(dataNasc)
  const hoje = new Date()
  let idade = hoje.getFullYear() - nasc.getFullYear()
  const m = hoje.getMonth() - nasc.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--
  return idade
}


</script>
