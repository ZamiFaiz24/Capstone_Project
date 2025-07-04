<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import ChartBar from '@/components/ChartBar.vue'
import ChartPie from '@/components/ChartPie.vue'
import ChartLine from '@/components/ChartLine.vue'
import DropdownFilter from '@/components/DropdownFilter.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// Data harga telur dari API
const dataHarga = ref<{ tanggal: string; harga: number }[]>([])
const bulan = ref('')
const tahun = ref('')
const bulan2 = ref('')
const tahun2 = ref('')
const dataPrediksi = ref<{ tanggal: string; harga: number }[]>([])
const prediksiDays = ref('7') // default: 7 hari

// Pilihan bulan (1–12)
const bulanOptions = [
  { label: 'Semua Bulan', value: '' },
  { label: 'Januari', value: '1' },
  { label: 'Februari', value: '2' },
  { label: 'Maret', value: '3' },
  { label: 'April', value: '4' },
  { label: 'Mei', value: '5' },
  { label: 'Juni', value: '6' },
  { label: 'Juli', value: '7' },
  { label: 'Agustus', value: '8' },
  { label: 'September', value: '9' },
  { label: 'Oktober', value: '10' },
  { label: 'November', value: '11' },
  { label: 'Desember', value: '12' },
]

// Tahun bisa kamu sesuaikan dari data atau tetap hardcoded
const tahunOptions = [
  { label: 'Semua Tahun', value: '' },
  { label: '2024', value: '2024' },
  { label: '2025', value: '2025' },
]

onMounted(async () => {
  const response = await fetch('/admin/data-harga')
  const result = await response.json()
  dataHarga.value = result
})

// Data hasil filter berdasarkan bulan & tahun
const dataHargaFiltered = computed(() => {
  return dataHarga.value.filter(item => {
    const date = new Date(item.tanggal)
    const m = (date.getMonth() + 1).toString()
    const y = date.getFullYear().toString()

    return (!bulan.value || bulan.value === m) && (!tahun.value || tahun.value === y)
  })
})

// Grafik harga telur dengan format label tanggal
const dataHargaTelur = computed(() => {
  if (dataHargaFiltered.value.length === 0) {
    return {
      labels: [],
      datasets: [],
    }
  }

  return {
    labels: dataHargaFiltered.value.map(item => {
      const date = new Date(item.tanggal)
      return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
      })
    }),
    datasets: [
      {
        label: 'Harga Telur (Rp)',
        data: dataHargaFiltered.value.map(item => item.harga),
        fill: false,
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.15)',
        tension: 0.4,
        pointBackgroundColor: '#3B82F6',
        pointRadius: 4,
      },
    ],
  }
})

const chartHagraOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        font: { family: 'Inter', size: 12 },
      },
    },
  },
  scales: {
    x: {
      ticks: {
        font: { family: 'Inter', size: 12 },
        maxRotation: 45,
        minRotation: 30,
        autoSkip: true,
        maxTicksLimit: 10,
      },
    },
    y: {
      beginAtZero: false,
      ticks: {
        font: { family: 'Inter', size: 12 },
      },
    },
  },
}

// Dummy Chart Data
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

const areaChartData = computed(() => {
  // Filter dataHarga sesuai bulan2 & tahun2
  const filtered = dataHarga.value.filter(item => {
    const date = new Date(item.tanggal)
    const m = (date.getMonth() + 1).toString()
    const y = date.getFullYear().toString()
    return (!bulan2.value || bulan2.value === m) && (!tahun2.value || tahun2.value === y)
  })

  const labels = filtered.map(item => {
    const date = new Date(item.tanggal)
    return `${String(date.getDate()).padStart(2, '0')} ${date.toLocaleString('default', { month: 'short' })}`
  })

  const harga = filtered.map(item => item.harga)
  const rataRata = harga.length
  ? Array(harga.length).fill(parseFloat((harga.reduce((a, b) => a + b, 0) / harga.length).toFixed(2)))
  : []

  return {
    labels,
    datasets: [
      {
        label: 'Harga Telur (Rp)',
        data: harga,
        fill: true,
        backgroundColor: 'rgba(59, 130, 246, 0.15)',
        borderColor: '#3B82F6',
        tension: 0.4,
        pointRadius: 3,
      },
      {
        label: 'Rata-rata',
        data: rataRata,
        fill: false,
        borderColor: '#EF4444',
        borderDash: [5, 5],
        tension: 0.1,
        pointRadius: 0,
      },
    ]
  }
})

const areaChartOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        font: { family: 'Inter', size: 12 },
      },
    },
    tooltip: {
      callbacks: {
        label: (context: any) => {
          const label = context.dataset.label || ''
          const value = context.formattedValue
          if (label === 'Harga Telur (Rp)') {
            return `Harga: Rp ${value}`
          } else if (label === 'Rata-rata') {
            return `Rata-rata: Rp ${value}`
          }
          return `${label}: Rp ${value}`
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: false,
      min: 23000,
      max: 30000,
      ticks: {
        font: { family: 'Inter', size: 12 },
      },
    },
    x: {
      ticks: {
        font: { family: 'Inter', size: 11 },
        maxRotation: 45,
        minRotation: 30,
        autoSkip: true,
        maxTicksLimit: 10,
      }
    }
  }
}

const fetchPrediksi = async () => {
  const res = await fetch(`/api/prediksi-harga?days=${prediksiDays.value}`)
  const json = await res.json()
  console.log('Prediksi:', json)
  if (json.status === 'success') {
    dataPrediksi.value = json.data
  }
}

onMounted(async () => {
  const response = await fetch('/api/data-harga')
  const result = await response.json()
  dataHarga.value = result

  await fetchPrediksi() // Tambahkan ini
})

watch(prediksiDays, () => {
  fetchPrediksi()
})


const chartPrediksiData = computed(() => {
  return {
    labels: dataPrediksi.value.map(item => {
      const date = new Date(item.tanggal)
      return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short'
      })
    }),
    datasets: [
      {
        label: 'Prediksi Harga (Rp)',
        data: dataPrediksi.value.map(item => item.harga), // <-- sebelumnya: item.prediksi
        borderColor: '#10B981',
        backgroundColor: 'rgba(16,185,129,0.15)',
        tension: 0.4,
        fill: true,
        pointRadius: 3,
      }
    ]
  }
})


const chartPrediksiOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        font: { family: 'Inter', size: 12 },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: false,
      ticks: {
        font: { family: 'Inter', size: 12 },
      }
    },
    x: {
      ticks: {
        font: { family: 'Inter', size: 11 },
        maxRotation: 45,
        minRotation: 30,
        autoSkip: true,
        maxTicksLimit: 10,
      }
    }
  }
}
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Visualisasi Data</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-6"></div>

      <!-- Chart Bar & Pie -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <ChartBar :chartData="chartData" />

        <!-- Chart Pie -->
        <div class="h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-6 flex flex-col">
          <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-2">Presentase Jumlah Setiap Klaster</h2>
          <div class="flex-1 flex flex-col items-center justify-center">
            <div class="w-48 h-48 mb-4">
              <ChartPie :chartData="pieData" :chartOptions="pieOptions" />
            </div>
          </div>
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

      <!-- Dummy Line Chart -->
      <div class="h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-6 flex flex-col mb-8">
        <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-2">Tren Produksi Telur Mingguan</h2>
        <ChartLine :chartData="lineData" :chartOptions="lineOptions" class="flex-1" />
      </div>

      <!-- Grafik Harga Telur -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        <!-- Grafik Harga Telur -->
        <div class="h-100 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-1">Grafik Harga Telur</h2>
              <p class="text-xs text-gray-400 font-inter">Periode berdasarkan filter bulan & tahun</p>
            </div>
            <div class="flex gap-2 items-center">
              <DropdownFilter
                :options="bulanOptions"
                v-model="bulan"
                class="min-w-[120px]"
              />
              <DropdownFilter
                :options="tahunOptions"
                v-model="tahun"
                class="min-w-[100px]"
              />
            </div>
          </div>
          <div class="flex-1 flex items-center justify-center overflow-x-auto">
            <ChartLine
              v-if="dataHargaTelur.datasets.length"
              :chartData="dataHargaTelur"
              :chartOptions="chartHagraOptions"
              class="w-full"
            />
            <p v-else class="text-sm text-gray-400 font-inter">Memuat data harga telur...</p>
          </div>
        </div>

        <!-- Area Chart Harga vs Rata-rata -->
        <div class="h-100 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-1">Harga Telur vs Rata-rata</h2>
              <p class="text-xs text-gray-400 font-inter">Periode berdasarkan filter bulan & tahun</p>
            </div>
            <div class="flex gap-2 items-center">
              <DropdownFilter
                :options="bulanOptions"
                v-model="bulan2"
                class="min-w-[120px]"
              />
              <DropdownFilter
                :options="tahunOptions"
                v-model="tahun2"
                class="min-w-[100px]"
              />
            </div>
          </div>
          <div class="flex-1 flex items-center justify-center overflow-x-auto">
            <ChartLine
              v-if="areaChartData.datasets.length"
              :chartData="areaChartData"
              :chartOptions="areaChartOptions"
              class="w-full"
            />
            <p v-else class="text-sm text-gray-400 font-inter">Memuat data grafik area...</p>
          </div>
        </div>
        <!-- Grafik Prediksi Harga Telur -->
        <div class="h-100 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col mb-8">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-1">Prediksi Harga Telur</h2>
              <p class="text-xs text-gray-400 font-inter">Prediksi otomatis berdasarkan model Prophet</p>
            </div>
            <div class="flex gap-2 items-center">
              <select v-model="prediksiDays" class="border border-blue-500 text-blue-500 rounded-md px-2 py-1 text-sm">
                <option value="7">7 Hari</option>
                <option value="14">14 Hari</option>
                <option value="30">30 Hari</option>
              </select>
            </div>
          </div>

          <div class="flex-1 flex items-center justify-center overflow-x-auto">
            <ChartLine
              v-if="chartPrediksiData.datasets.length"
              :chartData="chartPrediksiData"
              :chartOptions="chartPrediksiOptions"
              class="w-full"
            />
            <p v-else class="text-sm text-gray-400 font-inter">Memuat data prediksi harga...</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
