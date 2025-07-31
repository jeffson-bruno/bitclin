<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-3xl overflow-y-auto max-h-[90vh]">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">📝 Nova Anamnese</h2>
        <button @click="$emit('close')" class="text-red-600 font-bold text-lg">✖</button>
      </div>

      <!-- Informações do paciente -->
      <div class="mb-4 space-y-1">
        <p><strong>Nome:</strong> {{ paciente.nome }}</p>
        <p><strong>Idade:</strong> {{ calcularIdade(paciente.data_nascimento) }} anos</p>
        <p><strong>Estado Civil:</strong> {{ paciente.estado_civil }}</p>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="salvarAnamnese" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="font-semibold">Queixa Principal</label>
            <textarea v-model="form.queixa_principal" class="w-full border p-2 rounded" rows="3" required></textarea>
          </div>

          <div>
            <label class="font-semibold">História da Doença</label>
            <textarea v-model="form.historia_doenca" class="w-full border p-2 rounded" rows="3"></textarea>
          </div>

          <div>
            <label class="font-semibold">Histórico Médico Progressivo</label>
            <textarea v-model="form.historico_medico" class="w-full border p-2 rounded" rows="3"></textarea>
          </div>

          <div>
            <label class="font-semibold">Histórico Familiar</label>
            <textarea v-model="form.historico_familiar" class="w-full border p-2 rounded" rows="3"></textarea>
          </div>

          <div>
            <label class="font-semibold">Hábitos de Vida</label>
            <textarea v-model="form.habitos_vida" class="w-full border p-2 rounded" rows="3"></textarea>
          </div>

          <div>
            <label class="font-semibold">Revisão de Sistemas</label>
            <textarea v-model="form.revisao_sistemas" class="w-full border p-2 rounded" rows="3"></textarea>
          </div>
        </div>

        <div>
          <label class="font-semibold">Observações Gerais</label>
          <textarea v-model="form.observacoes" class="w-full border p-2 rounded" rows="3"></textarea>
        </div>

        <div class="flex justify-end mt-4">
          <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            Salvar Anamnese
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Props
const props = defineProps({
  paciente: Object
})

const emit = defineEmits(['close'])

// Formulário reativo
const form = ref({
  queixa_principal: '',
  historia_doenca: '',
  historico_medico: '',
  historico_familiar: '',
  habitos_vida: '',
  revisao_sistemas: '',
  observacoes: ''
})

// Função salvar
const salvarAnamnese = async () => {
  try {
    const payload = {
      paciente_id: props.paciente.id,
      ...form.value
    }

    await axios.post('/medico/salvar-anamnese', payload)

    alert('Anamnese salva com sucesso!')
    emit('close')
  } catch (error) {
    console.error(error)
    alert('Erro ao salvar anamnese.')
  }
}

// Função auxiliar
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

