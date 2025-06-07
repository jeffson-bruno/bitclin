<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800 leading-tight">Cadastro de Paciente</h2>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white shadow-sm rounded p-6 space-y-4">

            <!-- Nome -->
            <div>
              <label class="block font-medium">Nome</label>
              <input v-model="form.nome" type="text" class="mt-1 w-full rounded border-gray-300" />
            </div>

            <!-- CPF -->
            <div>
              <label class="block font-medium">CPF</label>
              <input v-model="form.cpf" type="text" class="mt-1 w-full rounded border-gray-300" />
            </div>

            <!-- Telefone -->
            <div>
              <label class="block font-medium">Telefone</label>
              <input v-model="form.telefone" type="text" class="mt-1 w-full rounded border-gray-300" />
            </div>

            <!-- Procedimento -->
            <div>
              <label class="block font-medium">Procedimento</label>
              <select v-model="form.procedimento" @change="definirPreco" class="mt-1 w-full rounded border-gray-300">
                <option disabled value="">Selecione</option>
                <option value="consulta">Consulta</option>
                <option value="exame">Exame</option>
              </select>
            </div>

            <!-- Preço -->
            <div>
              <label class="block font-medium">Preço</label>
              <input v-model="form.preco" type="number" step="0.01" class="mt-1 w-full rounded border-gray-300" />
            </div>

            <!-- Pagamento -->
            <div>
              <label class="block font-medium">Pagamento Realizado?</label>
              <select v-model="form.pago" class="mt-1 w-full rounded border-gray-300">
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
              </select>
            </div>

            <!-- Forma de pagamento -->
            <div v-if="form.pago === true">
              <label class="block font-medium">Forma de Pagamento</label>
              <select v-model="form.forma_pagamento" class="mt-1 w-full rounded border-gray-300">
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao">Cartão</option>
                <option value="pix">Pix</option>
              </select>
            </div>

            <!-- Data de pagamento -->
            <div v-if="form.pago === false">
              <label class="block font-medium">Data do Pagamento</label>
              <input v-model="form.data_pagamento" type="date" class="mt-1 w-full rounded border-gray-300" />
            </div>

            <div>
              <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = ref({
  nome: '',
  cpf: '',
  telefone: '',
  procedimento: '',
  preco: '',
  pago: false,
  forma_pagamento: '',
  data_pagamento: '',
})

function definirPreco() {
  if (form.value.procedimento === 'consulta') {
    form.value.preco = 150.00
  } else if (form.value.procedimento === 'exame') {
    form.value.preco = 250.00
  } else {
    form.value.preco = ''
  }
}

function submit() {
  router.post('/pacientes', form.value)
}
</script>
