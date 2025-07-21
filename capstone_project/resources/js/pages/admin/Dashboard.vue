<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import ChartLine from '@/components/ChartLine.vue'
import ChartPie from '@/components/ChartPie.vue'
import Table from '@/components/Table.vue'
import type { ChartData, ChartOptions } from 'chart.js'
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';
import { NotebookPen, TrendingUp, Heart, RefreshCcw, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3' 
import dayjs from 'dayjs';

interface HargaTelur {
  id: number
  tanggal: string
  harga: number
  keterangan: string
}

// Helper for label mapping
type LabelTampilanType = 'Kecil Mutu Tinggi' | 'Mutu Rendah' | 'Besar Mutu Tinggi'
const labelMap: Record<0 | 1 | 2, LabelTampilanType> = {
  0: 'Kecil Mutu Tinggi',
  1: 'Mutu Rendah',
  2: 'Besar Mutu Tinggi'
}

const props = defineProps<{
  jumlahTelurHariIni: Number,
  statusPerangkat: string
  hargaHariIni: HargaTelur | null
  persentaseKenaikan: number | null
  dataKlaster: Array<{
    no: number;
    berat: number;
    intensitas: number;
    klaster: number;
    ukuran: string;
    kualitas: string;
    label: string;
  }>
  logPerangkatData: Array<{
  waktu: string;
  perangkat: string;
  status: string;
  catatan: string;
  }>  // ...props lain
}>()

const pieData = ref<ChartData<'pie'> | null>(null)
const lineData = ref<ChartData<'line'> | null>(null) // Initialize with null
const lineOptions = ref<ChartOptions<'line'> | null>(null) // Initialize with null

const selectedFilterBar = ref('week')
const selectedFilterPie = ref('week')
const selectedFilterLine = ref('week')

const kenaikanLabel = computed(() => {
  if (props.persentaseKenaikan === null) return 'Belum ada data perbandingan'
  const sign = props.persentaseKenaikan > 0 ? '+' : ''
  return `${sign}${props.persentaseKenaikan}% dari kemarin`
})

const kenaikanClass = computed(() => {
  if (props.persentaseKenaikan === null || props.persentaseKenaikan === 0) return 'text-gray-500'
  return props.persentaseKenaikan > 0 ? 'text-green-600' : 'text-red-600'
})

const isInFilterRange = (tanggal: dayjs.Dayjs, filter: string): boolean => {
  const now = dayjs()

  switch (filter) {
    case 'today':
      return tanggal.isSame(now, 'day')
    case 'yesterday':
      return tanggal.isSame(now.subtract(1, 'day'), 'day')
    case 'week':
      return tanggal.isAfter(now.subtract(6, 'day'), 'day')
    case 'month':
      return tanggal.isAfter(now.subtract(1, 'month'), 'day')
    default:
      return true
  }
}

const fetchKlasterData = async () => {
  try {
    const response = await axios.get('/api/sensor/klaster')
    const klaster = response.data as { klaster: number; waktu_masuk: string }[]

    // --- Pie Chart ---
    const count: Record<LabelTampilanType, number> = {
      'Kecil Mutu Tinggi': 0,
      'Mutu Rendah': 0,
      'Besar Mutu Tinggi': 0
    }

    klaster.forEach(item => {
      const tanggal = dayjs(item.waktu_masuk)
      if (!tanggal.isSame(dayjs(), 'day')) return // ⬅️ hanya ambil yang tanggalnya hari ini

      const label = labelMap[item.klaster as 0 | 1 | 2]
      if (label) count[label]++
    })

    pieData.value = {
      labels: ['Besar Mutu Tinggi', 'Kecil Mutu Tinggi', 'Mutu Rendah'],
      datasets: [
        {
          data: [
            count['Besar Mutu Tinggi'],
            count['Kecil Mutu Tinggi'],
            count['Mutu Rendah']
          ],
          backgroundColor: ['#2563EB', '#60A5FA', '#F87171']
        }
      ]
    }

    // --- Line Chart Produksi ---
    const grouped: Record<string, Record<string, number>> = {}
    klaster.forEach(item => {
      const tanggal = dayjs(item.waktu_masuk)
      if (!isInFilterRange(tanggal, selectedFilterLine.value)) return

      const dateStr = tanggal.format('DD-MM')
      const label = labelMap[item.klaster as 0 | 1 | 2]
      if (!grouped[dateStr]) {
        grouped[dateStr] = { 'Besar Mutu Tinggi': 0, 'Kecil Mutu Tinggi': 0, 'Mutu Rendah': 0 }
      }
      grouped[dateStr][label]++
    })
    const sortedTanggal = Object.keys(grouped).sort()
    lineData.value = {
      labels: sortedTanggal,
      datasets: [
        { label: 'Besar Mutu Tinggi', data: sortedTanggal.map(tgl => grouped[tgl]['Besar Mutu Tinggi']), borderColor: '#2563EB', backgroundColor: '#2563EB', tension: 0.4, fill: false },
        { label: 'Kecil Mutu Tinggi', data: sortedTanggal.map(tgl => grouped[tgl]['Kecil Mutu Tinggi']), borderColor: '#60A5FA', backgroundColor: '#60A5FA', tension: 0.4, fill: false },
        { label: 'Mutu Rendah', data: sortedTanggal.map(tgl => grouped[tgl]['Mutu Rendah']), borderColor: '#F87171', backgroundColor: '#F87171', tension: 0.4, fill: false }
      ]
    }
    lineOptions.value = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' },
      },
      scales: {
        x: {},
        y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
      }
    }
  } catch (error) {
    console.error('Error fetching klaster data:', error)
  }
}


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

// Sample data for logs
const logData = ref([
  { id: 1, time: "12.30 WIB", action: "Admin Login" },
  { id: 2, time: "12.30 WIB", action: "Admin Login" },
  { id: 3, time: "12.30 WIB", action: "Admin Login" },
  { id: 4, time: "12.30 WIB", action: "Admin Login" },
]);



const pieOptions: ChartOptions<'pie'> = {
  responsive: true,
  maintainAspectRatio: false, // biar tidak kekecilan di layout flex
  plugins: {
    legend: {
      position: 'bottom',
      align: 'center', // tengah (default-nya "center", tapi bisa eksplisit)
      labels: {
        boxWidth: 14, // ukuran kotak warna
        padding: 20,  // jarak antar legend item
        font: {
          size: 13,
        },
        color: '#374151', // abu gelap untuk keterbacaan
      }
    },
    title: {
      display: false
    },
    tooltip: {
      bodyFont: {
        family: 'Inter',
        size: 12
      }
    }
  },
  layout: {
    padding: {
      top: 10,
      bottom: 10
    }
  }
}


// Menu state

const showMenu = ref<{
  pie: boolean;
  line: boolean;
  logPerangkat: boolean;
  table: boolean;
  logNotif: boolean;
}>({
  pie: false,
  line: false,
  table: false,
  logPerangkat: false,
  logNotif: false,
});

// Ambil key-nya langsung dari showMenu biar aman
type ChartType = keyof typeof showMenu.value;

function downloadChartAsPng(type: ChartType) {
  alert(`Unduh chart ${type} sebagai PNG`);
  showMenu.value[type] = false;
}

function refreshData(type: ChartType) {
  alert(`Perbarui data ${type}`);
  showMenu.value[type] = false;
}

function showAllData(type: ChartType) {
  showMenu.value[type] = false;

  if (type === 'table') {
    router.visit('/admin/klaster');
    return;
  }
  else if (type === 'logPerangkat') {
    router.visit('/admin/perangkat');
    return;
  }

  alert(`Tampilkan semua data ${type}`);
}

// Untuk klik di luar dropdown
function handleClickOutside(event: MouseEvent) {
  const menus = document.querySelectorAll('.more-action-dropdown');
  menus.forEach(menu => {
    if (!menu.contains(event.target as Node)) {
      showMenu.value = { pie: false, line: false, table: false, logPerangkat: false, logNotif: false };
    }
  });
}
onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleClickOutside));

onMounted(() => {
  fetchKlasterData()
})

</script>

<template>
  <AppLayout>
    <transition name="fade-up" appear>
      <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
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
              {{ jumlahTelurHariIni }}
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
            class="group w-full h-[180px] bg-white rounded-[15px] border border-gray-300 border-opacity-30 p-5 relative
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
        <div class="grid grid-cols-10 gap-8 mb-20">
          <!-- Pie Chart: 40% (4/10) -->
          <div class="md:col-span-4 h-[420px] bg-white rounded-[15px] border border-gray-300 border-opacity-30 p-5 relative
              transition-all duration-300 hover:border-blue-600 hover:shadow-xl flex flex-col">
            <div class="flex justify-between items-center mb-2">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Jumlah Produksi Telur Hari Ini</h3>
              <div class="relative">
                <button @click="showMenu.pie = !showMenu.pie" class="focus:outline-none transition hover:bg-blue-100 rounded-full p-1 cursor-pointer">
                  <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="4" r="2"></circle>
                    <circle cx="10" cy="10" r="2"></circle>
                    <circle cx="10" cy="16" r="2"></circle>
                  </svg>
                </button>
                <transition name="fade-scale">
                  <div
                    v-if="showMenu.pie"
                    class="more-action-dropdown absolute right-0 mt-2 w-52 bg-white border border-blue-200 rounded-xl shadow-xl z-50 py-2 animate-dropdown"
                  >
                    <button @click="refreshData('table')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                      <RefreshCcw class="w-4 h-4 text-blue-800" /> Perbarui Data
                    </button>
                    <button @click="showAllData('table')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                      <FileText class="w-4 h-4 text-blue-800" /> Tampilkan Semua Data
                    </button>
                  </div>
                </transition>
              </div>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center">
              <div class="w-full aspect-square max-h-[85%] mx-auto">
                <ChartPie
                  v-if="pieData"
                  :chartData="pieData"
                  :chartOptions="pieOptions"
                  class="w-full h-full"
                />
                <p v-else class="text-sm text-gray-400 font-inter text-center">
                  Memuat data pie chart...
                </p>
              </div>
            </div>
          </div>
          <!-- Line Chart: 60% (6/10) -->
          <div class="md:col-span-6 h-[420px] bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-5 overflow-auto transition-all duration-300 hover:border-blue-600 hover:shadow-xl flex flex-col">
            <div class="flex justify-between items-center p-4">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Grafik Produksi Harian Telur</h3>
              <div class="relative">
                <button @click="showMenu.line = !showMenu.line" class="focus:outline-none transition hover:bg-blue-100 rounded-full p-1 cursor-pointer ">
                  <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="4" r="2"></circle>
                    <circle cx="10" cy="10" r="2"></circle>
                    <circle cx="10" cy="16" r="2"></circle>
                  </svg>
                </button>
                <transition name="fade-scale">
                  <div
                    v-if="showMenu.line"
                    class="more-action-dropdown absolute right-0 mt-2 w-52 bg-white border border-blue-200 rounded-xl shadow-xl z-50 py-2 animate-dropdown"
                  >
                    <button @click="refreshData('table')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                      <RefreshCcw class="w-4 h-4 text-blue-800" /> Perbarui Data
                    </button>
                    <button @click="showAllData('table')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                      <FileText class="w-4 h-4 text-blue-800" /> Tampilkan Semua Data
                    </button>
                  </div>
                </transition>
              </div>
            </div>
            <div class="flex-1 flex items-center justify-center">
              <div class="w-full h-64 md:h-80 lg:h-64">
                <ChartLine
                  v-if="lineData && lineOptions"
                  :chartData="lineData"
                  :chartOptions="lineOptions"
                  class="w-full"
                />
                <p v-else class="text-sm text-gray-400 font-inter">Memuat data grafik produksi...</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bagian Tabel Klasterisasi (Full Konten) -->
        <div class="bg-white rounded-[15px] border border-gray-300 border-opacity-30 p-5 transition-all duration-300 hover:border-blue-600 hover:shadow-xl gap-8 mb-20">
          <div class="flex justify-between items-center mb-4">
            <div>
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Tabel Klasterisasi Telur</h3>
              <span class="text-sm text-gray-500 italic">Menampilkan 5 data terakhir hari ini</span>
            </div>
            <div class="relative">
              <button @click="showMenu.table = !showMenu.table" class="focus:outline-none transition hover:bg-blue-100 rounded-full p-1 cursor-pointer">
                <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                  <circle cx="10" cy="4" r="2"></circle>
                  <circle cx="10" cy="10" r="2"></circle>
                  <circle cx="10" cy="16" r="2"></circle>
                </svg>
              </button>
              <transition name="fade-scale">
                <div
                  v-if="showMenu.table"
                  class="more-action-dropdown absolute right-0 mt-2 w-52 bg-white border border-blue-200 rounded-xl shadow-xl z-50 py-2 animate-dropdown"
                >
                  <button @click="refreshData('table')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                    <RefreshCcw class="w-4 h-4 text-blue-800" /> Perbarui Data
                  </button>
                  <button @click="showAllData('table')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                    <FileText class="w-4 h-4 text-blue-800" /> Tampilkan Semua Data
                  </button>
                </div>
              </transition>
            </div>
          </div>
          <div class="mt-4">
            <div v-if="props.dataKlaster.length > 0">
              <Table
                :columns="[
                  { label: 'Berat (gr)', key: 'berat' },
                  { label: 'Intensitas', key: 'intensitas' },
                  { label: 'Kode Klaster', key: 'klaster' },
                  { label: 'Ukuran', key: 'ukuran' },
                  { label: 'Kualitas', key: 'kualitas' },
                  { label: 'Label Tampilan', key: 'label' }
                ]"
                :rows="props.dataKlaster"
              />
            </div>
            <div v-else class="p-4 text-center text-gray-500 text-sm italic bg-gray-50 border border-gray-200 rounded-lg">
              Data klasterisasi untuk hari ini belum tersedia.
            </div>
          </div>
        </div>

        <!-- Bagian Log Perangkat & Log Notifikasi (60:40) -->
        <div class="grid grid-cols-1 md:grid-cols-10 gap-8 mb-16">
          <!-- Tabel Log Perangkat: 60% -->
          <div class="md:col-span-6 bg-white rounded-[15px] shadow-lg h-[550px] relative w-full p-6 overflow-auto">
            <div class="flex justify-between items-center p-4">
              <h3 class="text-xl font-semibold text-gray-800 font-poppins">Tabel Log Perangkat</h3>
              <div class="relative">
                <button @click="showMenu.logPerangkat = !showMenu.logPerangkat" class="focus:outline-none transition hover:bg-blue-100 rounded-full p-1 cursor-pointer">
                  <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="4" r="2"></circle>
                    <circle cx="10" cy="10" r="2"></circle>
                    <circle cx="10" cy="16" r="2"></circle>
                  </svg>
                </button>
                <transition name="fade-scale">
                  <div
                    v-if="showMenu.logPerangkat"
                    class="more-action-dropdown absolute right-0 mt-2 w-52 bg-white border border-blue-200 rounded-xl shadow-xl z-50 py-2 animate-dropdown"
                  >
                    <button @click="refreshData('logPerangkat')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                      <RefreshCcw class="w-4 h-4 text-blue-800" /> Perbarui Data
                    </button>
                    <button @click="showAllData('logPerangkat')" class="flex items-center gap-2 w-full text-left px-4 py-2 text-gray-800 hover:bg-blue-50 transition">
                      <FileText class="w-4 h-4 text-blue-800" /> Tampilkan Semua Data
                    </button>
                  </div>
                </transition>
              </div>
            </div>
            <div class="mx-4 mb-4 border border-blue-600 rounded-lg overflow-hidden">
              <!-- Table Header -->
              <Table
              :columns="[
                { label: 'Waktu', key: 'waktu' },
                { label: 'Perangkat', key: 'perangkat' },
                { label: 'Status', key: 'status' },
                { label: 'Catatan', key: 'catatan' }
              ]"
              :rows="props.logPerangkatData"
              />
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

.fade-scale-enter-active, .fade-scale-leave-active {
  transition: opacity 0.18s cubic-bezier(.4,0,.2,1), transform 0.18s cubic-bezier(.4,0,.2,1);
}
.fade-scale-enter-from, .fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
.fade-scale-enter-to, .fade-scale-leave-from {
  opacity: 1;
  transform: scale(1);
}
.animate-dropdown {
  animation: dropdown-fade 0.18s cubic-bezier(.4,0,.2,1);
}
@keyframes dropdown-fade {
  from { opacity: 0; transform: scale(0.95);}
  to { opacity: 1; transform: scale(1);}
}
</style>

