<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl p-6 relative">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">Pacientes com Exames Agendados Esta Semana</h2>
        <button @click="$emit('close')" class="text-gray-600 hover:text-red-600 text-2xl">&times;</button>
      </div>

      <div class="mb-4">
        <input
          v-model="filtro"
          type="text"
          placeholder="Buscar paciente por nome..."
          class="w-full p-2 border border-gray-300 rounded"
        />
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded shadow">
          <thead>
            <tr class="bg-gray-100 text-left text-sm font-medium text-gray-700">
              <th class="px-4 py-2 border-b">Nome</th>
              <th class="px-4 py-2 border-b">Data</th>
              <th class="px-4 py-2 border-b">Exame</th>
              <th class="px-4 py-2 border-b">Telefone</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in pacientesFiltrados" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 border-b">{{ p.nome }}</td>
              <td class="px-4 py-2 border-b">{{ p.data_exame }}</td>
              <td class="px-4 py-2 border-b">{{ p.exame }}</td>
              <td class="px-4 py-2 border-b">{{ p.telefone }}</td>
            </tr>
            <tr v-if="pacientesFiltrados.length === 0">
              <td colspan="4" class="text-center text-gray-500 py-4">Nenhum exame encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'

const props = defineProps({
  show: Boolean,
  pacientes: Array
})

const filtro = ref('')

const pacientesFiltrados = computed(() => {
  return props.pacientes.filter(p =>
    p.nome.toLowerCase().includes(filtro.value.toLowerCase())
  )
})
</script>
