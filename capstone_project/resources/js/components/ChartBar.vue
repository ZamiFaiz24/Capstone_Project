<script setup lang="ts">
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Tooltip,
  Title,
  Legend
} from 'chart.js'
import type { ChartOptions, ChartData } from 'chart.js'

// Registrasi komponen Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

// Props dari parent
const props = defineProps<{
  chartData: ChartData<'bar'>
}>()

// Opsi chart
const barOptions: ChartOptions<'bar'> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        font: {
          size: 12,
          family: 'Poppins'
        }
      }
    },
    title: {
      display: false
    }
  },
  scales: {
    x: {
      grid: {
        display: false
      },
      ticks: {
        font: {
          size: 12,
          family: 'Poppins'
        }
      }
    },
    y: {
      beginAtZero: true,
      ticks: {
        font: {
          size: 12,
          family: 'Poppins'
        }
      },
      grid: {
        color: (ctx) => ctx.tick.value === 0 ? 'transparent' : '#f0f0f0'
      }
    }
  }
}

</script>

<template>
  <Bar :data="chartData" :options="barOptions" />
</template>
