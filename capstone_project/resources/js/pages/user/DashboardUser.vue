<script setup lang="ts">
import { NotebookPen, TrendingUp, Heart, BarChart } from 'lucide-vue-next';
import ChartLine from '@/components/ChartLine.vue';
import ChartPie from '@/components/ChartPie.vue';
import AppLayout from '@/layouts/AppLayout.vue';

// Ganti ref dengan variabel biasa
const hargaHariIni = 22500;
const persentaseKenaikan = 1.2;
const statusPerangkat = 'Aktif';

const pieData = {
  labels: ['Besar', 'Kecil'],
  datasets: [
    {
      data: [45, 55],
      backgroundColor: ['#3B82F6', '#93C5FD'],
      borderWidth: 2,
    },
  ],
};

const pieOptions = { responsive: true, plugins: { legend: { display: false } } };

const chartData = {
  labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
  datasets: [
    {
      label: 'Total Produksi',
      data: [250, 270, 260, 280, 300, 290, 310],
      fill: true,
      backgroundColor: 'rgba(59, 130, 246, 0.15)',
      borderColor: '#3B82F6',
      tension: 0.4,
    },
  ],
};

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { display: true, position: 'bottom' },
  },
};

const tableData = [
  { id: 1, sensor_id: 'S001', berat: 52.3, klaster: 1, kategori: 'Sedang' },
  { id: 2, sensor_id: 'S002', berat: 58.1, klaster: 2, kategori: 'Besar' },
];
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen p-16 bg-slate-50">
      <!-- Judul & Deskripsi -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-blue-700 font-poppins mb-1">Dashboard User</h1>
        <p class="text-sm text-gray-500 font-inter">Pemantauan sistem produksi telur harian</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-all">
          <div class="flex items-center space-x-3">
            <NotebookPen class="text-blue-600 w-6 h-6" />
            <h3 class="text-md font-semibold text-gray-700 font-poppins">Total Produksi</h3>
          </div>
          <p class="text-2xl font-bold mt-2 text-gray-900 font-poppins">30 Butir</p>
          <p class="text-sm text-gray-400 font-inter">Hari Ini</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-all">
          <div class="flex items-center space-x-3">
            <TrendingUp class="text-blue-600 w-6 h-6" />
            <h3 class="text-md font-semibold text-gray-700 font-poppins">Harga Telur</h3>
          </div>
          <p class="text-2xl font-bold mt-2 text-gray-900 font-poppins">
            Rp {{ hargaHariIni.toLocaleString('id-ID') }}
          </p>
          <p class="text-sm" :class="persentaseKenaikan > 0 ? 'text-green-500' : persentaseKenaikan < 0 ? 'text-red-500' : 'text-gray-400'">
            {{ persentaseKenaikan > 0 ? '+' : '' }}{{ persentaseKenaikan }}%
          </p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-all">
          <div class="flex items-center space-x-3">
            <Heart class="text-blue-600 w-6 h-6" />
            <h3 class="text-md font-semibold text-gray-700 font-poppins">Status Perangkat</h3>
          </div>
          <p class="text-2xl font-bold mt-2 text-gray-900 font-poppins">{{ statusPerangkat }}</p>
          <p class="text-sm text-gray-400 font-inter">Saat Ini</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-all">
          <div class="flex items-center space-x-3">
            <BarChart class="text-blue-600 w-6 h-6" />
            <h3 class="text-md font-semibold text-gray-700 font-poppins">Klasterisasi</h3>
          </div>
          <p class="text-2xl font-bold mt-2 text-gray-900 font-poppins">45% / 55%</p>
          <p class="text-sm text-gray-400 font-inter">Besar / Kecil</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid md:grid-cols-2 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-md font-semibold text-gray-700 mb-4 font-poppins">Produksi Harian</h3>
          <ChartLine :chartData="chartData" :chartOptions="chartOptions" />
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-md">
          <h3 class="text-md font-semibold text-gray-700 mb-4 font-poppins">Klasterisasi Telur</h3>
          <ChartPie :chartData="pieData" :chartOptions="pieOptions" />
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white p-6 rounded-2xl shadow-md">
        <h3 class="text-md font-semibold text-gray-700 mb-4 font-poppins">Tabel Klasterisasi</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
              <tr>
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Sensor</th>
                <th class="px-4 py-2">Berat</th>
                <th class="px-4 py-2">Klaster</th>
                <th class="px-4 py-2">Kategori</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in tableData" :key="row.id" class="border-b">
                <td class="px-4 py-2">{{ row.id }}</td>
                <td class="px-4 py-2">{{ row.sensor_id }}</td>
                <td class="px-4 py-2">{{ row.berat }}</td>
                <td class="px-4 py-2">{{ row.klaster }}</td>
                <td class="px-4 py-2">{{ row.kategori }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}
.font-inter {
  font-family: 'Inter', sans-serif;
}
</style>
