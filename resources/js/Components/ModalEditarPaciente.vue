<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-xl relative">
      <!-- Botão de fechar -->
      <button
        @click="$emit('close')"
        class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl"
        title="Fechar"
      >
        &times;
      </button>

      <h2 class="text-lg font-semibold mb-4">Editar Paciente</h2>

    <form @submit.prevent="atualizarPaciente">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Nome</label>
          <input v-model="form.nome" type="text" class="input-form" required />
        </div>

        <div>
          <label class="block font-medium">CPF</label>
          <input
            v-model="form.cpf"
            type="text"
            maxlength="14"
            inputmode="numeric"
            @input="form.cpf = mascaraCPF(form.cpf)"
            class="input-form"
            required
          />
        </div>

        <div>
          <label class="block font-medium">Telefone</label>
          <input
            v-model="form.telefone"
            type="text"
            maxlength="15"
            inputmode="numeric"
            @input="form.telefone = mascaraTelefone(form.telefone)"
            class="input-form"
            required
          />
        </div>

        <div>
          <label class="block font-medium">Data de Nascimento</label>
          <input
            type="text"
            v-model="form.data_nascimento"
            @input="form.data_nascimento = mascaraData(form.data_nascimento)"
            placeholder="dd/mm/aaaa"
            class="input-form"
          />
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium">Procedimento Realizado</label>
        <input v-model="form.procedimento" type="text" class="input-form" required />
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium">Já foi pago?</label>
        <select v-model="form.pago" class="input-form" required>
          <option :value="true">Sim</option>
          <option :value="false">Não</option>
        </select>
      </div>

      <div v-if="form.pago" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium">Forma de Pagamento</label>
          <input v-model="form.forma_pagamento" type="text" class="input-form" required />
        </div>

        <div>
          <label class="block text-sm font-medium">Data do Pagamento</label>
          <input v-model="form.data_pagamento" type="date" class="input-form" required />
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" @click="$emit('close')" class="px-4 py-2 bg-gray-300 rounded">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
      </div>
    </form>
  </div>
</div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import axios from 'axios'
import { mascaraCPF, mascaraTelefone, mascaraData } from '@/utils/masks'

const props = defineProps({
  paciente: Object
})
const emit = defineEmits(['close', 'atualizar-lista'])

const form = reactive({
  nome: '',
  cpf: '',
  telefone: '',
  data_nascimento: '',
  procedimento: '',
  pago: false,
  forma_pagamento: '',
  data_pagamento: ''
})

watch(() => props.paciente, (novo) => {
  if (novo) {
    Object.assign(form, {
      nome: novo.nome || '',
      cpf: novo.cpf || '',
      telefone: novo.telefone || '',
      data_nascimento: novo.data_nascimento || '',
      procedimento: novo.procedimento || '',
      pago: novo.pago === true || novo.pago === 'true' ? true : false,
      forma_pagamento: novo.forma_pagamento || '',
      data_pagamento: novo.data_pagamento || ''
    })
  }
}, { immediate: true })

function formatDateToISO(dateStr) {
  if (!dateStr) return null

  // Se já estiver no formato ISO (yyyy-mm-dd), retorna direto
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr

  const parts = dateStr.split('/')
  
  // Verificar se há 3 partes (dia, mês, ano)
  if (parts.length !== 3) return null // Retorna null se o formato não for esperado

  const [day, month, year] = parts

  // Verificar se os componentes são numéricos e válidos
  if (!/^\d{2}$/.test(day) || !/^\d{2}$/.test(month) || !/^\d{4}$/.test(year)) {
    return null // Retorna null se algum componente não for numérico ou válido
  }

  // Converte a data para o formato ISO (yyyy-mm-dd)
  return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
}


async function atualizarPaciente() {
  try {
    // Força booleano em pago
    form.pago = form.pago === true || form.pago === 'true' ? true : false

    // Converte as datas para ISO (yyyy-mm-dd)
    form.data_nascimento = formatDateToISO(form.data_nascimento)
    if (form.data_pagamento) {
      form.data_pagamento = formatDateToISO(form.data_pagamento)
    }

    await axios.put(`/pacientes/${props.paciente.id}`, form)

    emit('close')
    emit('atualizar-lista')
  } catch (error) {
    if (error.response && error.response.status === 422) {
      console.error('Erros de validação:', error.response.data.errors)
      alert('Erro de validação: ' + JSON.stringify(error.response.data.errors))
    } else {
      console.error(error)
      alert('Erro ao atualizar paciente')
    }
  }
}
</script>

<style scoped>
.input-form {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 0.375rem;
}
</style>