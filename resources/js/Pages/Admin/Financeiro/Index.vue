<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import BarChart from '@/Components/Charts/BarChart.vue'
import PieChart from '@/Components/Charts/PieChart.vue'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const csrf = usePage().props.csrf_token


const props = defineProps({
  entradasHoje: Number,
  entradasSemana: Number,
  entradasMes: Number,
  pendentes: Array,
  totalPendentes: Number,
  resumo: Object,
  grafico_comparativo: Object,
  grafico_pagamentos: Object
})

// Configuração do gráfico de barras
const barChartData = computed(() => {
  if (!props.grafico_comparativo) return null

  return {
    labels: props.grafico_comparativo.labels,
    datasets: [
      {
        label: 'Mês Atual',
        backgroundColor: '#3b82f6',
        data: props.grafico_comparativo.mes_atual
      },
      {
        label: 'Mês Anterior',
        backgroundColor: '#10b981',
        data: props.grafico_comparativo.mes_anterior
      }
    ]
  }
})

const barChartOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'top' },
    title: { display: true, text: 'Comparativo de Ganhos por Procedimento' }
  }
}

// Configuração do gráfico de pizza
const pieChartData = computed(() => {
  if (!props.grafico_pagamentos) return null

  return {
    labels: props.grafico_pagamentos.labels,
    datasets: [
      {
        label: 'Formas de Pagamento',
        backgroundColor: ['#f97316', '#22c55e', '#3b82f6'],
        data: props.grafico_pagamentos.valores
      }
    ]
  }
})

const pieChartOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'bottom' },
    title: { display: true, text: 'Distribuição por Forma de Pagamento' }
  }
}
</script>


<template>
  <AdminLayout>
    <Head title="Financeiro" />

    <div class="max-w-7xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-800">Painel Financeiro</h1>

      <!-- Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow">
          <p class="text-gray-500 text-sm">Entradas do Dia</p>
          <p class="text-xl font-semibold text-green-600">R$ {{ resumo.entradas_dia }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <p class="text-gray-500 text-sm">Entradas da Semana</p>
          <p class="text-xl font-semibold text-green-600">R$ {{ resumo.entradas_semana }}</p>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <p class="text-gray-500 text-sm">Entradas do Mês</p>
          <p class="text-xl font-semibold text-green-600">R$ {{ resumo.entradas_mes }}</p>
        </div>
      </div>

      <!-- Card de Pendentes -->
        <div class="bg-red-100 p-4 rounded shadow">
        <h2 class="text-xl font-bold mb-2 text-red-700">Pendentes de Pagamento</h2>
        <p class="mb-2">Total de pacientes: {{ pendentes.length }}</p>
        <p class="mb-4">Valor total pendente: R$ {{ totalPendentes.toFixed(2) }}</p>

        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border">Nome</th>
                <th class="p-2 border">Procedimento</th>
                <th class="p-2 border">Valor</th>
                <th class="p-2 border">Data Cadastro</th>
                <th class="p-2 border text-center">Ação</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="p in pendentes" :key="p.id">
                <td class="p-2 border">{{ p.nome }}</td>
                <td class="p-2 border">{{ p.procedimento }}</td>
                <td class="p-2 border">R$ {{ p.preco.toFixed(2) }}</td>
                <td class="p-2 border">{{ p.created_at.substring(0,10) }}</td>
                <td class="p-2 border text-center">
                <form :action="route('admin.financeiro.baixar', p.id)" method="post">
                    <input type="hidden" name="_token" :value="csrf" />
                    <button type="submit" class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">
                        Dar baixa
                    </button>
                </form>
                </td>
            </tr>
            <tr v-if="!pendentes.length">
                <td colspan="5" class="text-center p-4 text-gray-500">Nenhum pagamento pendente</td>
            </tr>
            </tbody>
        </table>
        </div>


      <!-- Gráficos -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-4 rounded shadow">
          <BarChart :chart-data="barChartData" :chart-options="barChartOptions" />
        </div>
        <div class="bg-white p-4 rounded shadow">
          <PieChart :chart-data="pieChartData" :chart-options="pieChartOptions" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

