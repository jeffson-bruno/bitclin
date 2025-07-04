<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  exames: Array
})

// Formulário
const form = useForm({
  id: null,
  nome: '',
  valor: ''
})

const editando = ref(false)

function salvar() {
  if (editando.value) {
    router.put(route('admin.exames.update', form.id), form, {
      onSuccess: () => resetForm()
    })
  } else {
    router.post(route('admin.exames.store'), form, {
      onSuccess: () => resetForm()
    })
  }
}

function editar(exame) {
  form.id = exame.id
  form.nome = exame.nome
  form.valor = exame.valor
  editando.value = true
}

function excluir(id) {
  if (confirm('Deseja excluir este exame?')) {
    router.delete(route('admin.exames.destroy', id))
  }
}

function resetForm() {
  form.reset()
  form.id = null
  editando.value = false
}
</script>

<template>
  <AdminLayout>
    <template #header>Cadastro de Exames</template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Formulário -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold mb-4">{{ editando ? 'Editar Exame' : 'Novo Exame' }}</h2>
        <form @submit.prevent="salvar" class="space-y-4">
          <div>
            <label class="block text-sm font-medium">Nome do Exame</label>
            <input v-model="form.nome" type="text" class="w-full border rounded p-2" required />
          </div>

          <div>
            <label class="block text-sm font-medium">Valor (R$)</label>
            <input v-model="form.valor" type="number" step="0.01" min="0" class="w-full border rounded p-2" required />
          </div>

          <div class="flex justify-between items-center">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-green-700">
              {{ editando ? 'Atualizar' : 'Cadastrar' }}
            </button>
            <button v-if="editando" type="button" @click="resetForm" class="text-gray-600 hover:underline">
              Cancelar
            </button>
          </div>
        </form>
      </div>

      <!-- Lista -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Exames Cadastrados</h2>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-600 border-b">
              <th>Nome</th>
              <th>Valor</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="exame in props.exames" :key="exame.id" class="border-b hover:bg-gray-50">
              <td class="py-2">{{ exame.nome }}</td>
              <td class="py-2">R$ {{ Number(exame.valor).toFixed(2) }}</td>
              <td class="py-2 text-center space-x-2">
                <button @click="editar(exame)" class="text-blue-600 hover:underline">Editar</button>
                <button @click="excluir(exame.id)" class="text-red-600 hover:underline">Excluir</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
