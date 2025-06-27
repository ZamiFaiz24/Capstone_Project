<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import ChartLine from '@/components/ChartLine.vue'
import ChartPie from '@/components/ChartPie.vue'
import { type BreadcrumbItem } from '@/types';
import { NotebookPen, TrendingUp, Heart } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';

interface HargaTelur {
  id: number
  tanggal: string
  harga: number
  keterangan: string
}


const props = defineProps<{
  statusPerangkat: string
  hargaHariIni: HargaTelur | null
  persentaseKenaikan: number | null
  // ...props lain
}>()

const kenaikanLabel = computed(() => {
  if (props.persentaseKenaikan === null) return 'Belum ada data perbandingan'
  const sign = props.persentaseKenaikan > 0 ? '+' : ''
  return `${sign}${props.persentaseKenaikan}% dari kemarin`
})

const kenaikanClass = computed(() => {
  if (props.persentaseKenaikan === null || props.persentaseKenaikan === 0) return 'text-gray-500'
  return props.persentaseKenaikan > 0 ? 'text-green-600' : 'text-red-600'
})

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
];

// Chart data 
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
      pointBackgroundColor: '#3B82F6',
      pointRadius: 4,
    },
    {
      label: 'Telur Besar',
      data: [120, 140, 130, 150, 160, 155, 170],
      fill: false,
      borderColor: '#f59e42',
      backgroundColor: 'rgba(245, 158, 66, 0.15)',
      tension: 0.4,
      pointBackgroundColor: '#f59e42',
      pointRadius: 4,
    },
    {
      label: 'Telur Kecil',
      data: [130, 130, 130, 130, 140, 135, 140],
      fill: false,
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.15)',
      tension: 0.4,
      pointBackgroundColor: '#10b981',
      pointRadius: 4,
    },
  ],
}

const chartOptions = {
  responsive: true,
  plugins: {
    legend: { display: true, position: 'bottom', labels: { font: { family: 'Inter', size: 13 } } },
    title: { display: false },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { color: '#2563eb', font: { family: 'Inter', size: 12 } },
      grid: { color: '#e5e7eb' },
    },
    x: {
      ticks: { color: '#2563eb', font: { family: 'Inter', size: 12 } },
      grid: { display: false },
    },
  },
}


// Sample data for table
const tableData = ref([
  { id: 1, sensor_id: "S001", berat: "52.3", deep_klaster: 1, kategori: "Sedang" },
  { id: 2, sensor_id: "S002", berat: "58.1", deep_klaster: 2, kategori: "Sedang" },
  { id: 3, sensor_id: "S003", berat: "61.7", deep_klaster: 2, kategori: "Besar" },
  { id: 4, sensor_id: "S004", berat: "48.9", deep_klaster: 0, kategori: "Kecil" },
  { id: 5, sensor_id: "S005", berat: "55.0", deep_klaster: 1, kategori: "Sedang" },
  { id: 6, sensor_id: "S001", berat: "62.3", deep_klaster: 2, kategori: "Besar" },
  { id: 7, sensor_id: "S002", berat: "53.1", deep_klaster: 1, kategori: "Sedang" },
  { id: 8, sensor_id: "S003", berat: "46.2", deep_klaster: 0, kategori: "Kecil" },
  { id: 9, sensor_id: "S004", berat: "49.2", deep_klaster: 0, kategori: "Kecil" },
  { id: 10, sensor_id: "S005", berat: "65.0", deep_klaster: 2, kategori: "Besar" },
]);

// Sample data for logs
const logData = ref([
  { id: 1, time: "12.30 WIB", action: "Admin Login" },
  { id: 2, time: "12.30 WIB", action: "Admin Login" },
  { id: 3, time: "12.30 WIB", action: "Admin Login" },
  { id: 4, time: "12.30 WIB", action: "Admin Login" },
]);

// Sample data for tabel device logs
// Sample data for device logs
const logPerangkatData = ref([
  {
    id: 1,
    waktu: '28 Mei 2025, 10:30',
    perangkat: 'Load Cell',
    status: '✅ Aktif',
    catatan: 'Normal'
  },
  {
    id: 2,
    waktu: '28 Mei 2025, 10:00',
    perangkat: 'Load Cell',
    status: '❌ Nonaktif',
    catatan: 'Manual shutdown'
  },
  {
    id: 3,
    waktu: '28 Mei 2025, 9:45',
    perangkat: 'Load Cell',
    status: '✅ Aktif',
    catatan: 'Normal'
  },
  {
    id: 4,
    waktu: '28 Mei 2025, 9:00',
    perangkat: 'Load Cell',
    status: '❌ Nonaktif',
    catatan: 'Daya habis'
  },
  {
    id: 5,
    waktu: '28 Mei 2025, 8:30',
    perangkat: 'Load Cell',
    status: '✅ Aktif',
    catatan: 'Normal'
  },
  {
    id: 6,
    waktu: '28 Mei 2025, 8:00',
    perangkat: 'Load Cell',
    status: '❌ Nonaktif',
    catatan: 'Maintenance'
  }
])

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
  <AppLayout>
    <transition name="fade-up" appear>
      <div class="p-16 min-h-screen">
        <!-- Judul Halaman -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Dashboard</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

        <h1 class="text-xl font-bold">Selamat Datang Admin!</h1>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
          <!-- Card 1 -->
          <div
            class="group w-full h-[180px] bg-white rounded-[15px] border border-gray-300 cursor-pointer border-opacity-30 p-5 relative
            transition-all duration-300 hover:border-blue-600 hover:shadow-xl"
          >
            <NotebookPen class="w-8 h-9 mb-3 transition-colors duration-300 text-blue-600" />
            <div class="text-lg font-bold mb-1 font-poppins transition-colors duration-300 text-gray-800">
              Jumlah Produksi Telur
            </div>
            <div class="text-base font-bold mb-1 font-poppins transition-colors duration-300 text-gray-800">
              30
            </div>
            <div class="text-base font-bold font-poppins transition-colors duration-300 group-hover:text-gray-800 text-gray-500">
              Butir Hari Ini
            </div>
          </div>
          <!-- Card 2 -->
          <div
            class="group w-full h-[180px] bg-white rounded-[15px] border border-gray-300 cursor-pointer border-opacity-30 p-5 relative
              transition-all duration-300 hover:border-blue-600 hover:shadow-xl"
          >
            <TrendingUp class="w-8 h-9 mb-3 transition-colors duration-300 text-blue-600" />
            
            <div class="text-lg font-bold mb-1 font-poppins transition-colors duration-300  text-gray-800">
              Harga Telur Hari Ini
            </div>

            <div class="text-base font-bold mb-1 font-poppins transition-colors duration-300 text-blue-700">
              Rp {{ props.hargaHariIni?.harga?.toLocaleString('id-ID') ?? 'Belum ada data' }}
            </div>

            <div
              class="text-base font-bold font-poppins transition-colors duration-300 group-hover:text-gray-800 text-gray-500"
            >
              {{
                props.persentaseKenaikan === null
                  ? 'Belum ada data perbandingan'
                  : props.persentaseKenaikan > 0
                    ? `Naik ${props.persentaseKenaikan}% dari kemarin`
                    : props.persentaseKenaikan < 0
                      ? `Turun ${Math.abs(props.persentaseKenaikan)}% dari kemarin`
                      : 'Stabil dibanding kemarin'
              }}
            </div>
          </div>
          <!-- Card 3 -->
          <div
            class="group w-full h-[180px] bg-white rounded-[15px] border border-gray-300 cursor-pointer border-opacity-30 p-5 relative
              transition-all duration-300 hover:border-blue-600 hover:shadow-xl"
          >
            <Heart class="w-7 h-8 mb-3 transition-colors duration-300 text-blue-600" />
            <div class="text-lg font-bold mb-1 font-poppins transition-colors duration-300 text-gray-800">
              Status Perangkat
            </div>
            <div class="text-base font-bold mb-1 font-poppins transition-colors duration-300"
                :class="props.statusPerangkat.toLowerCase() === 'on' ? '!text-green-600' : '!text-red-600'">
                {{ props.statusPerangkat.toUpperCase() }}
            </div>
            <div class="text-base font-bold font-poppins transition-colors duration-300 text-gray-500">
              Saat Ini
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-10 gap-8 mb-16">
          <!-- Pie Chart: 40% (4/10) -->
          <div class="md:col-span-4 h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-6 flex flex-col">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Hasil Klasterisasi Telur</h3>
              <svg class="w-1 h-4 text-gray-800" fill="currentColor" viewBox="0 0 4 16">
                <circle cx="2" cy="2" r="2"></circle>
                <circle cx="2" cy="8" r="2"></circle>
                <circle cx="2" cy="14" r="2"></circle>
              </svg>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center">
              <div class="w-48 h-48 mb-4">
                <ChartPie :chartData="pieData" :chartOptions="pieOptions" />
              </div>
            </div>
            <!-- Legend custom di bawah kanan -->
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
          <!-- Line Chart: 60% (6/10) -->
          <div class="md:col-span-6 h-80 bg-white rounded-[15px] shadow-lg relative p-6 overflow-auto flex flex-col">
            <div class="flex justify-between items-center p-4">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Grafik Produksi Harian Telur</h3>
              <svg class="w-1 h-4 text-gray-800" fill="currentColor" viewBox="0 0 4 16">
                <circle cx="2" cy="2" r="2"></circle>
                <circle cx="2" cy="8" r="2"></circle>
                <circle cx="2" cy="14" r="2"></circle>
              </svg>
            </div>
            <div class="flex-1 flex items-center justify-center">
              <div class="w-full h-64">
                <ChartLine :chartData="chartData" :chartOptions="chartOptions" />
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Section -->
        <!-- Bagian Tabel Klasterisasi (Full Konten) -->
        <div class="bg-white rounded-[15px] shadow-lg relative w-full mb-16">
          <div class="flex justify-between items-center p-4">
            <h3 class="text-xl font-semibold text-gray-800 font-poppins">Tabel Klasterisasi Telur</h3>
            <svg class="w-1 h-4 text-gray-800" fill="currentColor" viewBox="0 0 4 16">
              <circle cx="2" cy="2" r="2"></circle>
              <circle cx="2" cy="8" r="2"></circle>
              <circle cx="2" cy="14" r="2"></circle>
            </svg>
          </div>
          <div class="mx-8 mb-8 border border-blue-600 rounded-lg overflow-hidden">
            <!-- Table Header -->
            <div class="flex bg-blue-300 border-b border-blue-600">
              <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">id</div>
              <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">sensor_id</div>
              <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">berat</div>
              <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">deep_klaster</div>
              <div class="flex-1 p-3 text-xs font-bold text-black font-inter">kategori</div>
            </div>
            <!-- Table Rows -->
            <div v-for="row in tableData" :key="row.id" class="flex border-b border-blue-600">
              <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ row.id }}</div>
              <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ row.sensor_id }}</div>
              <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ row.berat }}</div>
              <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ row.deep_klaster }}</div>
              <div class="flex-1 p-3 text-xs text-black font-inter">{{ row.kategori }}</div>
            </div>
          </div>
        </div>

        <!-- Bagian Log Perangkat & Log Notifikasi (60:40) -->
        <div class="grid grid-cols-1 md:grid-cols-10 gap-8 mb-16">
          <!-- Tabel Log Perangkat: 60% -->
          <div class="md:col-span-6 bg-white rounded-[15px] shadow-lg h-[550px] relative w-full p-6 overflow-auto">
            <div class="flex justify-between items-center p-4">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Tabel Log Perangkat</h3>
              <svg class="w-1 h-4 text-gray-800" fill="currentColor" viewBox="0 0 4 16">
                <circle cx="2" cy="2" r="2"></circle>
                <circle cx="2" cy="8" r="2"></circle>
                <circle cx="2" cy="14" r="2"></circle>
              </svg>
            </div>
            <div class="mx-4 mb-4 border border-blue-600 rounded-lg overflow-hidden">
              <!-- Table Header -->
              <div class="flex bg-blue-200 border-b border-blue-600">
                <div class="w-12 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">ID</div>
                <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">Perangkat</div>
                <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">Status</div>
                <div class="flex-1 p-3 border-r border-gray-400 text-xs font-bold text-black font-inter">Catatan</div>
                <div class="flex-1 p-3 text-xs font-bold text-black font-inter">Waktu</div>
              </div>
              <!-- Table Rows -->
              <div v-for="log in logPerangkatData" :key="log.id" class="flex border-b border-blue-600">
                <div class="w-12 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ log.id }}</div>
                <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ log.perangkat }}</div>
                <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ log.status }}</div>
                <div class="flex-1 p-3 border-r border-gray-400 text-xs text-black font-inter">{{ log.catatan }}</div>
                <div class="flex-1 p-3 text-xs text-black font-inter">{{ log.waktu }}</div>
              </div>
            </div>
          </div>
          <!-- Log Notifikasi: 40% -->
          <div class="md:col-span-4 bg-white rounded-[15px] shadow-lg h-[550px] relative w-full p-6 overflow-auto">
            <div class="flex justify-between items-center p-4">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Log Notifikasi</h3>
              <svg class="w-1 h-4 text-gray-800" fill="currentColor" viewBox="0 0 4 16">
                <circle cx="2" cy="2" r="2"></circle>
                <circle cx="2" cy="8" r="2"></circle>
                <circle cx="2" cy="14" r="2"></circle>
              </svg>
            </div>
            <div class="px-6 py-5">
              <div class="relative">
                <!-- Timeline line -->
                <div class="absolute left-3 top-0 w-0.5 h-full bg-black"></div>
                <!-- Timeline items -->
                <div v-for="log in logData" :key="log.id" class="relative mb-14">
                  <div class="absolute left-0 w-6 h-6 bg-white rounded-full border-2 border-blue-600">
                    <div class="w-2.5 h-2.5 bg-blue-600 rounded-full mx-auto mt-1"></div>
                  </div>
                  <div class="ml-20">
                    <div class="text-xs font-bold text-gray-500 mb-1.5 font-poppins">{{ log.time }}</div>
                    <div class="text-sm font-bold text-gray-800 font-poppins">{{ log.action }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<style scoped>
.font-poppins {
  font-family: "Poppins", -apple-system, Roboto, Helvetica, sans-serif;
}
.font-inter {
  font-family: "Inter", -apple-system, Roboto, Helvetica, sans-serif;
}

.fade-up-enter-active, .fade-up-appear-active {
  transition: opacity 0.6s cubic-bezier(.4,0,.2,1), transform 0.6s cubic-bezier(.4,0,.2,1);
}
.fade-up-enter-from, .fade-up-appear-from {
  opacity: 0;
  transform: translateY(40px);
}
.fade-up-enter-to, .fade-up-appear-to {
  opacity: 1;
  transform: translateY(0);
}
</style>

