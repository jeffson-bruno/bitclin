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
      <h2 class="text-2xl font-bold text-center mb-6">Atestado Médico</h2>

      <!-- Informações do Paciente -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
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

      <!-- Campo do Texto do Atestado -->
      <div class="mb-4">
        <label class="text-sm text-gray-600">Texto do Atestado</label>
        <textarea
          v-model="texto"
          rows="6"
          class="input-form"
          placeholder="Exemplo: Atesto para os devidos fins que o(a) paciente encontra-se impossibilitado(a) de exercer suas atividades habituais por X dias, necessitando de repouso domiciliar."
        ></textarea>
      </div>

      <!-- Campo CID -->
      <div class="mb-4">
        <label class="text-sm text-gray-600">CID(s)</label>
        <input v-model="cid" type="text" class="input-form" placeholder="Ex: M54.5, J10.1" />
      </div>

      <!-- Botão de Enviar -->
      <div class="flex justify-end mt-6">
        <button
          @click="enviarAtestado"
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

const texto = ref('')
const cid = ref('')

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

const enviarAtestado = () => {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/medico/gerar-atestado'
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
    texto: texto.value,
    cid: cid.value
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


