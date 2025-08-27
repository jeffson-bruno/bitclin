<template>
  <div class="h-full w-full">
    <Pie :data="chartData" :options="mergedOptions" :height="null" :width="null" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Pie } from 'vue-chartjs'
import {
  Chart,
  ArcElement, Tooltip, Legend, Title,
} from 'chart.js'

Chart.register(ArcElement, Tooltip, Legend, Title)

const props = defineProps({
  chartData: { type: [Object, Function], required: true },
  chartOptions: { type: Object, default: () => ({}) },
})

const mergedOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  resizeDelay: 200,
  ...props.chartOptions,
}))
</script>

<style scoped>
:deep(canvas){
  display: block;
  width: 100% !important;
  height: 100% !important;
}
</style>

