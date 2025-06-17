<script setup lang="ts">
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

const props = defineProps<{
  chartData: any,
  chartOptions?: any
}>()

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
  <Pie :data="chartData" :options="chartOptions" />
</template>