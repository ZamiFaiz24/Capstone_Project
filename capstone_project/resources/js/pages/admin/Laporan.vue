<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import DropdownFilter from "@/components/DropdownFilter.vue";
import Table from "@/components/Table.vue";
import Pagination from "@/components/Pagination.vue";
import ExportModal from '@/components/ExportModal.vue'
import { RefreshCcw, FileDown, ChevronUp, ChevronDown, Minus } from 'lucide-vue-next';
import { useToast, POSITION } from "vue-toastification"
import * as XLSX from "xlsx"

const toast = useToast()

// Props dari Laravel (Inertia)
interface HargaData {
  id: number;
  tanggal: string;
  harga: number;
  keterangan: "Naik" | "Turun" | "Stabil";
  no?: number; 
}

const props = defineProps<{
  hargaData: HargaData[]
}>()

const currentPage = ref(1)
const itemsPerPage = 10

const hargaList = computed(() => {
  // Urutkan dari tanggal terbaru ke terlama
  return [...props.hargaData]
    .sort((a, b) => new Date(b.tanggal).getTime() - new Date(a.tanggal).getTime())
    .map((item, index) => ({ ...item, no: index + 1 }))
})

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return hargaList.value.slice(start, start + itemsPerPage)
})

const form = useForm({
  harga: '',
  tanggal: ''
})

const simpanData = () => {
  if (!form.harga || !form.tanggal) {
    alert("Mohon lengkapi semua field")
    return
  }

  form.post(route('admin.harga.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      currentPage.value = 1
      toast.success("Data harga berhasil ditambahkan!", {
        position: POSITION.TOP_CENTER,
      })
    },
    onError: () => {
      toast.error("Gagal menyimpan data.", {
        position: POSITION.TOP_CENTER,
      })
    }
  })
}

interface FilterOption {
  value: string;
  label: string;
}

const selectedFilter = ref("");

// Dropdown filter
const filterOptions: FilterOption[] = [
  { label: "Hari Ini", value: "today" },
  { label: "Kemarin", value: "yesterday" },
  { label: "Seminggu", value: "week" },
  { label: "Sebulan", value: "month" },
];

// Refresh Tabel
const isRefreshing = ref(false);
const refreshData = () => {
  isRefreshing.value = true;
  setTimeout(() => {
    isRefreshing.value = false;
    alert('Data refreshed!');
  }, 1000);
};

function formatTanggal(dateString: string): string {
  const date = new Date(dateString);

  const day = String(date.getDate()).padStart(2, '0');
  const monthNames = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
  ];
  const month = monthNames[date.getMonth()];
  const year = date.getFullYear();

  return `${day} ${month} ${year}`;
}

const columns = [
  { key: "no", label: "No", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300",  },
  { key: "tanggal", label: "Tanggal", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300", render: (row: HargaData) => formatTanggal(row.tanggal)
},
  { key: "harga", label: "Harga Telur/kg", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black border-r border-gray-300", cellClass: "px-3 py-3 text-xs text-black border-r border-gray-300", render: (row: HargaData) => `Rp ${row.harga.toLocaleString('id-ID')}`
},
  { key: "keterangan", label: "Keterangan", headerClass: "px-3 py-3 text-left text-xs font-semibold text-black", cellClass: "px-3 py-3 text-xs",  render: (row: HargaData) => {
    const icon = row.keterangan === 'Naik'
      ? '▲'
      : row.keterangan === 'Turun'
      ? '▼'
      : '–'

    return `<span style="color:${
      row.keterangan === 'Naik'
        ? 'green'
        : row.keterangan === 'Turun'
        ? 'red'
        : 'blue'
    }">${icon} ${row.keterangan}</span>`
  } },
];

const exportModalRef = ref()

const openExportModal = () => {
  exportModalRef.value?.open()
}

const exportData = () => {
  const groupedByMonth = props.hargaData.reduce((acc, item) => {
    const date = new Date(item.tanggal)
    const month = date.toLocaleDateString('id-ID', {
      month: 'long',
      year: 'numeric',
    }) // Contoh: "Maret 2025"
    if (!acc[month]) acc[month] = []
    acc[month].push(item)
    return acc
  }, {} as Record<string, HargaData[]>)

  // Buat workbook
  const wb = XLSX.utils.book_new()

  // Loop setiap bulan → Sheet sendiri
  Object.entries(groupedByMonth).forEach(([month, data]) => {
    const formatted = data.map((row, i) => ({
      No: i + 1,
      Tanggal: new Date(row.tanggal).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      }),
      'Harga Telur (Rp)': `Rp ${row.harga.toLocaleString('id-ID')}`,
      Keterangan: row.keterangan,
    }))
    const ws = XLSX.utils.json_to_sheet(formatted)
    XLSX.utils.book_append_sheet(wb, ws, month)
  })

  XLSX.writeFile(wb, 'laporan_harga_telur_per_bulan.xlsx')

  const modal = document.getElementById('export_modal') as HTMLDialogElement
  if (modal && modal.open) {
    modal.close()
  }
}
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen p-16">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Laporan Sistem</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      <!-- Harga Telur Section -->
      <div class="bg-white rounded-2xl border border-blue-700 p-6 mb-8">
        <h2 class="text-lg lg:text-xl font-bold text-black font-poppins mb-6">
          Harga Telur Ayam Negeri Harian/KG
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-10 gap-8">
          <!-- Kolom 1: Tombol referensi + Input Form (30%) -->
          <div class="bg-white p-6 lg:col-span-3 gap-6">
            <div>
              <h3 class="text-base font-bold text-black font-poppins mb-2">
                Referensi Harga dari:
              </h3>
              <h4 class="text-xs text-black font-poppins mb-4">
                SIMBOK (Sistem Informasi Manajemen Bahan Pokok) Kabupaten Kebumen
              </h4>
              <a 
                href="https://simbok.kebumenkab.go.id" 
                target="_blank" 
                class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition mb-6"
              >
                Lihat Harga Pasar Hari Ini
              </a>
            </div>
            <div>
              <h3 class="text-base font-bold text-black font-poppins mb-6">
                Input Harga
              </h3>
              <form @submit.prevent="simpanData">
                <div class="space-y-4">
                  <div>
                    <label class="block text-xs text-black font-poppins mb-2">
                      Harga Telur per KG
                    </label>
                    <input
                      v-model="form.harga"
                      type="number"
                      class="w-full h-9 px-4 rounded-2xl text-black border border-blue-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Masukkan harga"
                    />
                  </div>
                  <div>
                    <label class="block text-xs text-black font-poppins mb-2">
                      Tanggal
                    </label>
                    <input
                      v-model="form.tanggal"
                      type="date"
                      class="w-full h-9 px-4 rounded-2xl text-gray-500 border border-blue-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <button
                    type="submit"
                    class="w-full h-9 bg-blue-500 text-white font-semibold font-poppins rounded-2xl hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-sky-500 mt-6"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Kolom 2: Data Table (70%) -->
          <div class="lg:col-span-7 gap-8">
            <!-- Controls -->
            <div class="flex flex-col justify-end sm:flex-row gap-3 mb-4">
              <DropdownFilter
                v-model="selectedFilter"
                :options="filterOptions"
                class="w-[140px]"
              />
              <button
                @click="refreshData"
                class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <span>Refresh Data</span>
                <span :class="isRefreshing ? 'animate-spin' : ''">
                  <RefreshCcw class="w-4 h-4" />
                </span>
              </button>
              <button
                @click="openExportModal"
                class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700"
              >
                <span>Ekspor Data</span>
                <FileDown class="w-3 h-4" />
              </button>
            </div>
            <ExportModal ref="exportModalRef" :onConfirm="exportData" />

            <!-- Table -->
            <div class="overflow-x-auto border border-blue-800 rounded-2xl">
              <Table :columns="columns" :rows="paginatedData" />
            </div>

            <!-- Pagination -->
            <div>
              <Pagination
                :total-items="hargaList.length"
                :items-per-page="itemsPerPage"
                :current-page="currentPage"
                @update:currentPage="(page) => currentPage = page"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Laporan Sistem Section -->
      <div class="bg-white rounded-lg p-6">
        <div class="mb-6">
          <h2 class="text-2xl font-semibold text-black font-poppins mb-4">
            Laporan Sistem
          </h2>
          <div class="flex flex-col sm:flex-row gap-3">
            <DropdownFilter
              v-model="selectedFilter"
              :options="filterOptions"
              class="w-[140px]"
            />
            <button
              @click="refreshData"
              class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <span>Refresh Data</span>
            </button>
            <button
              @click="openExportModal"
              class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700"
            >
              <span>Ekspor Data</span>
            </button>
          </div>
          <ExportModal ref="exportModalRef" :onConfirm="exportData" />
        </div>
        <!-- Statistik Cards -->
        <div class="border border-gray-400 p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="text-xs font-semibold text-black font-['Inter']">
              Jumlah Produksi Telur
            </div>
            <div class="text-xs font-semibold text-black font-['Inter']">
              Perangkat dan Sensor
            </div>
            <div class="text-xs font-semibold text-black font-['Inter']">
              Harga Telur per KG
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Column 1: Produksi Telur -->
            <div class="space-y-4">
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-1 mr-2" viewBox="0 0 12 2" fill="none">
                    <path d="M1 1H11" stroke="#0056B3" stroke-width="2" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Rata-rata Produksi Telur</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Rata-rata periode ini terbilang Stabil
                </div>
              </div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M14 8L8 2L2 8" stroke="#0B8B1E" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Tertinggi</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M2 2L8 8L14 2" stroke="#CA3434" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Terendah</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
            </div>
            <!-- Column 2: Perangkat dan Sensor -->
            <div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M2 2L8 8L14 2" stroke="#CA3434" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Tertinggi</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
            </div>
            <!-- Column 3: Harga Telur -->
            <div>
              <div class="bg-gray-300 p-4 rounded">
                <div class="flex items-center mb-2">
                  <svg class="w-3 h-2 mr-2" viewBox="0 0 16 10" fill="none">
                    <path d="M2 2L8 8L14 2" stroke="#CA3434" stroke-width="3" stroke-linecap="round" />
                  </svg>
                  <span class="text-xs text-black">Produksi Telur Tertinggi</span>
                </div>
                <div class="text-xs font-semibold text-black mb-1">
                  200 Butir dalam Periode Ini
                </div>
                <div class="text-xs text-black font-light">
                  Terjadi Pada Tanggal 21 Mei 2025
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>