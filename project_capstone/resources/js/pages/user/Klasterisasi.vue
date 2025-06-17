<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import ChartPie from '@/components/ChartPie.vue'
import ChartBar from '@/components/ChartBar.vue'
import DropdownFilter from '@/components/DropdownFilter.vue'
import Table from '@/components/Table.vue'
import { ref, computed, watch } from "vue";

// TypeScript interfaces
interface ClusterData {
  id: number;
  berat: number;
  klaster: "Kecil" | "Besar";
  kualitas: "Bagus" | "Jelek";
  waktu_masuk: string;
  status_penuh: boolean;
}
interface FilterOption {
  value: string;
  label: string;
}
interface WadahData {
  id_wadah: number;
  klaster: string;
  kapasitas_max: number;
  total_berat: number;
  status_penuh: boolean;
}

// Reactive data
const allData = ref<ClusterData[]>([]);
const currentPage = ref(1);
const itemsPerPage = 10;

const wadahList = ref<WadahData[]>([
  { id_wadah: 1, klaster: "besar-bagus", kapasitas_max: 5.0, total_berat: 4.7, status_penuh: false },
  { id_wadah: 2, klaster: "kecil-bagus", kapasitas_max: 5.0, total_berat: 5.0, status_penuh: true },
]);

// Dropdown filter logic
const filterOptions: FilterOption[] = [
  { value: "today", label: "Hari ini" },
  { value: "yesterday", label: "Kemarin" },
  { value: "week", label: "Minggu ini" },
  { value: "month", label: "Bulan ini" },
];
const selectedFilter = ref("");

const selectedFilterLabel = computed(() =>
  filterOptions.find(opt => opt.value === selectedFilter.value)?.label || "Filter Data"
);

// Chart data for bar chart
const chartData = [
  { kecil: 400, besar: 350 },
  { kecil: 450, besar: 300 },
  { kecil: 150, besar: 350 },
  { kecil: 130, besar: 150 },
  { kecil: 120, besar: 400 },
  { kecil: 450, besar: 350 },
  { kecil: 200, besar: 400 },
];

// Generate mock data
const generateMockData = (): ClusterData[] => {
  const data: ClusterData[] = [];
  const klasterOptions: ("Kecil" | "Besar")[] = ["Kecil", "Besar"];
  const kualitasOptions: ("Bagus" | "Jelek")[] = ["Bagus", "Jelek"];
  for (let i = 1; i <= 100; i++) {
    data.push({
      id: i,
      berat: +(Math.random() * 100 + 40).toFixed(2),
      klaster: klasterOptions[i % 2],
      kualitas: kualitasOptions[i % 2],
      waktu_masuk: `2025-06-${(i % 30 + 1).toString().padStart(2, "0")} 08:00:00`,
      status_penuh: i % 2 === 0,
    });
  }
  return data;
};
allData.value = generateMockData();

// Pagination & Table Data
const totalItems = computed(() => allData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));
const startItem = computed(() => (currentPage.value - 1) * itemsPerPage + 1);
const endItem = computed(() =>
  Math.min(currentPage.value * itemsPerPage, totalItems.value),
);
const currentData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return allData.value.slice(startIndex, endIndex);
});
const pageNumbers = computed(() => {
  const pages: (number | string)[] = [];
  const maxVisiblePages = 5;
  if (totalPages.value <= maxVisiblePages) {
    for (let i = 1; i <= totalPages.value; i++) {
      pages.push(i);
    }
  } else {
    if (currentPage.value <= 3) {
      pages.push(1, 2, 3, 4, "...", totalPages.value);
    } else if (currentPage.value >= totalPages.value - 2) {
      pages.push(1, "...", totalPages.value - 3, totalPages.value - 2, totalPages.value - 1, totalPages.value);
    } else {
      pages.push(1, "...", currentPage.value - 1, currentPage.value, currentPage.value + 1, "...", totalPages.value);
    }
  }
  return pages;
});

// Methods
const handleRefresh = () => {
  // Simulasi refresh
  alert("Data refreshed!");
};
const handleExport = () => {
  alert("Export data functionality would be implemented here");
};
const changePage = (page: number) => {
  if (typeof page === "number" && page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Pie chart data
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
    legend: { display: false },
  },
}

// Table columns
const clusterColumns = [
  { key: 'id', label: 'ID', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300' },
  { key: 'berat', label: 'Berat (gr)', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300' },
  { 
    key: 'klaster', label: 'Klaster', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
    render: (row: ClusterData) => `<span class="${row.klaster === 'Kecil' ? 'text-blue-600 font-semibold' : 'text-green-600 font-semibold'}">${row.klaster}</span>`
  },
  { 
    key: 'kualitas', label: 'Kualitas', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
    render: (row: ClusterData) => `<span class="${row.kualitas === 'Bagus' ? 'text-blue-600 font-semibold' : 'text-red-600 font-semibold'}">${row.kualitas}</span>`
  },
  { key: 'waktu_masuk', label: 'Waktu Masuk', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300' },
  { 
    key: 'status_penuh', label: 'Status Penuh', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black', cellClass: 'px-4 py-3 text-xs text-black',
    render: (row: ClusterData) => `<span class="${row.status_penuh ? 'text-green-600 font-semibold' : 'text-gray-400'}">${row.status_penuh ? 'Penuh' : 'Belum'}</span>`
  },
];
const wadahColumns = [
  { key: 'id_wadah', label: 'ID Wadah', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800' },
  { 
    key: 'klaster', label: 'Klaster', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800',
    render: (row: WadahData) => `<span class="${row.klaster.includes('besar') ? 'text-green-600 font-semibold' : 'text-blue-600 font-semibold'}">${row.klaster}</span>`
  },
  { key: 'kapasitas_max', label: 'Kapasitas Maks (kg)', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800' },
  { key: 'total_berat', label: 'Total Berat (kg)', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300', cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800' },
  { 
    key: 'status_penuh', label: 'Status Penuh', headerClass: 'px-4 py-3 text-left text-xs font-bold text-black', cellClass: 'px-4 py-3 text-xs text-black',
    render: (row: WadahData) => `<span class="${row.status_penuh ? 'text-green-600 font-semibold' : 'text-gray-400'}">${row.status_penuh ? 'Penuh' : 'Belum'}</span>`
  },
];

// Kartu status wadah telur
const batasBerat = 10; // misal 10 kg adalah batas wadah
const wadahTelur = ref([
  {
    nama: 'Telur Besar - Baik',
    jumlahTelur: 12,
    totalBerat: 9.4,
    isFull: false
  },
  {
    nama: 'Telur Kecil - Baik',
    jumlahTelur: 16,
    totalBerat: 10.2,
    isFull: true
  }
]);

watch(wadahTelur, (val) => {
  val.forEach((w) => {
    w.isFull = w.totalBerat >= batasBerat;
  });
}, { deep: true });

function resetWadah(index: number) {
  wadahTelur.value[index].jumlahTelur = 0;
  wadahTelur.value[index].totalBerat = 0;
  wadahTelur.value[index].isFull = false;
}
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen p-16">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Perangkat</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      <!-- Kartu Status Wadah Telur -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div
          v-for="(wadah, index) in wadahList"
          :key="index"
          :class="[
            'p-6 rounded-2xl shadow border-2',
            wadah.status_penuh ? 'border-red-500 bg-red-100' : 'border-blue-500 bg-white'
          ]"
        >
          <h2 class="text-lg font-bold mb-4 text-black">{{ wadah.klaster.replace('-', ' ').toUpperCase() }}</h2>
          <div class="mb-2 text-sm text-black">
            <span class="font-medium">ID Wadah:</span> {{ wadah.id_wadah }}
          </div>
          <div class="mb-2 text-sm text-black">
            <span class="font-medium">Kapasitas Maks:</span> {{ wadah.kapasitas_max }} kg
          </div>
          <div class="mb-2 text-sm text-black">
            <span class="font-medium">Total Berat:</span> {{ wadah.total_berat.toFixed(2) }} kg
          </div>
          <div class="mb-4 text-sm text-black">
            <span class="font-medium">Status:</span>
            <span :class="wadah.status_penuh ? 'text-red-600 font-semibold' : 'text-green-600'">
              {{ wadah.status_penuh ? 'Penuh' : 'Belum Penuh' }}
            </span>
          </div>
          <button
            @click="() => { wadah.total_berat = 0; wadah.status_penuh = false; }"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Bar Chart -->
        <ChartBar :chartData="chartData" />
        <!-- Pie Chart -->
        <div class="h-80 bg-white rounded-[15px] border border-gray-300 border-opacity-30 shadow-lg relative p-6 flex flex-col">
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
      </div>


      <!-- Data Table Section -->
      <div class="bg-white rounded-2xl border border-blue-700 p-6">
        <!-- Table Header -->
        <div class="mb-6">
          <h2 class="text-lg lg:text-xl font-bold text-gray-900 font-poppins mb-2">
            Tabel Data Hasil Klasterisasi
          </h2>
          <p class="text-xs font-bold text-gray-900 font-poppins">
            Melihat data hasil pembacaan sensor secara real-time
          </p>
        </div>

        <!-- Controls -->
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 mb-6">
          <DropdownFilter
            v-model="selectedFilter"
            :options="filterOptions"
            class="w-[140px]"
          />
          <!-- Refresh Button -->
          <button
            @click="handleRefresh"
            class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2 w-[140px] justify-center transition-colors duration-200"
          >
            <span>Refresh Data</span>
            <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 17 17">
              <path
                d="M16 8.5C16 6.51088 15.2098 4.60322 13.8033 3.1967C12.3968 1.79018 10.4891 1 8.5 1C6.40329 1.00789 4.39081 1.82602 2.88333 3.28333L1 5.16667M1 5.16667V1M1 5.16667H5.16667M1 8.5C1 10.4891 1.79018 12.3968 3.1967 13.8033C4.60322 15.2098 6.51088 16 8.5 16C10.5967 15.9921 12.6092 15.174 14.1167 13.7167L16 11.8333M16 11.8333H11.8333M16 11.8333V16"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>
          <!-- Export Button -->
          <button
            @click="handleExport"
            class="px-3 py-2 bg-blue-800 text-white text-sm font-semibold rounded font-inter hover:bg-blue-900 flex items-center gap-2 w-[140px] justify-center transition-colors duration-200"
          >
            <span>Ekspor Data</span>
            <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 13 17">
              <path
                d="M7.875 1V4C7.875 4.39782 8.01987 4.77936 8.27773 5.06066C8.53559 5.34196 8.88533 5.5 9.25 5.5H12M6.5 13V8.5M6.5 13L4.4375 10.75M6.5 13L8.5625 10.75M8.5625 1H2.375C2.01033 1 1.66059 1.15804 1.40273 1.43934C1.14487 1.72064 1 2.10218 1 2.5V14.5C1 14.8978 1.14487 15.2794 1.40273 15.5607C1.66059 15.842 2.01033 16 2.375 16H10.625C10.9897 16 11.3394 15.842 11.5973 15.5607C11.8551 15.2794 12 14.8978 12 14.5V4.75L8.5625 1Z"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>
        </div>

        <!-- Table Data Klasterisasi -->
        <div class="overflow-x-auto border border-blue-800 shadow-lg rounded-xl">
          <Table :columns="clusterColumns" :rows="currentData" />
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-6 gap-4">
          <div class="text-xs font-bold text-gray-500">
            Tampilkan {{ startItem }}-{{ endItem }} dari {{ totalItems }} data
          </div>
          <div class="flex items-center gap-2">
              <button
                  @click="changePage(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="text-xs font-bold text-gray-500 hover:text-gray-700 disabled:opacity-30 px-2 py-1"
              >
                  Sebelumnya
              </button>
              <div class="flex items-center gap-1">
                  <template v-for="(page, index) in pageNumbers" :key="index">
                      <span
                          v-if="page === '...'"
                          class="text-xs font-bold text-gray-500 px-2"
                      >...</span>
                      <button
                          v-else
                          @click="typeof page === 'number' && changePage(page)"
                          :class="[
                          'w-7 h-7 rounded text-xs font-bold flex items-center justify-center',
                          currentPage === page
                              ? 'bg-blue-600 text-white'
                              : 'bg-white text-gray-500 border border-gray-400 hover:bg-gray-50',
                          ]"
                      >
                          {{ page }}
                      </button>
                  </template>
              </div>
              <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="text-xs font-bold text-gray-500 hover:text-gray-700 disabled:opacity-30 px-2 py-1"
              >
              Selanjutnya
              </button>
          </div>
        </div>
      </div>

      <!-- Wadah Data Table Section -->
      <div class="bg-white rounded-2xl border border-blue-700 p-6 mt-8">
        <div class="mb-6">
          <h2 class="text-lg lg:text-xl font-bold text-gray-900 font-poppins mb-2">
            Tabel Data Wadah
          </h2>
          <p class="text-xs font-bold text-gray-900 font-poppins">
            Data status wadah berdasarkan klaster dan kapasitas
          </p>
        </div>
        <div class="overflow-x-auto border border-blue-800 shadow-lg rounded-xl">
          <Table :columns="wadahColumns" :rows="wadahList" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>