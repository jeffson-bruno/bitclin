<!-- resources/js/Components/AgendamentosRealizados.vue -->
<template>
  <div class="bg-white rounded-xl shadow p-4 max-h-[460px] overflow-y-auto">
    <h2 class="text-lg font-bold mb-4">Agendamentos Realizados</h2>

    <input
      v-model="busca"
      type="text"
      placeholder="Buscar paciente..."
      class="w-full mb-3 p-2 border rounded"
    />

    <ul v-if="agendamentosFiltrados.length > 0" class="space-y-2">
      <li
        v-for="agendamento in agendamentosFiltrados"
        :key="agendamento.id"
        class="p-3 border rounded hover:shadow transition-all"
      >
        <p class="font-semibold">{{ agendamento.paciente }}</p>
        <p class="text-sm text-gray-600">
          <span class="font-medium">{{ agendamento.tipo }}:</span>
          {{ agendamento.tipo === 'Consulta' ? agendamento.medico : agendamento.exame }}
        </p>
        <p class="text-sm text-gray-500">Data: {{ formatarData(agendamento.data) }}</p>
      </li>
    </ul>

    <p v-else class="text-gray-500 text-center mt-4">Nenhum agendamento encontrado.</p>
  </div>
</template>

<script setup>
import { defineProps, ref, computed } from 'vue'

const props = defineProps({
  agendamentos: Array
})

const busca = ref('')

const agendamentosFiltrados = computed(() => {
  return props.agendamentos.filter(a =>
    a.paciente.toLowerCase().includes(busca.value.toLowerCase())
  )
})

function formatarData(data) {
  return new Date(data).toLocaleDateString('pt-BR')
}
</script>
