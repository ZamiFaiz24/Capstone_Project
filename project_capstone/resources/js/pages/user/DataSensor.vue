<script setup lang="ts">
import { ref, computed, watch } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import PaginationDaisy from '@/components/Pagination.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import Table from '@/components/Table.vue';
import { RefreshCcw, FileDown } from 'lucide-vue-next';

// TypeScript interfaces
interface SensorData {
  id: number;
  no: number;
  sensorName: string;
  value: string;
  status: "Normal" | "Warning" | "Critical";
  lastUpdate: string;
}
interface FilterOption {
  value: string;
  label: string;
}

// Reactive data
const allData = ref<SensorData[]>([]);
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Filter functionality
const filterOptions: FilterOption[] = [
  { label: 'Hari Ini', value: 'today' },
  { label: 'Kemarin', value: 'yesterday' },
  { label: 'Seminggu', value: 'week' },
  { label: 'Sebulan', value: 'month' },
];
const selectedFilter = ref('');

// Generate mock data
const generateMockData = (): SensorData[] => {
  const data: SensorData[] = [];
  const values = [
    "1.25", "1.23", "1.20", "1.15", "1.10",
    "1.08", "1.05", "1.02", "1.00", "0.98"
  ];
  const statuses: ("Normal" | "Warning")[] = [
    "Normal", "Normal", "Warning", "Normal", "Normal",
    "Normal", "Warning", "Normal", "Normal", "Normal"
  ];
  const times = [
    "2025-05-26 11:40 WIB", "2025-05-26 11:35 WIB", "2025-05-26 11:30 WIB",
    "2025-05-26 11:25 WIB", "2025-05-26 11:20 WIB", "2025-05-26 11:15 WIB",
    "2025-05-26 11:10 WIB", "2025-05-26 11:05 WIB", "2025-05-26 11:00 WIB", "2025-05-26 10:55 WIB"
  ];

  for (let i = 0; i < 100; i++) {
    data.push({
      id: i + 1,
      no: i + 1,
      sensorName: "Load Cell #1",
      value: `${values[i % 10]} kg`,
      status: statuses[i % 10],
      lastUpdate: times[i % 10],
    });
  }
  return data;
};

// Initialize data
allData.value = generateMockData();

// Computed for pagination
const totalItems = computed(() => allData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
const startItem = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1);
const endItem = computed(() => Math.min(currentPage.value * itemsPerPage.value, totalItems.value));

// Filtered & paginated data
const filteredData = computed(() => {
  if (!selectedFilter.value) return allData.value;
  // contoh filter, sesuaikan dengan kebutuhan
  return allData.value.filter(item => {
    // filter sesuai value, misal berdasarkan tanggal
    // return true jika cocok
    return true;
  });
});
const currentData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return filteredData.value.slice(startIndex, endIndex);
});

// Refresh Tabel
const isRefreshing = ref(false);
const refreshData = () => {
  isRefreshing.value = true;
  setTimeout(() => {
    isRefreshing.value = false;
    alert('Data refreshed!');
  }, 1000);
};

const exportData = () => {
  alert('Export data functionality would be implemented here');
};

const getStatusColor = (status: string): string => {
  switch (status.toLowerCase()) {
    case "warning":
      return "text-orange-600";
    case "critical":
      return "text-red-600";
    default:
      return "text-green-600";
  }
};

// Reset to page 1 when filter changes
watch(selectedFilter, () => {
  currentPage.value = 1;
});

const columns = [
  { key: 'no', label: 'No', headerClass: 'w-16 px-3 py-3 border-r border-gray-300', cellClass: 'w-16 px-3 py-3 border-r border-gray-300' },
  { key: 'sensorName', label: 'Nama Sensor', headerClass: 'flex-1 px-3 py-3 border-r border-gray-300', cellClass: 'flex-1 px-3 py-3 border-r border-gray-300' },
  { key: 'value', label: 'Nilai', headerClass: 'flex-1 px-3 py-3 border-r border-gray-300', cellClass: 'flex-1 px-3 py-3 border-r border-gray-300' },
  { key: 'status', label: 'Status', headerClass: 'flex-1 px-3 py-3 border-r border-gray-300', cellClass: 'flex-1 px-3 py-3 border-r border-gray-300', render: (row: SensorData) => row.status },
  { key: 'lastUpdate', label: 'Waktu Update', headerClass: 'w-44 px-3 py-3', cellClass: 'w-44 px-3 py-3' },
];
</script>


<template>
  <AppLayout>
    <div class="min-h-screen p-8 bg-slate-50">
      <div class="mx-auto max-w-5xl bg-white rounded-[15px] border border-blue-700 shadow-lg">
        <!-- Page Header -->
        <div class="p-6 pb-2">
          <h1 class="text-xl font-bold text-blue-600 font-poppins">Data Sensor</h1>
          <div class="w-24 h-px bg-gray-400 my-4"></div>
          <h2 class="text-lg font-bold text-gray-900 font-poppins mb-1">Tabel Data Sensor</h2>
        </div>

        <!-- Controls Section -->
        <div class="px-6 flex flex-wrap items-center gap-3 justify-end mb-6">
          <DropdownFilter
            v-model="selectedFilter"
            :options="filterOptions"
            placeholder="Filter Data"
            class="w-[140px]"
          />
          <!-- Refresh Button -->
          <button 
              @click="refreshData"
              :disabled="isRefreshing"
              class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2"
            >
              <span>
                Refresh Data
              </span>
              <span :class="isRefreshing ? 'animate-spin' : ''">
                <RefreshCcw class="w-4 h-4" />
              </span>
            </button>
            
            <button 
              @click="exportData"
               class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2"
            >
              Ekspor Data
              <FileDown class="w-3 h-4" />
            </button>
        </div>

        <!-- Data Table -->
        <div class="mb-6 overflow-x-auto px-6">
          <Table :columns="columns" :rows="currentData" />
        </div>

        <!-- Tambahkan sebelum PaginationDaisy -->
        <div class="flex items-center gap-2 px-6 pb-2">
          <span class="text-xs text-gray-500">Tampilkan</span>
          <select
            v-model="itemsPerPage"
            class="border border-gray-300 hover:border-blue-600 rounded px-2 py-1 text-xs"
          >
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
          <span class="text-xs text-gray-500">data per halaman</span>
        </div>

        <!-- Pagination dengan DaisyUI -->
        <PaginationDaisy
          :total-items="totalItems"
          :items-per-page="itemsPerPage"
          :current-page="currentPage"
          @update:currentPage="(val) => currentPage = val"
        />
      </div>
    </div>
  </AppLayout>
</template>