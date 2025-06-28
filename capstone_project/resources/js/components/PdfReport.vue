<script setup>
import { computed } from 'vue'

// Props: data laporan bulanan
const props = defineProps({
  bulanLabel: String,
  summary: Array,
  insightList: Array,
  tableData: Array,
  usahaName: {
    type: String,
    default: 'Nama Usaha'
  }
})

const today = computed(() => new Date().toLocaleDateString('id-ID'))
</script>

<template>
  <div class="pdf-report px-8 py-8 bg-white text-black font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 border-b pb-4">
      <div>
        <div class="text-xl font-bold text-blue-800 mb-1">Laporan Bulanan Usaha</div>
        <div class="text-sm text-gray-600">{{ usahaName }}</div>
        <div class="text-sm text-gray-600">Periode: <span class="font-semibold">{{ bulanLabel }}</span></div>
      </div>
      <div class="text-xs text-gray-500 text-right">
        Dicetak: {{ today }}
      </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div
        v-for="item in summary"
        :key="item.label"
        class="border rounded-lg p-4 flex flex-col items-center"
      >
        <div class="font-semibold text-base mb-1">{{ item.label }}</div>
        <div class="text-lg font-bold mb-1">{{ item.value }}</div>
        <div class="text-xs text-gray-500">{{ item.desc }}</div>
      </div>
    </div>

    <!-- Table -->
    <div class="mb-6">
      <div class="font-semibold text-base mb-2 text-blue-700">Tabel Produksi & Penjualan</div>
      <table class="w-full border text-sm">
        <thead>
          <tr class="bg-blue-50 text-blue-700">
            <th class="border px-2 py-1">Tanggal</th>
            <th class="border px-2 py-1">Produksi</th>
            <th class="border px-2 py-1">Penjualan</th>
            <th class="border px-2 py-1">Klaster</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in tableData" :key="row.tanggal">
            <td class="border px-2 py-1">{{ row.tanggal }}</td>
            <td class="border px-2 py-1">{{ row.produksi }}</td>
            <td class="border px-2 py-1">{{ row.penjualan }}</td>
            <td class="border px-2 py-1">{{ row.klaster }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Insight -->
    <div>
      <div class="font-semibold text-base mb-2 text-blue-700">Insight & Analisis</div>
      <ul class="list-disc ml-6 text-gray-700 text-sm space-y-1">
        <li v-for="(item, idx) in insightList" :key="idx">{{ item }}</li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.pdf-report {
  /* Ukuran A4 untuk print/pdf */
  width: 210mm;
  min-height: 297mm;
  background: white;
}
@media print {
  .pdf-report {
    box-shadow: none !important;
    background: white !important;
    color: black !important;
  }
}
</style>