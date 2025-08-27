<template>
  <div class="h-full w-full">
    <Bar :data="chartData" :options="mergedOptions" :height="null" :width="null" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart,
  BarElement, CategoryScale, LinearScale, Tooltip, Legend, Title,
} from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend, Title)

const props = defineProps({
  chartData: { type: [Object, Function], required: true },
  chartOptions: { type: Object, default: () => ({}) },
})

const mergedOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false, // importante p/ respeitar a altura do wrapper
  resizeDelay: 200,           // evita “thrash” de resize
  ...props.chartOptions,
}))
</script>

<style scoped>
:deep(canvas){
  display: block;
  width: 100% !important;
  height: 100% !important; /* preenche a altura do wrapper */
}
</style>
