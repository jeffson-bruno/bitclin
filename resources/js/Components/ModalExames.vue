<template>
  <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto relative p-6">
      
      <!-- Botão Fechar -->
      <button
        @click="$emit('close')"
        class="absolute top-3 right-3 text-gray-500 hover:text-red-500"
      >
        ✕
      </button>

      <!-- Título -->
      <h2 class="text-2xl font-bold text-center mb-6">Solicitação de Exames</h2>

      <!-- Informações do Paciente -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div>
          <label class="text-sm text-gray-600">Nome do Paciente</label>
          <div class="font-semibold">{{ paciente.nome }}</div>
        </div>
        <div>
          <label class="text-sm text-gray-600">CPF</label>
          <div class="font-semibold">{{ paciente.cpf ?? '—' }}</div>
        </div>
        <div>
          <label class="text-sm text-gray-600">Idade</label>
          <div class="font-semibold">{{ calcularIdade(paciente.data_nascimento) }} anos</div>
        </div>
      </div>

      <!-- Campo para adicionar exames -->
      <div class="mb-4">
        <label class="text-sm text-gray-600">Exames Solicitados</label>
        <div v-for="(exame, index) in exames" :key="index" class="flex gap-2 mb-2">
          <input
            type="text"
            v-model="exames[index]"
            class="input-form flex-1"
            placeholder="Digite o nome do exame"
          />
          <button
            v-if="exames.length > 1"
            @click="removerExame(index)"
            class="text-red-500 hover:text-red-700 font-bold"
            title="Remover exame"
          >
            ✕
          </button>
        </div>
        <button
          @click="adicionarExame"
          class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded"
        >
          Adicionar Exame
        </button>
      </div>

      <!-- Botão de Gerar PDF -->
      <div class="flex justify-end mt-6">
        <button
          @click="gerarPdf"
          class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded"
        >
          Gerar PDF e Salvar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['close'])
const props = defineProps({
  paciente: Object,
  medico: Object
})

const exames = ref(['']) // começa com um campo vazio

const calcularIdade = (dataNasc) => {
  if (!dataNasc) return '—'
  const nasc = new Date(dataNasc)
  if (isNaN(nasc)) return '—'
  const hoje = new Date()
  let idade = hoje.getFullYear() - nasc.getFullYear()
  const m = hoje.getMonth() - nasc.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--
  return idade
}

const adicionarExame = () => {
  exames.value.push('')
}

const removerExame = (index) => {
  exames.value.splice(index, 1)
}

const gerarPdf = () => {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/medico/gerar-solicitacao-exames'
  form.target = '_blank'

  // CSRF Token
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (token) {
    const csrf = document.createElement('input')
    csrf.type = 'hidden'
    csrf.name = '_token'
    csrf.value = token
    form.appendChild(csrf)
  }

  const campos = {
    paciente_id: props.paciente.id,
    exames: JSON.stringify(exames.value),
  }

  for (const key in campos) {
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = key
    input.value = campos[key]
    form.appendChild(input)
  }

  document.body.appendChild(form)
  form.submit()
  document.body.removeChild(form)

  emit('close')
}
</script>

<style scoped>
.input-form {
  @apply w-full rounded border border-gray-300 px-3 py-2 text-sm;
}
</style>
