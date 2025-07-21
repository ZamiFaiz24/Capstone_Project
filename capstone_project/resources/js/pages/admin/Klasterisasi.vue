<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import ChartPie from '@/components/ChartPie.vue';
import ChartBar from '@/components/ChartBar.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import Table from '@/components/Table.vue';
import { ref, computed, watch, onMounted } from "vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import dayjs from "dayjs";

// Toast configuration
const toast = useToast();

const loadData = async () => {
  console.log('Loading...')
}


// TypeScript Interfaces
type ClusterData = {
  id: number;
  berat: number;
  intensitas: number;
  klaster: number;
  label_tampilan: string;
  label_ukuran: string;
  label_kualitas: string;
  waktu_masuk: string;
  status_penuh: boolean;
};

type FilterOption = {
  value: string;
  label: string;
};

type WadahData = {
  id_wadah: number;
  klaster: string;
  kapasitas_max: number;
  total_berat: number;
  jumlah_telur: number;
  status_penuh: boolean;
};

type WadahBackendResponse = {
  nama: string;
  jumlahTelur: number;
  totalBerat: number;
};

// Reactive Data
const allData = ref<ClusterData[]>([]);
const currentPage = ref(1);
const itemsPerPage = 10;
const wadahList = ref<WadahData[]>([]);
const selectedFilter = ref("");
const batasTelur = 5;

const wadahTelur = ref<WadahData[]>([]);

// Filter Options
const filterOptions: FilterOption[] = [
  { value: "today", label: "Hari ini" },
  { value: "yesterday", label: "Kemarin" },
  { value: "week", label: "Minggu ini" },
  { value: "month", label: "Bulan ini" },
];

const selectedFilterLabel = computed(() =>
  filterOptions.find(opt => opt.value === selectedFilter.value)?.label || "Filter Data"
);

watch(selectedFilter, () => {
  currentPage.value = 1;
});

const filteredAllData = computed(() => {
  const today = dayjs().format("YYYY-MM-DD");
  const yesterday = dayjs().subtract(1, "day").format("YYYY-MM-DD");
  const startOfWeek = dayjs().startOf("week");
  const startOfMonth = dayjs().startOf("month");

  return allData.value.filter(item => {
    const itemDate = dayjs(item.waktu_masuk).format("YYYY-MM-DD");
    switch (selectedFilter.value) {
      case "today": return itemDate === today;
      case "yesterday": return itemDate === yesterday;
      case "week": return dayjs(itemDate).isAfter(startOfWeek);
      case "month": return dayjs(itemDate).isAfter(startOfMonth);
      default: return true;
    }
  });
});

const totalItems = computed(() => filteredAllData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));
const startItem = computed(() => (currentPage.value - 1) * itemsPerPage + 1);
const endItem = computed(() => Math.min(currentPage.value * itemsPerPage, totalItems.value));

const currentData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  return filteredAllData.value.slice(startIndex, startIndex + itemsPerPage).map((row, index) => ({
    ...row,
    no: startIndex + index + 1,
  }));
});

const pageNumbers = computed(() => {
  const pages: (number | string)[] = [];
  const maxVisiblePages = 5;

  if (totalPages.value <= maxVisiblePages) {
    for (let i = 1; i <= totalPages.value; i++) pages.push(i);
  } else if (currentPage.value <= 3) {
    pages.push(1, 2, 3, 4, "...", totalPages.value);
  } else if (currentPage.value >= totalPages.value - 2) {
    pages.push(1, "...", totalPages.value - 3, totalPages.value - 2, totalPages.value - 1, totalPages.value);
  } else {
    pages.push(1, "...", currentPage.value - 1, currentPage.value, currentPage.value + 1, "...", totalPages.value);
  }

  return pages;
});

const clusterColumns = [
  {
    key: 'no',
    label: 'No',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
    render: (row: any) => `<span class="font-semibold">${row.no}</span>`,
  },
  {
    key: 'berat',
    label: 'Berat (gr)',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
  },
  {
    key: 'intensitas',
    label: 'Intensitas',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
  },
  {
    key: 'klaster',
    label: 'Kode Klaster',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
    render: (row: any) => `<span class="font-bold text-gray-800">${row.klaster}</span>`,
  },
  {
    key: 'label_ukuran',
    label: 'Ukuran',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
    render: (row: any) => {
      const color = row.label_ukuran === 'Besar' ? 'text-green-600' : 'text-blue-600';
      return `<span class="${color} font-semibold">${row.label_ukuran}</span>`;
    },
  },
  {
    key: 'label_kualitas',
    label: 'Kualitas',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-gray-300',
    render: (row: any) => {
      const color = row.label_kualitas === 'Mutu Rendah' ? 'text-red-600' : 'text-green-600';
      return `<span class="${color} font-semibold">${row.label_kualitas}</span>`;
    },
  },
  {
    key: 'label_tampilan',
    label: 'Label Tampilan',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black',
    cellClass: 'px-4 py-3 text-xs text-black',
    render: (row: any) => `<span class="font-semibold">${row.label_tampilan}</span>`,
  },
];

const wadahColumns = [
  {
    key: 'id_wadah',
    label: 'ID Wadah',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800',
  },
  {
    key: 'klaster',
    label: 'Klaster',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800',
    render: (row: WadahData) => {
      const color = row.klaster.includes('besar') ? 'text-green-600' : 'text-blue-600';
      return `<span class="${color} font-semibold">${row.klaster}</span>`;
    }
  },
  {
    key: 'kapasitas_max',
    label: 'Kapasitas Maks (kg)',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800',
  },
  {
    key: 'total_berat',
    label: 'Total Berat (kg)',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black border-r border-gray-300',
    cellClass: 'px-4 py-3 text-xs text-black border-r border-blue-800',
  },
  {
    key: 'status_penuh',
    label: 'Status Penuh',
    headerClass: 'px-4 py-3 text-left text-xs font-bold text-black',
    cellClass: 'px-4 py-3 text-xs text-black',
    render: (row: WadahData) => {
      const color = row.status_penuh ? 'text-green-600' : 'text-gray-400';
      const status = row.status_penuh ? 'Penuh' : 'Belum';
      return `<span class="${color} font-semibold">${status}</span>`;
    }
  },
];


const handleRefresh = async () => {
  try {
    const response = await axios.get('/api/sensor/klaster');
    allData.value = response.data;
  } catch (error) {
    toast.error("Gagal memuat data.");
    console.error(error);
  }
};

const handleExport = () => {
  alert("Export data functionality would be implemented here");
};
const changePage = (page: number) => {
  if (typeof page === "number" && page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const klasterManualThreshold = async () => {
  try {
    await axios.post('/admin/klasterisasi/threshold')
    alert("Klasterisasi manual selesai!")
    await loadData() // misalnya kamu refresh tabel
  } catch (err: unknown) {
    if (axios.isAxiosError(err)) {
      console.error("ERROR:", err.response?.data || err.message);
    } else if (err instanceof Error) {
      console.error("ERROR:", err.message);
    } else {
      console.error("Terjadi error yang tidak diketahui");
    }
    alert("Gagal melakukan klasterisasi manual");
  }
}


const klasterisasiManual = async () => {
  try {
    const res = await axios.post("/api/klasterisasi/manual");
    toast.success(res.data.message || "Klasterisasi selesai");
    await handleRefresh();
    await fetchWadahTelur();
  } catch (error: any) {
    toast.error("Gagal klasterisasi data");
    console.error(error);
  }
};

const fetchWadahTelur = async () => {
  try {
    const res = await axios.get('/admin/wadah-status');
    wadahTelur.value = res.data.map((item: WadahBackendResponse, index: number): WadahData => ({
      id_wadah: index + 1,
      klaster: item.nama,
      jumlah_telur: item.jumlahTelur,
      total_berat: item.totalBerat,
      kapasitas_max: batasTelur,
      status_penuh: item.jumlahTelur >= batasTelur,
    }));
  } catch (error) {
    console.error('Gagal ambil data wadah:', error);
  }
};

async function resetWadah(index: number) {
  const target = wadahTelur.value[index];

  try {
    await axios.post(`/admin/reset-wadah/${target.klaster}`);
    
    // Perbarui ulang data setelah reset berhasil
    await fetchWadahTelur();

    toast.success(`Wadah ${target.klaster} berhasil direset.`);
  } catch (error) {
    toast.error(`Gagal reset wadah ${target.klaster}`);
    console.error(error);
  }
}


onMounted(() => {
  handleRefresh();
  fetchWadahTelur();
  loadData();
});
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Klasterisasi</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      <!-- Kartu Status Wadah Telur -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div
          v-for="(wadah, index) in wadahTelur"
          :key="index"
          :class="[
            'p-6 rounded-2xl shadow-xl border-2 relative transition-all duration-300',
            wadah.status_penuh ? 'border-red-500 bg-red-50' : 'border-blue-500 bg-white'
          ]"
        >
          <!-- Badge Status -->
          <div
            class="absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full"
            :class="wadah.status_penuh ? 'bg-red-500 text-white' : 'bg-green-500 text-white'"
          >
            {{ wadah.status_penuh ? 'PENUH' : 'TERSEDIA' }}
          </div>

          <!-- Nama Klaster -->
          <h2 v-if="wadah.klaster" class="text-xl font-bold mb-4 text-blue-800 font-poppins tracking-wide uppercase">
            {{ wadah.klaster.replace('-', ' ') }}
          </h2>

          <!-- ID Wadah -->
          <div class="mb-2 text-sm text-gray-700">
            <span class="font-medium">ID Wadah:</span> {{ wadah.id_wadah }}
          </div>

          <!-- Kapasitas Maks -->
          <div class="mb-2 text-sm text-gray-700">
            <span class="font-medium">Kapasitas Maks:</span> {{ wadah.kapasitas_max }} butir
          </div>

          <!-- Jumlah Telur -->
          <div class="mb-2 text-sm text-gray-700">
            <span class="font-medium">Jumlah Telur:</span> {{ wadah.jumlah_telur }} / 5 butir
          </div>

          <!-- Total Berat -->
          <div class="mb-4 text-sm text-gray-700">
            <span class="font-medium">Total Berat:</span> {{ wadah.total_berat.toFixed(2) }} kg
          </div>

          <!-- Tombol Reset -->
          <button
            @click="resetWadah(index)"
            class="w-full mb-2 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 font-semibold transition"
          >
            Reset Wadah
          </button>
        </div>
      </div>

      <!-- Data Table Section -->
      <div class="bg-white rounded-2xl border border-gray-300 p-6 transition-all duration-300 hover:border-blue-600 hover:shadow-xl">
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
            @click="klasterisasiManual"
            class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2 w-[140px] justify-center transition-colors duration-200"
          >
            <span>Klasterisasi</span>
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
          <!-- Klaster Manual (tombol baru) -->
          <button
            @click="klasterManualThreshold"
            class="px-3 py-2 bg-green-600 text-white text-sm font-semibold rounded font-inter hover:bg-green-700 flex items-center gap-2 w-[140px] justify-center transition-colors duration-200"
          >
            <span>Klaster Manual</span>
            <svg class="w-4 h-4 stroke-2" fill="none" viewBox="0 0 16 16">
              <path
                d="M2 4H14M2 8H14M2 12H14"
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
    </div>
  </AppLayout>
</template>