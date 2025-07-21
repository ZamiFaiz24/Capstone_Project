<script setup lang="ts">
// ===== IMPORTS =====
import { ref, computed, onMounted, watch } from 'vue'
import { ChevronDown, ChevronUp } from 'lucide-vue-next'
import axios from 'axios'
import dayjs from 'dayjs'
import type { ChartData, ChartOptions } from 'chart.js'
import ChartBar from '@/components/ChartBar.vue'
import ChartPie from '@/components/ChartPie.vue'
import ChartLine from '@/components/ChartLine.vue'
import DropdownFilter from '@/components/DropdownFilter.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { fetchDataAktual, fetchDataPrediksi } from '../../utils/chartHelper'

const bulanGabungan = ref('Juli')
const tahunGabungan = ref('2025')
const prediksiDaysGabungan = ref(7)
const tanggalGabungan = ref<string[]>([])
const dataAktualGabung = ref<number[]>([])
const dataPrediksiGabung = ref<number[]>([])

// ===== STATE: FILTER =====
const bulan = ref('')
const tahun = ref('')
const today = new Date()
const bulan2 = ref(String(today.getMonth() + 1).padStart(2, '0')) // '01' s/d '12'
const tahun2 = ref(String(today.getFullYear()))
const prediksiDays = ref('7')

const currentPage = ref(1);
const selectedFilterBar = ref('week')
const selectedFilterPie = ref('week')
const selectedFilterLine = ref('week')


interface FilterOption {
  value: string;
  label: string;
}
// ===== OPTIONS: FILTER =====
const bulanOptions = [
  { label: 'Semua Bulan', value: '' },
  { label: 'Januari', value: '01' },
  { label: 'Februari', value: '02' },
  { label: 'Maret', value: '03' },
  { label: 'April', value: '04' },
  { label: 'Mei', value: '05' },
  { label: 'Juni', value: '06' },
  { label: 'Juli', value: '07' },
  { label: 'Agustus', value: '08' },
  { label: 'September', value: '09' },
  { label: 'Oktober', value: '10' },
  { label: 'November', value: '11' },
  { label: 'Desember', value: '12' },
]
const tahunOptions = [
  { label: 'Semua Tahun', value: '' },
  { label: '2024', value: '2024' },
  { label: '2025', value: '2025' },
]

const filterOptions: FilterOption[] = [
  { label: 'Hari Ini', value: 'today' },
  { label: 'Kemarin', value: 'yesterday' },
  { label: 'Seminggu', value: 'week' },
  { label: 'Sebulan', value: 'month' },
];

// ===== Filter Klaster =====
const filterKategori = ref('all')

const kategoriOptions = [
  { label: 'Semua Kategori', value: 'all' },
  { label: 'Kecil Mutu Tinggi', value: 'Kecil Mutu Tinggi' },
  { label: 'Besar Mutu Tinggi', value: 'Besar Mutu Tinggi' },
  { label: 'Mutu Rendah', value: 'Mutu Rendah' },
]

// ===== STATE: DATA =====
const dataHarga = ref<{ tanggal: string; harga: number }[]>([])
const dataPrediksi = ref<{ tanggal: string; harga: number }[]>([])
const barChartData = ref<ChartData<'bar'> | null>(null)
const pieData = ref<ChartData<'pie'> | null>(null)
const lineData = ref<ChartData<'line'> | null>(null) // Initialize with null
const lineOptions = ref<ChartOptions<'line'> | null>(null) // Initialize with null
const showInsightProduksi = ref(false)
const showInsightBar = ref(false)
const showInsightPie = ref(false)

// Helper for label mapping
type LabelTampilanType = 'Kecil Mutu Tinggi' | 'Mutu Rendah' | 'Besar Mutu Tinggi'
const labelMap: Record<0 | 1 | 2, LabelTampilanType> = {
  0: 'Kecil Mutu Tinggi',
  1: 'Mutu Rendah',
  2: 'Besar Mutu Tinggi'
}

// ===== FETCH FUNCTIONS =====
const fetchDataHarga = async () => {
  try {
    // Assuming /api/data-harga is the correct endpoint for general price data
    const response = await fetch('/api/data-harga')
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
    dataHarga.value = await response.json()
  } catch (error) {
    console.error('Error fetching data harga:', error)
    // Handle error, e.g., show a message to the user
  }
}

const fetchPrediksi = async () => {
  try {
    const res = await fetch(`/api/prediksi-harga?days=${prediksiDays.value}`)
    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`)
    const json = await res.json()
    if (json.status === 'success') {
      dataPrediksi.value = json.data
    } else {
      console.error('Prediksi API returned an error status:', json.message)
    }
  } catch (error) {
    console.error('Error fetching prediksi data:', error)
  }
}

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

    // --- Bar Chart ---
    const last7Days: string[] = []
    for (let i = 6; i >= 0; i--) last7Days.push(dayjs().subtract(i, 'day').format('DD-MM'))

    const countByDate: Record<LabelTampilanType, number[]> = {
      'Besar Mutu Tinggi': new Array(7).fill(0),
      'Kecil Mutu Tinggi': new Array(7).fill(0),
      'Mutu Rendah': new Array(7).fill(0)
    }
    klaster.forEach(item => {
      const tanggal = dayjs(item.waktu_masuk)
      if (!isInFilterRange(tanggal, selectedFilterBar.value)) return

      const label = labelMap[item.klaster as 0 | 1 | 2]
      const formatted = tanggal.format('DD-MM')
      const index = last7Days.indexOf(formatted)
      if (index !== -1 && label) countByDate[label][index]++
    })
    barChartData.value = {
      labels: last7Days,
      datasets: [
        { label: 'Besar Mutu Tinggi', data: countByDate['Besar Mutu Tinggi'], backgroundColor: '#2563EB' },
        { label: 'Kecil Mutu Tinggi', data: countByDate['Kecil Mutu Tinggi'], backgroundColor: '#60A5FA' },
        { label: 'Mutu Rendah', data: countByDate['Mutu Rendah'], backgroundColor: '#F87171' }
      ]
    }

    // --- Pie Chart ---
    const count: Record<LabelTampilanType, number> = {
      'Kecil Mutu Tinggi': 0,
      'Mutu Rendah': 0,
      'Besar Mutu Tinggi': 0
    }
    klaster.forEach(item => {
      const tanggal = dayjs(item.waktu_masuk)
      if (!isInFilterRange(tanggal, selectedFilterPie.value)) return

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
    const fullDatasets = [
      { label: 'Besar Mutu Tinggi', data: sortedTanggal.map(tgl => grouped[tgl]['Besar Mutu Tinggi']), borderColor: '#2563EB', backgroundColor: '#2563EB', tension: 0.4, fill: false },
      { label: 'Kecil Mutu Tinggi', data: sortedTanggal.map(tgl => grouped[tgl]['Kecil Mutu Tinggi']), borderColor: '#60A5FA', backgroundColor: '#60A5FA', tension: 0.4, fill: false },
      { label: 'Mutu Rendah', data: sortedTanggal.map(tgl => grouped[tgl]['Mutu Rendah']), borderColor: '#F87171', backgroundColor: '#F87171', tension: 0.4, fill: false }
    ]

    const filteredDatasets = filterKategori.value === 'all'
      ? fullDatasets
      : fullDatasets.filter(ds => ds.label === filterKategori.value)

    lineData.value = {
      labels: sortedTanggal,
      datasets: filteredDatasets
    }
    lineOptions.value = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'top', labels: { font: { size: 12 } } },
      },
      scales: {
        x: { ticks: { font: {size: 12 } } },
        y: { beginAtZero: true, ticks: { font: { size: 12 } }, grid: { color: '#f0f0f0' } }
      }
    }
  } catch (error) {
    console.error('Error fetching klaster data:', error)
  }
}

// ===== ON MOUNTED: INITIAL DATA FETCHING =====
onMounted(async () => {
  await fetchDataHarga()
  await fetchPrediksi()
  await fetchKlasterData()
})

// ===== WATCHERS =====
watch(prediksiDays, fetchPrediksi)

watch(selectedFilterBar, fetchKlasterData)
watch(selectedFilterPie, fetchKlasterData)
watch(selectedFilterLine, fetchKlasterData)

watch(filterKategori, fetchKlasterData)


// ===== COMPUTED: FILTERED DATA HARGA TELUR =====
const dataHargaFiltered = computed(() =>
  dataHarga.value.filter(item => {
    const date = new Date(item.tanggal)
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const y = date.getFullYear().toString()
    return (!bulan.value || bulan.value === m) && (!tahun.value || tahun.value === y)
  })
)

// ===== CHART: HARGA TELUR =====
const dataHargaTelur = computed(() => ({
  labels: dataHargaFiltered.value.map(item => {
    const date = new Date(item.tanggal)
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
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
}))
const chartHagraOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: { font: { family: 'Inter', size: 12 } },
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
      ticks: { font: { family: 'Inter', size: 12 } },
    },
  },
}

// INSIGHT CHART
// BarChart
const insightBar = computed(() => {
  const insights: string[] = []
  const data = barChartData.value
  if (!data || !data.labels || !data.datasets) return insights

  const totalPerLabel: Record<string, number> = {}
  let maxHari = ''
  let maxJumlah = 0

  data.labels.forEach((label: any, index: number) => {
    let total = 0
    data.datasets.forEach(ds => {
      const value = (ds.data as number[])[index] || 0
      total += value
    })
    if (total > maxJumlah) {
      maxJumlah = total
      maxHari = label
    }
  })

  data.datasets.forEach(ds => {
    const total = (ds.data as number[]).reduce((a, b) => a + b, 0)
    totalPerLabel[ds.label as string] = total
  })

  const sorted = Object.entries(totalPerLabel).sort((a, b) => b[1] - a[1])
  if (sorted.length) {
    insights.push(`Kategori ${sorted[0][0]} memiliki jumlah produksi terbanyak dengan total ${sorted[0][1]} butir.`)
  }

  if (maxHari) {
    insights.push(`Hari dengan produksi tertinggi adalah ${maxHari} dengan total ${maxJumlah} butir.`)
  }

  return insights
})

// Presentase Piechart

const insightPie = computed(() => {
  const insights: string[] = []
  const data = pieData.value
  if (!data || !data.datasets || !data.datasets.length) return insights

  const values = data.datasets[0].data as number[]
  const labels = data.labels as string[]
  const total = values.reduce((a, b) => a + b, 0)

  const labelValuePairs = labels.map((label, i) => ({
    label,
    value: values[i],
    percent: total > 0 ? ((values[i] / total) * 100).toFixed(1) : '0'
  }))

  const sorted = [...labelValuePairs].sort((a, b) => Number(b.value) - Number(a.value))

  insights.push(`Total produksi dalam periode ini adalah ${total} butir telur.`)
  insights.push(`Kategori dengan distribusi terbanyak adalah ${sorted[0].label} sebesar ${sorted[0].percent}%.`)

  return insights
})

// Produksi LineChart
const insightLine = computed(() => {
  if (!lineData.value || !lineData.value.datasets.length) return []

  const insights: string[] = []
  const datasets = lineData.value.datasets

  // 1. Tambahkan total per kategori
  datasets.forEach(ds => {
    const total = (ds.data as number[]).reduce((a, b) => a + b, 0)
    insights.push(`Total produksi untuk kategori ${ds.label} adalah ${total} butir.`)
  })

  // 2. Cari kategori dengan total produksi tertinggi
  const totals = datasets.map(ds => ({
    label: ds.label,
    total: (ds.data as number[]).reduce((a, b) => a + b, 0)
  }))
  const sorted = [...totals].sort((a, b) => b.total - a.total)
  insights.push(`Kategori dengan produksi tertinggi selama periode ini adalah ${sorted[0].label}.`)

  // 3. Cek stabilitas dan tren
  datasets.forEach(ds => {
    const data = ds.data as number[]
    const max = Math.max(...data)
    const min = Math.min(...data)
    const range = max - min

    if (range < 3) {
      insights.push(`Kategori ${ds.label} relatif stabil dalam 7 hari terakhir.`)
    } else if (data[0] < data[data.length - 1]) {
      insights.push(`Kategori ${ds.label} mengalami peningkatan produksi.`)
    } else {
      insights.push(`Kategori ${ds.label} mengalami penurunan produksi.`)
    }
  })

  return insights
})


// ===== CHART: BAR & PIE OPTIONS =====
const pieOptions: ChartOptions<'pie'> = {
  responsive: true,
  plugins: {
    legend: {
      display: false, // matikan legend default
      position: 'bottom', // ini tidak akan dipakai karena display false
    },
    title: { display: false }
  }
}


// ===== CHART: AREA HARGA VS RATA-RATA =====
const areaChartData = computed(() => {
  const filtered = dataHarga.value.filter(item => {
    const date = new Date(item.tanggal)
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const y = String(date.getFullYear())
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
      labels: { font: { size: 12 } },
    },
    tooltip: {
      callbacks: {
        label: (context: any) => {
          const label = context.dataset.label || ''
          const value = context.formattedValue
          if (label === 'Harga Telur (Rp)') return `Harga: Rp ${value}`
          if (label === 'Rata-rata') return `Rata-rata: Rp ${value}`
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
      ticks: { font: { size: 12 } },
    },
    x: {
      ticks: {
        font: { size: 11 },
        maxRotation: 45,
        minRotation: 30,
        autoSkip: true,
        maxTicksLimit: 10,
      }
    }
  }
}

// ===== CHART: PREDIKSI HARGA TELUR =====
const chartPrediksiData = computed(() => ({
  labels: dataPrediksi.value.map(item => {
    const date = new Date(item.tanggal)
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
  }),
  datasets: [
    {
      label: 'Prediksi Harga (Rp)',
      data: dataPrediksi.value.map(item => item.harga),
      borderColor: '#10B981',
      backgroundColor: 'rgba(16,185,129,0.15)',
      tension: 0.4,
      fill: true,
      pointRadius: 3,
    }
  ]
}))
const chartPrediksiOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: true,
      position: 'bottom',
      labels: { font: { family: 'Inter', size: 12 } },
    },
  },
  scales: {
    y: { beginAtZero: false, ticks: { font: { family: 'Inter', size: 12 } } },
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

const chartGabunganData = computed(() => {
  if (!tanggalGabungan.value.length) return { labels: [], datasets: [] }

  return {
    labels: tanggalGabungan.value,
    datasets: [
      {
        label: 'Harga Aktual',
        data: dataAktualGabung.value,
        borderColor: '#3B82F6',
        tension: 0.3,
      },
      {
        label: 'Harga Prediksi',
        data: dataPrediksiGabung.value,
        borderColor: '#F59E0B',
        borderDash: [5, 5],
        tension: 0.3,
      }
    ]
  }
})

const chartGabunganOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        font: { size: 12 },
        color: '#374151'
      }
    }
  },
  scales: {
    y: {
      beginAtZero: false
    }
  }
}

const generateTanggalRange = (startDate: string, days: number): string[] => {
  const dates = []
  const start = new Date(startDate)
  for (let i = 0; i < days; i++) {
    const d = new Date(start)
    d.setDate(start.getDate() + i)
    dates.push(d.toISOString().split('T')[0]) // YYYY-MM-DD
  }
  return dates
}

const loadGabunganData = async () => {
  const startTanggalAktual = `${tahunGabungan.value}-${String(bulanGabungan.value).padStart(2, '0')}-01`
  const jumlahHariAktual = 30 // atau kamu bisa hitung jumlah hari di bulan tersebut

  // 1. Buat tanggal aktual & ambil datanya
  const tanggalAktual = generateTanggalRange(startTanggalAktual, jumlahHariAktual)
  const hargaAktual = await fetchDataAktual(tanggalAktual)

  // 2. Ambil tanggal prediksi mulai dari akhir aktual + 1
  const lastDate = new Date(tanggalAktual.at(-1)!)
  lastDate.setDate(lastDate.getDate() + 1)
  const startTanggalPrediksi = lastDate.toISOString().split('T')[0]

  const tanggalPrediksi = generateTanggalRange(startTanggalPrediksi, prediksiDaysGabungan.value)
  const hargaPrediksi = await fetchDataPrediksi(tanggalPrediksi)

  // 3. Gabungkan
  tanggalGabungan.value = [...tanggalAktual, ...tanggalPrediksi]

  dataAktualGabung.value = [
    ...hargaAktual,
    ...Array(prediksiDaysGabungan.value).fill(null)
  ]
  dataPrediksiGabung.value = [
    ...Array(tanggalAktual.length).fill(null),
    ...hargaPrediksi
  ]
}

watch([bulanGabungan, tahunGabungan, prediksiDaysGabungan], loadGabunganData, { immediate: true })

onMounted(loadGabunganData)

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
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <!-- Bar Chart -->
        <div class="bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col min-h-[360px]">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-semibold text-gray-800 font-poppins">
              Data Jumlah Produksi Per-Klaster
            </h2>
            <DropdownFilter
              v-model="selectedFilterBar"
              :options="filterOptions"
              class="w-[140px]"
            />
          </div>
          <div class="flex-1 flex flex-col">
            <p class="text-xs text-gray-500 font-inter mt-2 mb-6">
            Grafik menunjukkan jumlah produksi berdasarkan klaster telur selama periode yang dipilih.
            </p>
            <div class="w-full overflow-x-auto">
              <div class="min-w-[500px] h-[300px]">
                <ChartBar
                  v-if="barChartData"
                  :chartData="barChartData"
                  class="w-full h-full"
                />
                <p v-else class="text-sm text-gray-400 font-inter text-center">
                  Memuat data bar chart...
                </p>
              </div>
              <!-- Tombol Insight Bar -->
              <div class="flex justify-start mt-4">
                <button @click="showInsightBar = !showInsightBar" class="p-2 text-blue-600 hover:text-blue-800 transition">
                  <ChevronDown v-if="!showInsightBar" class="w-8 h-8" />
                  <ChevronUp v-else class="w-8 h-8" />
                </button>
              </div>
              <!-- Insight Bar -->
              <transition name="fade-scale">
                <div
                  v-if="showInsightBar && insightBar.length"
                  class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-gray-700 font-inter"
                >
                  <h4 class="font-semibold text-blue-800 mb-2">Insight Produksi Bar</h4>
                  <ul class="list-disc ml-5 space-y-1">
                    <li v-for="(text, i) in insightBar" :key="i" v-html="text" />
                  </ul>
                </div>
              </transition>
            </div>
          </div>
        </div>

        <!-- Pie Chart -->
        <div class="bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col min-h-[360px]">
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-semibold text-gray-800 font-poppins">
              Distribusi Klaster Telur
            </h2>
            <DropdownFilter
              v-model="selectedFilterPie"
              :options="filterOptions"
              class="w-[140px]"
            />
          </div>
          <div class="flex-1 flex flex-col">
            <p class="text-xs text-gray-500 font-inter mt-2 text-center mb-6">
              Persentase distribusi klaster telur berdasarkan mutu yang terdeteksi selama periode yang dipilih.
            </p>
            <!-- Wrapper Chart + Legend -->
            <div class="flex flex-col lg:flex-row items-center justify-center gap-6">
              <!-- Pie Chart yang lebih besar -->
              <div class="w-[300px] h-[300px]">
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

              <!-- Custom Legend -->
              <div class="flex flex-col items-start text-sm text-gray-600 font-inter">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-4 h-4 bg-[#2563EB] rounded"></div>
                  <span>Besar Mutu Tinggi</span>
                </div>
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-4 h-4 bg-[#60A5FA] rounded"></div>
                  <span>Kecil Mutu Tinggi</span>
                </div>
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-4 h-4 bg-[#F87171] rounded"></div>
                  <span>Mutu Rendah</span>
                </div>
              </div>
            </div>
            <!-- Tombol Insight -->
            <div class="flex justify-start mt-4">
              <button @click="showInsightPie = !showInsightPie" class="p-2 text-blue-600 hover:text-blue-800 transition">
                <ChevronDown v-if="!showInsightPie" class="w-8 h-8" />
                <ChevronUp v-else class="w-8 h-8" />
              </button>
            </div>
            <transition name="fade-scale">
              <div
                v-if="showInsightPie && insightPie.length"
                class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-gray-700 font-inter"
              >
                <h4 class="font-semibold text-blue-800 mb-2">Insight Distribusi Klaster</h4>
                <ul class="list-disc ml-5 space-y-1">
                  <li v-for="(text, i) in insightPie" :key="i" v-html="text" />
                </ul>
              </div>
            </transition>
          </div>
        </div>
      </div>


      <!-- Grafik Produksi -->
      <div class="bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 mb-8 flex flex-col">
        <!-- Header: Judul & Filter -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-1">Grafik Produksi Per-Klaster</h2>
            <p class="text-xs text-gray-600 font-inter">Tren produksi telur berdasarkan periode filter waktu</p>
          </div>
          <div class="flex gap-2 items-center">
            <DropdownFilter
              v-model="filterKategori"
              :options="kategoriOptions"
              class="w-[180px]"
            />
            <DropdownFilter
              v-model="selectedFilterLine"
              :options="filterOptions"
              class="w-[140px]"
            />
          </div>
        </div>

        <!-- Chart Line -->
        <div class="flex-1 flex items-center justify-center overflow-x-auto">
          <ChartLine
            v-if="lineData && lineOptions"
            :chartData="lineData"
            :chartOptions="lineOptions"
            class="w-full h-[300px]"
          />
          <p v-else class="text-sm text-gray-400 font-inter text-center">Memuat data grafik produksi...</p>
        </div>

        <!-- Tombol Insight -->
        <div class="flex justify-start mt-4">
          <button @click="showInsightProduksi = !showInsightProduksi" class="p-2 text-blue-600 hover:text-blue-800 transition">
            <ChevronDown v-if="!showInsightProduksi" class="w-8 h-8" />
            <ChevronUp v-else class="w-8 h-8" />
          </button>
        </div>

        <!-- Insight -->
        <transition name="fade-scale">
          <div
            v-if="showInsightProduksi"
            class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-gray-700 font-inter"
          >
            <h4 class="text-lg font-semibold text-blue-800 mb-2">Insight Produksi Telur</h4>
            <ul class="list-disc ml-5 space-y-1">
              <li v-for="(text, i) in insightLine" :key="i" v-html="text" />
            </ul>
          </div>
        </transition>
      </div>


      <!-- Grafik Harga vs Rata-rata (Full Width) -->
      <div class="bg-white h-[600px] rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 mb-8 flex flex-col">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-1">Harga Telur vs Rata-rata</h2>
            <p class="text-xs text-gray-500 font-inter">Periode berdasarkan filter bulan & tahun</p>
          </div>
          <div class="flex gap-2 items-center">
            <DropdownFilter :options="bulanOptions" v-model="bulan2" class="min-w-[120px]" />
            <DropdownFilter :options="tahunOptions" v-model="tahun2" class="min-w-[100px]" />
          </div>
        </div>
        <div class="flex-1 flex items-center justify-center overflow-x-auto">
          <ChartLine
            v-if="areaChartData?.datasets?.length && areaChartData.datasets[0].data.length > 0"
            :chartData="areaChartData"
            :chartOptions="areaChartOptions"
            class="w-full"
          />
          <p v-else class="text-sm text-gray-400 font-inter text-center">Memuat data grafik area...</p>
        </div>
      </div>

      <!-- Grafik Harga Telur Gabungan -->
      <div class="bg-white h-[600px] rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg p-6 flex flex-col mb-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800 font-poppins mb-1">Harga Telur (Aktual vs Prediksi)</h2>
            <p class="text-xs text-gray-400 font-inter">Data aktual dan prediksi digabung dalam satu grafik</p>
          </div>
          <div class="flex gap-2 items-center">
            <DropdownFilter :options="bulanOptions" v-model="bulanGabungan" class="min-w-[120px]" />
            <DropdownFilter :options="tahunOptions" v-model="tahunGabungan" class="min-w-[100px]" />
            <select v-model="prediksiDaysGabungan" class="border border-blue-500 text-blue-500 rounded-md px-2 py-1 text-sm">
              <option value="7">7 Hari</option>
              <option value="14">14 Hari</option>
              <option value="30">30 Hari</option>
            </select>
          </div>
        </div>

        <!-- Chart Gabungan -->
        <div class="flex-1 flex items-center justify-center overflow-x-auto">
          <ChartLine
            v-if="chartGabunganData && chartGabunganData.datasets.length"
            :chartData="chartGabunganData"
            :chartOptions="chartGabunganOptions"
            class="w-full"
          />
          <p v-else class="text-sm text-gray-400 font-inter">Memuat data gabungan harga telur...</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-scale-enter-active, .fade-scale-leave-active {
  transition: all 0.3s ease;
}
.fade-scale-enter-from, .fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

</style>