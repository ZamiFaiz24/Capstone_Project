<script setup lang="ts">
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js'
import { computed, defineProps } from 'vue'

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps<{
  chartData: { kecil: number; besar: number }[]
}>()

const barData = computed(() => ({
  labels: props.chartData.map((_, i) => `#${i + 1}`),
  datasets: [
    {
      label: 'Kecil',
      backgroundColor: '#38bdf8',
      data: props.chartData.map(d => d.kecil),
    },
    {
      label: 'Besar',
      backgroundColor: '#1e40af',
      data: props.chartData.map(d => d.besar),
    },
  ],
}))

const barOptions = {
  responsive: true,
  plugins: {
    legend: { display: true },
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 600,
      ticks: { stepSize: 100 },
    },
  },
}
</script>

<template>
  <div class="h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col">
    <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-2">
      Grafik Jumlah Telur Berdasarkan Klaster
    </h2>
    <Bar :data="barData" :options="barOptions" class="flex-1" />
  </div>
</template>