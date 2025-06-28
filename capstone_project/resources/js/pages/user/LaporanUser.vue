<script setup>
import { ref, computed } from 'vue'
import { TrendingUp, Egg, AlertCircle, FileDown, BarChart2, PieChart, Calendar } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import PdfReport from '@/components/PdfReport.vue'
import html2pdf from 'html2pdf.js'

const pdfRef = ref(null)
const pdfEl = ref(null)
defineExpose({ pdfEl })

// Pilihan bulan
const bulanOptions = [
  { label: 'Mei 2025', value: '2025-05' },
  { label: 'April 2025', value: '2025-04' },
  { label: 'Maret 2025', value: '2025-03' }
]
const bulanDipilih = ref(bulanOptions[0].value)

// Data dummy per bulan
const dataPerBulan = {
  '2025-05': {
    summary: [
      { icon: Egg, label: 'Total Produksi', value: '12.500 kg', desc: 'Bulan Ini', color: 'bg-blue-100 text-blue-700' },
      { icon: TrendingUp, label: 'Total Penjualan', value: 'Rp 120.000.000', desc: 'Bulan Ini', color: 'bg-green-100 text-green-700' },
      { icon: AlertCircle, label: 'Notifikasi Penting', value: '2', desc: 'Perlu Tindakan', color: 'bg-red-100 text-red-700' }
    ],
    insight: [
      'Produksi meningkat 10% dibanding bulan lalu.',
      'Klaster A mendominasi 65% dari total produksi.',
      '2 perangkat menunjukkan kapasitas hampir penuh.'
    ],
    table: [
      { tanggal: '2025-05-01', produksi: '400 kg', penjualan: 'Rp 3.500.000', klaster: 'A' },
      { tanggal: '2025-05-02', produksi: '420 kg', penjualan: 'Rp 3.700.000', klaster: 'A' },
      { tanggal: '2025-05-03', produksi: '410 kg', penjualan: 'Rp 3.900.000', klaster: 'B' }
    ]
  },
  '2025-04': {
    summary: [
      { icon: Egg, label: 'Total Produksi', value: '11.200 kg', desc: 'Bulan Ini', color: 'bg-blue-100 text-blue-700' },
      { icon: TrendingUp, label: 'Total Penjualan', value: 'Rp 110.000.000', desc: 'Bulan Ini', color: 'bg-green-100 text-green-700' },
      { icon: AlertCircle, label: 'Notifikasi Penting', value: '1', desc: 'Perlu Tindakan', color: 'bg-red-100 text-red-700' }
    ],
    insight: [
      'Produksi stabil dibanding bulan sebelumnya.',
      'Klaster B mulai menunjukkan peningkatan.',
      '1 perangkat perlu pengecekan rutin.'
    ],
    table: [
      { tanggal: '2025-04-01', produksi: '370 kg', penjualan: 'Rp 3.200.000', klaster: 'A' },
      { tanggal: '2025-04-02', produksi: '390 kg', penjualan: 'Rp 3.400.000', klaster: 'B' },
      { tanggal: '2025-04-03', produksi: '400 kg', penjualan: 'Rp 3.600.000', klaster: 'B' }
    ]
  },
  '2025-03': {
    summary: [
      { icon: Egg, label: 'Total Produksi', value: '10.800 kg', desc: 'Bulan Ini', color: 'bg-blue-100 text-blue-700' },
      { icon: TrendingUp, label: 'Total Penjualan', value: 'Rp 100.000.000', desc: 'Bulan Ini', color: 'bg-green-100 text-green-700' },
      { icon: AlertCircle, label: 'Notifikasi Penting', value: '0', desc: 'Aman', color: 'bg-gray-100 text-gray-700' }
    ],
    insight: [
      'Produksi sedikit menurun karena perawatan mesin.',
      'Klaster A dan B seimbang.',
      'Tidak ada notifikasi penting.'
    ],
    table: [
      { tanggal: '2025-03-01', produksi: '350 kg', penjualan: 'Rp 3.000.000', klaster: 'A' },
      { tanggal: '2025-03-02', produksi: '360 kg', penjualan: 'Rp 3.100.000', klaster: 'A' },
      { tanggal: '2025-03-03', produksi: '370 kg', penjualan: 'Rp 3.200.000', klaster: 'B' }
    ]
  }
}

// Data yang aktif sesuai bulan dipilih
const summary = computed(() => dataPerBulan[bulanDipilih.value]?.summary || [])
const insightList = computed(() => dataPerBulan[bulanDipilih.value]?.insight || [])
const tableData = computed(() => dataPerBulan[bulanDipilih.value]?.table || [])

function downloadPDF() {
  setTimeout(() => {
    const pdfElement = pdfRef.value?.$el || pdfRef.value;
    if (!pdfElement) return alert('Gagal menemukan elemen PDF.');

    html2pdf()
      .set({
        margin: 10,
        filename: `Laporan-Bulanan-${bulanOptions.find(b => b.value === bulanDipilih.value)?.label || ''}.pdf`,
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      })
      .from(pdfElement)
      .save();
  }, 100);
}

</script>

<template>
  <AppLayout>
    <div ref="pdfEl" class="min-h-screen bg-slate-50 p-6 md:p-10">
      <!-- Header Executive Summary -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-blue-800 mb-1">
            Laporan BI
            {{ bulanOptions.find(b => b.value === bulanDipilih.value)?.label || '' }}
          </h1>
          <div class="text-gray-500 text-sm">Ringkasan performa usaha Anda bulan ini</div>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
          <!-- Dropdown Filter Bulan -->
          <div class="relative">
            <select
              v-model="bulanDipilih"
              class="appearance-none border border-blue-300 rounded-md px-3 py-2 pr-8 text-sm font-semibold text-blue-700 bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition"
            >
              <option v-for="b in bulanOptions" :key="b.value" :value="b.value">
                {{ b.label }}
              </option>
            </select>
            <Calendar class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400" />
          </div>
          <button @click="downloadPDF" class="flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-md shadow hover:bg-red-700 transition">
            <FileDown class="w-4 h-4" /> Unduh PDF
          </button>
          <button @click="exportExcel" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-md shadow hover:bg-green-700 transition">
            <FileDown class="w-4 h-4" /> Export Excel
          </button>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8">
        <div
          v-for="item in summary"
          :key="item.label"
          class="flex items-center gap-4 p-6 rounded-2xl border shadow bg-white hover:shadow-md transition"
        >
          <div :class="['rounded-full p-3', item.color]">
            <component :is="item.icon" class="w-6 h-6" />
          </div>
          <div>
            <div class="text-sm text-gray-600 font-medium">{{ item.label }}</div>
            <div class="text-2xl font-bold">{{ item.value }}</div>
            <div class="text-xs text-gray-400">{{ item.desc }}</div>
          </div>
        </div>
      </div>

      <!-- Grafik Panel -->
      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl border shadow p-6 h-64 flex flex-col">
          <div class="flex items-center gap-2 mb-2">
            <BarChart2 class="w-5 h-5 text-blue-500" />
            <h2 class="text-lg font-semibold text-blue-700">Grafik Produksi Harian</h2>
          </div>
          <div class="flex-1 flex items-center justify-center">
            <span class="text-gray-400 italic text-sm">[Placeholder grafik produksi]</span>
          </div>
        </div>
        <div class="bg-white rounded-2xl border shadow p-6 h-64 flex flex-col">
          <div class="flex items-center gap-2 mb-2">
            <BarChart2 class="w-5 h-5 text-green-500" />
            <h2 class="text-lg font-semibold text-green-700">Grafik Penjualan Mingguan</h2>
          </div>
          <div class="flex-1 flex items-center justify-center">
            <span class="text-gray-400 italic text-sm">[Placeholder grafik penjualan]</span>
          </div>
        </div>
      </div>

      <!-- Pie Chart Panel -->
      <div class="bg-white rounded-2xl border shadow p-6 mb-8">
        <div class="flex items-center gap-2 mb-2">
          <PieChart class="w-5 h-5 text-purple-500" />
          <h2 class="text-lg font-semibold text-purple-700">Distribusi Klaster Telur</h2>
        </div>
        <div class="flex items-center justify-center h-40">
          <span class="text-gray-400 italic text-sm">[Placeholder pie chart klaster]</span>
        </div>
      </div>

      <!-- Tabel Ringkasan -->
      <div class="bg-white rounded-2xl border shadow p-6 mb-8 overflow-x-auto">
        <h2 class="text-lg font-semibold text-blue-700 mb-2">Tabel Ringkasan Produksi & Penjualan</h2>
        <table class="min-w-full text-sm text-left">
          <thead>
            <tr class="bg-blue-50 text-blue-700">
              <th class="px-4 py-2 font-semibold">Tanggal</th>
              <th class="px-4 py-2 font-semibold">Produksi</th>
              <th class="px-4 py-2 font-semibold">Penjualan</th>
              <th class="px-4 py-2 font-semibold">Klaster</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in tableData" :key="row.tanggal" class="border-b text-black hover:bg-blue-50">
              <td class="px-4 py-2">{{ row.tanggal }}</td>
              <td class="px-4 py-2">{{ row.produksi }}</td>
              <td class="px-4 py-2">{{ row.penjualan }}</td>
              <td class="px-4 py-2">{{ row.klaster }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Insight -->
      <div class="bg-white rounded-2xl border shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-blue-700 mb-2">Insight & Analisis</h2>
        <ul class="list-disc ml-5 text-gray-700 space-y-2">
          <li v-for="(item, index) in insightList" :key="index">{{ item }}</li>
        </ul>
      </div>

      <!-- Sembunyikan komponen PDF dari tampilan, hanya untuk export -->
      <PdfReport
        ref="pdfRef"
        :bulan-label="bulanOptions.find(b => b.value === bulanDipilih)?.label"
        :summary="summary.value"
        :insight-list="insightList.value"
        :table-data="tableData.value"
        usaha-name="Usaha Telur Jaya"
        style="position: absolute; left: -9999px; top: 0;"
      />

    </div>
  </AppLayout>
</template>