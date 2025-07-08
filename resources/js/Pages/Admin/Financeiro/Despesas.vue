<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  despesas: Array
})

const form = useForm({
  nome: '',
  valor: '',
  data_pagamento: '',
})

const submit = () => {
  form.post(route('admin.despesas.store'), {
    onSuccess: () => form.reset(),
    preserveScroll: true,
  })
}

const darBaixa = (id) => {
  if (confirm('Confirmar baixa da despesa?')) {
    form.put(route('admin.despesas.baixar', id), {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Controle de Despesas" />

    <div class="max-w-4xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold">Despesas da Clínica</h1>

      <!-- Formulário -->
      <form @submit.prevent="submit" class="bg-white p-6 rounded shadow grid grid-cols-1 gap-4">
        <div>
          <label class="block font-medium mb-1">Nome da Despesa</label>
          <input v-model="form.nome" type="text" class="w-full border rounded p-2" required />
        </div>
        <div>
          <label class="block font-medium mb-1">Valor</label>
          <input v-model="form.valor" type="number" step="0.01" class="w-full border rounded p-2" required />
        </div>
        <div>
          <label class="block font-medium mb-1">Data de Pagamento</label>
          <input v-model="form.data_pagamento" type="date" class="w-full border rounded p-2" required />
        </div>
        <div class="text-right">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Cadastrar</button>
        </div>
      </form>

      <!-- Listagem -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Despesas Cadastradas</h2>
        <table class="w-full border-collapse text-left">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 border">Nome</th>
              <th class="p-2 border">Valor</th>
              <th class="p-2 border">Data</th>
              <th class="p-2 border">Status</th>
              <th class="p-2 border text-center">Ação</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="despesa in despesas" :key="despesa.id" class="hover:bg-gray-50">
              <td class="p-2 border">{{ despesa.nome }}</td>
              <td class="p-2 border">R$ {{ parseFloat(despesa.valor).toFixed(2) }}</td>
              <td class="p-2 border">{{ despesa.data_pagamento }}</td>
              <td class="p-2 border">
                <span :class="despesa.paga ? 'text-green-600' : 'text-red-600'">
                  {{ despesa.paga ? 'Paga' : 'Pendente' }}
                </span>
              </td>
              <td class="p-2 border text-center">
                <button v-if="!despesa.paga" @click="darBaixa(despesa.id)"
                        class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                  Dar Baixa
                </button>
              </td>
            </tr>
            <tr v-if="!despesas.length">
              <td colspan="5" class="text-center text-gray-500 p-4">Nenhuma despesa registrada</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
