<script setup lang="ts">
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

import type { ChartData, ChartOptions } from 'chart.js'

const props = defineProps<{
  chartData: ChartData<'pie'>,
  chartOptions?: ChartOptions<'pie'>
}>()

ChartJS.register(Title, Tooltip, Legend, ArcElement)


const pieData = {
  labels: ['Besar', 'Kecil'],
  datasets: [
    {
      data: [65, 35],
      backgroundColor: ['#0056B3', '#66BFFF'],
      borderWidth: 2,
    },
  ],
}

const pieOptions = {
  responsive: true,
  plugins: {
    legend: { display: false }, // Legend bisa diatur di luar chart jika ingin custom
  },
}
</script>

<template>
  <div class="w-full h-full min-h-[200px]">
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>
