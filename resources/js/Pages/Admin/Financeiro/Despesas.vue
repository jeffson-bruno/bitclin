<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Check, Trash, X, Plus } from 'lucide-vue-next'
import Toast from '@/Components/Toast.vue'

const toastRef = ref(null)

const props = defineProps({
  despesas: {
    type: Array,
    default: () => [],
  },
})

const despesas = ref([])
watch(() => props.despesas, (val) => despesas.value = val, { immediate: true })

const showForm = ref(false)

const form = useForm({
  nome: '',
  valor: '',
  data_pagamento: '',
})

const submit = () => {
  form.post(route('admin.despesas.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showForm.value = false
      router.reload({ only: ['despesas'] })
    }
  })
}

const confirmarPagamento = async (id) => {
  try {
    await axios.post(route('admin.despesas.baixar', id))
    despesas.value = despesas.value.map(d =>
      d.id === id ? { ...d, pago: true } : d
    )
    toastRef.value.showToast('Pagamento confirmado com sucesso!', 3000)
  } catch (e) {
    console.error('Erro ao confirmar:', e)
    toastRef.value.showToast('Erro ao confirmar pagamento!', 3000)
  }
}

const excluirDespesa = async (id) => {
  if (!confirm('Tem certeza que deseja excluir esta despesa?')) return

  try {
    await axios.delete(route('admin.despesas.destroy', id))
    despesas.value = despesas.value.filter(d => d.id !== id)
    toastRef.value.showToast('Despesa excluída com sucesso!', 3000, 'error')
  } catch (e) {
    console.error('Erro ao excluir:', e)
    toastRef.value.showToast('Erro ao excluir despesa!', 3000, 'error')
  }
}

const mesAtual = new Date().toLocaleString('pt-BR', { month: 'long' }).toUpperCase()
</script>

<template>
  <AdminLayout>
    <Head title="Despesas da Clínica" />

    <div class="max-w-5xl mx-auto space-y-6">

      <!-- Botão topo -->
      <div class="flex items-center justify-between">
        <button type="button" @click="showForm = !showForm" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
          <Plus class="w-4 h-4" /> Cadastrar
        </button>
      </div>

      <!-- Collapse de formulário -->
      <div v-if="showForm" class="bg-white p-6 rounded shadow space-y-4">
        <h2 class="text-lg font-semibold">Nova Despesa</h2>
        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium">Nome</label>
            <input v-model="form.nome" type="text" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block text-sm font-medium">Valor</label>
            <input v-model="form.valor" type="number" step="0.01" class="w-full border rounded p-2" required />
          </div>
          <div>
            <label class="block text-sm font-medium">Data de Pagamento</label>
            <input v-model="form.data_pagamento" type="date" class="w-full border rounded p-2" required />
          </div>
          <div class="md:col-span-3 flex justify-end gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Cadastrar</button>
            <button type="button" @click="showForm = false" class="text-gray-600 hover:text-red-600 px-4 py-2 border rounded">
              <X class="w-4 h-4 inline" /> Fechar
            </button>
          </div>
        </form>
      </div>

      <!-- Tabela de despesas -->
      <div class="bg-white p-6 rounded shadow">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold">Despesas - Mês de {{ mesAtual }}</h2>
          <button type="button" class="text-blue-600 border border-blue-600 px-3 py-1 rounded hover:bg-blue-600 hover:text-white">
            Imprimir Relatório
          </button>
        </div>

        <table class="w-full border-collapse text-left text-sm">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="p-2 border">Nome</th>
              <th class="p-2 border">Valor</th>
              <th class="p-2 border">Data</th>
              <th class="p-2 border">Status</th>
              <th class="p-2 border text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="despesa in despesas.filter(d => new Date(d.data_pagamento).getMonth() === new Date().getMonth())"
              :key="despesa.id"
              class="hover:bg-gray-50">
              <td class="p-2 border">{{ despesa.nome }}</td>
              <td class="p-2 border">R$ {{ parseFloat(despesa.valor).toFixed(2) }}</td>
              <td class="p-2 border">{{ despesa.data_pagamento }}</td>
              <td class="p-2 border">
                <span :class="despesa.pago ? 'text-blue-600' : 'text-red-600'">
                  {{ despesa.pago ? 'Pago' : 'Pendente' }}
                </span>
              </td>
              <td class="p-2 border text-center space-x-2">
                <button
                  type="button"
                  @click.prevent="confirmarPagamento(despesa.id)"
                  v-if="!despesa.pago"
                  class="text-green-600 hover:text-green-800"
                  title="Confirmar pagamento">
                  <Check class="w-5 h-5 inline" />
                </button>
                <button
                  type="button"
                  @click.prevent="excluirDespesa(despesa.id)"
                  class="text-red-600 hover:text-red-800"
                  title="Excluir">
                  <Trash class="w-5 h-5 inline" />
                </button>
              </td>
            </tr>
            <tr v-if="!despesas.length">
              <td colspan="5" class="text-center text-gray-500 p-4">Nenhuma despesa registrada neste mês</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
  <Toast ref="toastRef" />

</template>

