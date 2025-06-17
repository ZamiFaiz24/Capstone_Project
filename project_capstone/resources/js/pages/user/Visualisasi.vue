<script setup lang="ts">
import ChartBar from '@/components/ChartBar.vue'
import ChartPie from '@/components/ChartPie.vue'
import ChartLine from '@/components/ChartLine.vue'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

// Data dummy untuk bar dan pie chart
const chartData = [
  { kecil: 400, besar: 350 },
  { kecil: 450, besar: 300 },
  { kecil: 150, besar: 350 },
  { kecil: 130, besar: 150 },
  { kecil: 120, besar: 400 },
  { kecil: 450, besar: 350 },
  { kecil: 200, besar: 400 },
]

const pieData = {
  labels: ['Besar', 'Kecil'],
  datasets: [
    {
      data: [65, 35],
      backgroundColor: ['#0056B3', '#66BFFF'],
    },
  ],
}

const pieOptions = {
  responsive: true,
  plugins: {
    legend: { display: false },
  },
}

// Data dummy untuk line chart
const lineData = {
  labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
  datasets: [
    {
      label: 'Produksi Telur',
      data: [120, 150, 170, 140, 180, 160, 175],
      borderColor: '#1e40af',
      backgroundColor: 'rgba(30,64,175,0.2)',
      tension: 0.4,
      fill: true,
    },
  ],
}

const lineOptions = {
  responsive: true,
  plugins: {
    legend: { display: true },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { stepSize: 20 },
    },
  },
}
</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50 p-8">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Perangkat</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>
      <!-- Chart Bar & Pie -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Chart Bar -->
        <ChartBar :chartData="chartData" />
        <!-- Chart Pie -->
        <div class="h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-6 flex flex-col">
          <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-2">
            Presentase Jumlah Setiap Klaster
          </h2>
          <div class="flex-1 flex flex-col items-center justify-center">
            <div class="w-48 h-48 mb-4">
              <ChartPie :chartData="pieData" :chartOptions="pieOptions" />
            </div>
          </div>
          <!-- Legend -->
          <div class="flex justify-end pr-6">
            <div class="flex flex-col gap-2 items-end">
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-[#0056B3]"></div>
                <span class="text-xs font-semibold text-gray-800 font-poppins">Besar 65%</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-[#66BFFF]"></div>
                <span class="text-xs font-semibold text-gray-800 font-poppins">Kecil 35%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Chart Line -->
      <div class="h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-6 flex flex-col mb-8">
        <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-2">
          Tren Produksi Telur Mingguan
        </h2>
        <ChartLine :chartData="lineData" :chartOptions="lineOptions" class="flex-1" />
      </div>
    </div>
  </AppLayout>
</template>