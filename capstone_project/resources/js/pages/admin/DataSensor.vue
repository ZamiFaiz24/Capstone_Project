<script setup lang="ts">
import { ref, computed, watch, onMounted } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import PaginationDaisy from '@/components/Pagination.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import Table from '@/components/Table.vue';
import { RefreshCcw, FileDown } from 'lucide-vue-next';
import { useToast, POSITION } from "vue-toastification"
import api from "@/lib/axios";
import axios from "axios";

// Toast configuration
const toast = useToast()

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
interface WadahData {
  id_wadah: number;
  klaster: string;
  total_berat: number;
  kapasitas_max: number;
  status_penuh: boolean;
}
interface NotifikasiLog {
  id: number;
  judul: string;
  pesan: string;
  sudah_dibaca: number;
  dibuat_pada: string;
  tautan: string;
}

// Reactive data
const allData = ref<SensorData[]>([]);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const notifikasiLogs = ref<NotifikasiLog[]>([]);
const isLoadingNotif = ref(false);

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

const notifColumns = [
  { key: 'judul', label: 'Judul', headerClass: 'px-3 py-3', cellClass: 'px-3 py-3 font-semibold' },
  { key: 'pesan', label: 'Pesan', headerClass: 'px-3 py-3', cellClass: 'px-3 py-3' },
  {
    key: 'sudah_dibaca',
    label: 'Status',
    headerClass: 'px-3 py-3',
    cellClass: 'px-3 py-3',
    render: (row: NotifikasiLog) =>
      row.sudah_dibaca === 0
        ? `<span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded font-bold text-xs">Belum Dibaca</span>`
        : `<span class="inline-block px-2 py-1 bg-gray-100 text-gray-500 rounded text-xs">Sudah Dibaca</span>`
  },
  {
    key: 'dibuat_pada',
    label: 'Waktu',
    headerClass: 'px-3 py-3',
    cellClass: 'px-3 py-3',
    render: (row: NotifikasiLog) => new Date(row.dibuat_pada).toLocaleString('id-ID')
  },
];

const fetchNotifikasiLogs = async () => {
  isLoadingNotif.value = true;
  try {
    const res = await axios.get('/admin/notifikasi-log'); // gunakan endpoint baru
    notifikasiLogs.value = res.data.data.map((n: any) => ({
      ...n,
      sudah_dibaca: Number(n.sudah_dibaca),
    }));
  } catch (e) {
    console.error("Detail error:", e);
    toast.error("Gagal mengambil log!!");
  } finally {
    isLoadingNotif.value = false;
  }
};

// Panggil saat halaman dimount
onMounted(() => {
  fetchNotifikasiLogs();
});

// Pagination for Notifikasi Logs
const notifCurrentPage = ref(1);
const notifItemsPerPage = ref(10);
const notifTotalItems = computed(() => notifikasiLogs.value.length);
const notifTotalPages = computed(() => Math.ceil(notifTotalItems.value / notifItemsPerPage.value));
const notifStartItem = computed(() => (notifCurrentPage.value - 1) * notifItemsPerPage.value + 1);
const notifEndItem = computed(() => Math.min(notifCurrentPage.value * notifItemsPerPage.value, notifTotalItems.value));
const paginatedNotifikasiLogs = computed(() => {
  const startIndex = (notifCurrentPage.value - 1) * notifItemsPerPage.value;
  const endIndex = startIndex + notifItemsPerPage.value;
  return notifikasiLogs.value.slice(startIndex, endIndex);
});
</script>


<template>
  <AppLayout>
    <transition name="fade-up" appear>
      <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
      <!-- Judul Halaman -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Data Sensor</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>
      <!-- Main Content -->
      <div class="bg-white rounded-2xl border border-blue-700 shadow-lg max-w-6xl mx-auto">
        <!-- Page Header -->
        <div class="p-6 pb-2">
          <h2 class="text-lg font-semibold text-gray-900 font-poppins mb-1">Tabel Data Sensor</h2>
        </div>

        <!-- Controls Section -->
        <div class="px-6 flex flex-wrap items-center gap-3 justify-end mb-6">
          <DropdownFilter
            v-model="selectedFilter"
            :options="filterOptions"
            class="w-[140px]"
          />
          <button 
            @click="refreshData"
            :disabled="isRefreshing"
            class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2"
          >
            <span>Refresh Data</span>
            <span :class="isRefreshing ? 'animate-spin' : ''">
              <RefreshCcw class="w-4 h-4" />
            </span>
          </button>
          <button 
            @click="exportData"
            class="px-3 py-2 bg-blue-800 text-white text-sm font-semibold rounded font-inter hover:bg-blue-900 flex items-center gap-2"
          >
            Ekspor Data
            <FileDown class="w-3 h-4" />
          </button>
        </div>

        <!-- Data Table -->
        <div class="mb-6 overflow-x-auto px-6">
          <Table :columns="columns" :rows="currentData" />
        </div>
        <PaginationDaisy
          :total-items="totalItems"
          :items-per-page="itemsPerPage"
          :current-page="currentPage"
          @update:currentPage="(val) => currentPage = val"
        />
        </div>

        <!-- Log Notifikasi -->
        <div class="bg-white rounded-2xl border border-blue-700 shadow-lg max-w-6xl mx-auto mt-10">
          <div class="p-6 pb-2">
            <h2 class="text-lg font-semibold text-blue-600 font-poppins mb-1">Log Notifikasi</h2>
          </div>
          <div class="mb-6 overflow-x-auto px-6">
            <Table :columns="notifColumns" :rows="paginatedNotifikasiLogs" />
            <div v-if="isLoadingNotif" class="text-center py-4 text-gray-500">Memuat data notifikasi...</div>
            <div v-else-if="notifikasiLogs.length === 0" class="text-center py-4 text-gray-400">Tidak ada notifikasi.</div>
            <PaginationDaisy
              :total-items="notifTotalItems"
              :items-per-page="notifItemsPerPage"
              :current-page="notifCurrentPage"
              @update:currentPage="(val) => notifCurrentPage = val"
            />
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
</style>