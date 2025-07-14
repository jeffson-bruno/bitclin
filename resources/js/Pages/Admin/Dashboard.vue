<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  title: String,
  consultasHoje: Number,
  pacientesTotal: Number,
  consultasNoMes: Number,
  despesasHoje: Array
})
</script>

<template>
  <AdminLayout>
    <template #header>
      {{ title }}
    </template>
  <!--------   ALERTA DE DESPESAS  ---------->
    <div
      v-if="despesasHoje.length"
      class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded w-full animate-pulse"
    >
      <p class="font-semibold mb-2 text-center text-lg">🚨 Despesas vencendo hoje:</p>
      <ul class="space-y-1 text-center">
        <li
          v-for="despesa in despesasHoje"
          :key="despesa.id"
          class="bg-yellow-50 inline-block px-4 py-2 rounded"
        >
          {{ despesa.nome }} - R$ {{ Number(despesa.valor).toFixed(2) }}
        </li>
      </ul>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Card 1 -->
      <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-gray-500 text-sm uppercase">Consultas Hoje</h3>
        <p class="text-3xl font-bold text-blue-600 mt-2">{{ consultasHoje }}</p>
      </div>

      <!-- Card 2 -->
      <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-gray-500 text-sm uppercase">Total de Pacientes</h3>
        <p class="text-3xl font-bold text-green-600 mt-2">{{ pacientesTotal }}</p>
      </div>

      <!-- Card 3 -->
      <div class="bg-white p-6 rounded shadow text-center">
        <h3 class="text-gray-500 text-sm uppercase">Consultas no Mês</h3>
        <p class="text-3xl font-bold text-purple-600 mt-2">{{ consultasNoMes }}</p>
      </div>
    </div>
  </AdminLayout>
</template>

