<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props recebidas
const props = defineProps({
  agendas: Array,
  medicos: Array
})

// Formulário
const form = useForm({
  medico_id: '',
  dia: '', // antes era "data", que causava conflito
  hora_inicio: '',
  hora_fim: ''
})

// Submissão
const submitForm = () => {
  form.post(route('admin.agenda-medica.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

// Exclusão
const excluir = (id) => {
  if (confirm('Tem certeza que deseja excluir esta agenda?')) {
    router.delete(route('admin.agenda-medica.destroy', id), {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Agenda Médica" />

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Título -->
      <h1 class="text-2xl font-bold">Cadastro de Agenda Médica</h1>

      <!-- Formulário -->
      <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-6 rounded shadow">

        <!-- Médico -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1">Médico</label>
          <select v-model="form.medico_id" class="w-full border rounded p-2" required>
            <option disabled value="">Selecione o médico</option>
            <option v-for="medico in medicos" :key="medico.id" :value="medico.id">
              {{ medico.name }}
            </option>
          </select>
        </div>

        <!-- Data -->
        <div>
          <label class="block text-sm font-medium mb-1">Data</label>
          <input type="date" v-model="form.dia" class="w-full border rounded p-2" required />
        </div>

        <!-- Hora início -->
        <div>
          <label class="block text-sm font-medium mb-1">Hora de Início</label>
          <input type="time" v-model="form.hora_inicio" class="w-full border rounded p-2" required />
        </div>

        <!-- Hora fim -->
        <div>
          <label class="block text-sm font-medium mb-1">Hora de Fim</label>
          <input type="time" v-model="form.hora_fim" class="w-full border rounded p-2" required />
        </div>

        <!-- Botão -->
        <div class="md:col-span-2 text-right">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-green-700">
            Cadastrar
          </button>
        </div>
      </form>

      <!-- Listagem -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Agendas Cadastradas</h2>
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 border">Médico</th>
              <th class="p-2 border">Data</th>
              <th class="p-2 border">Início</th>
              <th class="p-2 border">Fim</th>
              <th class="p-2 border text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="agenda in props.agendas" :key="agenda.id" class="hover:bg-gray-50">
              <td class="p-2 border">{{ agenda.medico.name }}</td>
              <td class="p-2 border">{{ agenda.data }}</td>
              <td class="p-2 border">{{ agenda.hora_inicio }}</td>
              <td class="p-2 border">{{ agenda.hora_fim }}</td>
              <td class="p-2 border text-center">
                <button @click="excluir(agenda.id)" class="text-red-600 hover:underline text-sm">
                  Excluir
                </button>
              </td>
            </tr>
            <tr v-if="!agendas.length">
              <td colspan="5" class="text-center text-gray-500 p-4">Nenhuma agenda cadastrada.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
