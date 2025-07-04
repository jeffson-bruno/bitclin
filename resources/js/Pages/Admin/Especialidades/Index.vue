<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router, useForm } from '@inertiajs/vue3'


const props = defineProps({
  especialidades: Array
})

const form = useForm({
  nome: '',
})

function salvar() {
  router.post(route('admin.especialidades.store'), form, {
    onSuccess: () => form.reset()
  })
}

function excluir(id) {
  if (confirm('Tem certeza que deseja excluir?')) {
    router.delete(route('admin.especialidades.destroy', id))
  }
}
</script>

<template>
  <AdminLayout>
    <template #header>
      Especialidades
    </template>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- CARD: CADASTRO -->
        <div class="bg-white p-6 rounded shadow">
            <form @submit.prevent="salvar" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome da Especialidade</label>
                <input v-model="form.nome" type="text" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                Cadastrar
            </button>
            </form>
        </div>

        <!-- CARD: LISTA -->
        <div class="bg-white p-6 rounded shadow">
            <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                <th class="pb-2">ID</th>
                <th class="pb-2">Nome</th>
                <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in props.especialidades" :key="item.id" class="border-t">
                <td>{{ item.id }}</td>
                <td>{{ item.nome }}</td>
                <td>
                    <button @click="excluir(item.id)" class="text-red-600 hover:underline">Excluir</button>
                </td>
                </tr>
            </tbody>
            </table>
        </div>
        </div>

    
  </AdminLayout>
</template>
