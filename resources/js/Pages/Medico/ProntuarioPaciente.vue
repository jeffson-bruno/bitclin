<template>
  <div class="p-8 bg-gray-100 min-h-screen">
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2">📁 Prontuário Médico</h1>
      <div class="bg-white rounded shadow p-4 space-y-1">
        <p><strong>Nome:</strong> {{ paciente.nome }}</p>
        <p><strong>CPF:</strong> {{ paciente.cpf }}</p>
        <p><strong>Idade:</strong> {{ calcularIdade(paciente.data_nascimento) }} anos</p>
      </div>
    </div>

    <div v-if="Object.keys(prontuariosPorData).length > 0">
      <div
        v-for="(lista, data) in prontuariosPorData"
        :key="data"
        class="mb-8"
      >
        <h2 class="text-xl font-semibold text-blue-700 mb-4 border-b pb-2">
          Atendimento em {{ data }}
        </h2>

        <div
          v-for="(item, index) in lista"
          :key="index"
          class="bg-white rounded shadow p-6 space-y-2 mb-6 border-l-4 border-blue-500"
        >
          <p><strong>Dr(a):</strong> {{ item.medico.name }}</p>
          <p><strong>Queixa Principal:</strong> {{ item.queixa_principal || '—' }}</p>
          <p><strong>História da Doença:</strong> {{ item.historia_doenca || '—' }}</p>
          <p><strong>Histórico Progressivo:</strong> {{ item.historico_progressivo || '—' }}</p>
          <p><strong>Histórico Familiar:</strong> {{ item.historico_familiar || '—' }}</p>
          <p><strong>Hábitos de Vida:</strong> {{ item.habitos_vida || '—' }}</p>
          <p><strong>Revisão de Sistemas:</strong> {{ item.revisao_sistemas || '—' }}</p>

          <div class="mt-4">
            <p><strong>Receitas:</strong> {{ item.receitas?.length || 0 }}</p>
            <p><strong>Atestados:</strong> {{ item.atestados?.length || 0 }}</p>
            <p><strong>Exames:</strong> {{ item.exames?.length || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-500 mt-10">
      Nenhum prontuário encontrado para este paciente.
    </div>

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

const props = defineProps({
  paciente: Object,
  prontuariosPorData: Object
})

const voltar = () => {
  router.get(`/medico/atendimento/${props.paciente.id}`)
}

const gerarPDF = () => {
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
</script>
