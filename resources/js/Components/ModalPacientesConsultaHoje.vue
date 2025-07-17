<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl p-6 relative">
      <!-- Título -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold">Pacientes Agendados para Hoje</h2>
        <button @click="$emit('fechar')" class="text-gray-600 hover:text-red-600 text-2xl">&times;</button>
      </div>

      <!-- Campo de busca -->
      <div class="mb-4">
        <input
          v-model="filtro"
          type="text"
          placeholder="Buscar paciente por nome..."
          class="w-full p-2 border border-gray-300 rounded"
        />
      </div>

      <!-- Tabela de pacientes -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded shadow">
          <thead>
            <tr class="bg-gray-100 text-left text-sm font-medium text-gray-700">
              <th class="px-4 py-2 border-b">Nome</th>
              <th class="px-4 py-2 border-b">Data</th>
              <th class="px-4 py-2 border-b">Médico</th>
              <th class="px-4 py-2 border-b">Especialidade</th>
              <th class="px-4 py-2 border-b">Telefone</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in pacientesFiltrados" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 border-b">{{ p.nome }}</td>
              <td class="px-4 py-2 border-b">{{ formatarData(p.data_consulta) }}</td>
              <td class="px-4 py-2 border-b">{{ p.medico?.name ?? 'Não informado' }}</td>
              <td class="px-4 py-2 border-b">{{ p.medico?.especialidade?.nome ?? 'Não informado' }}</td>
              <td class="px-4 py-2 border-b">{{ p.telefone }}</td>
            </tr>
            <tr v-if="pacientesFiltrados.length === 0">
              <td colspan="5" class="text-center text-gray-500 py-4">Nenhum paciente encontrado.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineProps, ref } from 'vue'

const props = defineProps({
  show: Boolean,
  pacientes: Array, // Espera receber todos os pacientes com consulta hoje (com médico/especialidade carregados)
})

const filtro = ref('')

const pacientesFiltrados = computed(() => {
  return props.pacientes.filter(p => {
    return p.nome.toLowerCase().includes(filtro.value.toLowerCase())
  })
})

function formatarData(data) {
  if (!data) return 'Não informado'
  const [ano, mes, dia] = data.split('-')
  return `${dia}/${mes}/${ano}`
}
</script>
