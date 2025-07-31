<template>
  <div class="p-8 bg-gray-100 min-h-screen">
    <!-- Cabeçalho -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2">📁 Prontuário Médico</h1>
      <div class="bg-white rounded shadow p-4 space-y-1">
        <p><strong>Nome:</strong> {{ paciente.nome }}</p>
        <p><strong>CPF:</strong> {{ paciente.cpf }}</p>
        <p><strong>Idade:</strong> {{ calcularIdade(paciente.data_nascimento) }} anos</p>
      </div>
    </div>

    <!-- Lista de Prontuários -->
    <div v-if="prontuarios.length > 0" class="space-y-6">
      <div
        v-for="(item, index) in prontuarios"
        :key="index"
        class="bg-white rounded shadow p-6 space-y-2 border-l-4 border-blue-500"
      >
        <h2 class="text-xl font-semibold">🗓️ {{ formatarData(item.created_at) }} — Dr(a). {{ item.medico.name }}</h2>
        <p><strong>Queixa Principal:</strong> {{ item.queixa_principal || '—' }}</p>
        <p><strong>História da Doença:</strong> {{ item.historia_doenca || '—' }}</p>
        <p><strong>Histórico Progressivo:</strong> {{ item.historico_progressivo || '—' }}</p>
        <p><strong>Histórico Familiar:</strong> {{ item.historico_familiar || '—' }}</p>
        <p><strong>Hábitos de Vida:</strong> {{ item.habitos_vida || '—' }}</p>
        <p><strong>Revisão de Sistemas:</strong> {{ item.revisao_sistemas || '—' }}</p>

        <!-- Documentos emitidos -->
        <div class="mt-4">
          <p><strong>Receitas:</strong> {{ item.receitas?.length || 0 }}</p>
          <p><strong>Atestados:</strong> {{ item.atestados?.length || 0 }}</p>
          <p><strong>Exames Solicitados:</strong> {{ item.exames?.length || 0 }}</p>
        </div>
      </div>
    </div>
    <div v-else class="text-center text-gray-500 mt-10">
      Nenhum prontuário encontrado para este paciente.
    </div>

    <!-- Ações -->
    <div class="mt-8 flex gap-4">
      <button
        @click="voltar"
        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded"
      >
        🔙 Voltar
      </button>

      <button
        @click="gerarPDF"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded"
      >
        📄 Gerar PDF
      </button>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props
const props = defineProps({
  paciente: Object,
  prontuarios: Array
})

const voltar = () => {
  router.get('/medico/dashboard')
}

const gerarPDF = () => {
  // Aqui você pode gerar um PDF usando jsPDF ou uma rota Laravel que retorne o PDF
  alert('🚧 Em breve: Geração de PDF!')
}

const calcularIdade = (data) => {
  const nascimento = new Date(data)
  const hoje = new Date()
  let idade = hoje.getFullYear() - nascimento.getFullYear()
  const m = hoje.getMonth() - nascimento.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) idade--
  return idade
}

const formatarData = (data) => {
  return new Date(data).toLocaleDateString('pt-BR')
}
</script>
