<script setup lang="ts">
import { ref, computed, watch, onMounted } from "vue";
import AppLayout from '@/layouts/AppLayout.vue';
import PaginationDaisy from '@/components/Pagination.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import Table from '@/components/Table.vue';
import { RefreshCcw, FileDown } from 'lucide-vue-next';
import { useToast, POSITION } from "vue-toastification"
import dayjs from 'dayjs'
import axios from "axios";

// Toast configuration
const toast = useToast()

interface SensorData {
  id: number;
  no: number;
  berat: number;
  intensitas: number;
  dibuat_pada: string;
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

const filteredData = computed(() => {
  if (!selectedFilter.value) return allData.value;

  const now = dayjs();

  // Debug: tampilkan format tanggal setiap item
  console.log('🔍 Filter:', selectedFilter.value);
  allData.value.forEach(item => {
    console.log(`ID: ${item.id} | Dibuat pada: ${item.dibuat_pada} | Parsed: ${dayjs(item.dibuat_pada).format()}`);
  });

  return allData.value.filter(item => {
    const itemDate = dayjs(item.dibuat_pada);

    switch (selectedFilter.value) {
      case "today":
        return itemDate.isSame(now, 'day');
      case "yesterday":
        return itemDate.isSame(now.subtract(1, 'day'), 'day');
      case "week":
        return itemDate.isAfter(now.subtract(7, 'day'));
      case "month":
        return itemDate.isAfter(now.subtract(30, 'day'));
      default:
        return true;
    }
  });
});

// Filter functionality
const filterOptions: FilterOption[] = [
  { label: 'Hari Ini', value: 'today' },
  { label: 'Kemarin', value: 'yesterday' },
  { label: 'Seminggu', value: 'week' },
  { label: 'Sebulan', value: 'month' },
];
const selectedFilter = ref('');

const fetchSensorData = async () => {
  try {
    const res = await axios.get('/api/sensor-data');
    allData.value = res.data.data.map((item: any, index: number) => ({
      id: item.id,
      no: index + 1,
      berat: item.berat,
      intensitas: item.intensitas,
      dibuat_pada: item.dibuat_pada,
    }));
  } catch (error) {
    console.error("Gagal mengambil data sensor:", error);
    toast.error("Gagal mengambil data sensor");
  }
};


// Computed for pagination
const totalItems = computed(() => allData.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
const startItem = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1);
const endItem = computed(() => Math.min(currentPage.value * itemsPerPage.value, totalItems.value));

const currentData = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return filteredData.value.slice(startIndex, endIndex);
});

// Refresh Tabel
const isRefreshing = ref(false);
const refreshData = async () => {
  isRefreshing.value = true;
  try {
    await fetchSensorData(); // pastikan ini adalah fungsi ambil data dari backend
    toast.success("Data berhasil diperbarui!");
  } catch (error) {
    toast.error("Gagal memperbarui data!");
  } finally {
    isRefreshing.value = false;
  }
};


const exportData = () => {
  const rows = allData.value;
  if (rows.length === 0) {
    toast.warning("Tidak ada data untuk diekspor");
    return;
  }

  const headers = ["No", "Berat", "Intensitas", "Status", "Waktu"];
  const csvContent = [
    headers.join(","),
    ...rows.map(row => `${row.no},${row.berat},${row.intensitas},${row.dibuat_pada}`)
  ].join("\n");

  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.setAttribute("download", `data_sensor_${new Date().toISOString()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
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
  { key: 'berat', label: 'Berat (gram)', headerClass: 'px-3 py-3 border-r border-gray-300', cellClass: 'px-3 py-3 border-r border-gray-300' },
  { key: 'intensitas', label: 'Intensitas (lux)', headerClass: 'px-3 py-3 border-r border-gray-300', cellClass: 'px-3 py-3 border-r border-gray-300' },
  {
    key: 'dibuat_pada',
    label: 'Data Masuk',
    headerClass: 'px-3 py-3',
    cellClass: 'px-3 py-3',
    render: (row: SensorData) => new Date(row.dibuat_pada).toLocaleString('id-ID')
  },
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
  fetchSensorData();
  fetchNotifikasiLogs(); // tetap panggil juga notifikasi
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

const showModal = ref(false)

const form = ref({
  berat: '',
  intensitas: ''
})

const submitManualData = async () => {
  const beratNum = parseFloat(form.value.berat);
  const intensitasNum = parseFloat(form.value.intensitas);

  // Validasi input
  if (isNaN(beratNum) || beratNum <= 0) {
    toast.error('❌ Berat harus berupa angka yang valid');
    return;
  }

  if (isNaN(intensitasNum) || intensitasNum <= 0) {
    toast.error('❌ Intensitas harus berupa angka yang valid');
    return;
  }

  try {
    await axios.post('/admin/manual-input', {
      berat: beratNum,
      intensitas: intensitasNum,
    });

    toast.success('✅ Data berhasil ditambahkan dan diklasterisasi');
    showModal.value = false;

    // Reset form
    form.value = {
      berat: '',
      intensitas: ''
    };

  } catch (err: any) {
    if (err.response && err.response.data?.message?.includes('Wadah penuh')) {
      toast.warning('⚠️ Wadah penuh. Data tidak disimpan!');
    } else {
      toast.error('❌ Gagal mengirim data');
    }
  }
};



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

          <!-- 🔽 Tombol Input Manual -->
          <button 
            @click="showModal = true"
            class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2"
          >
            Input Manual
          </button>

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

         <!-- Modal Input Manual -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-white/30 backdrop-blur-sm transition-all">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 border border-blue-200 relative animate-fadeIn">
            
            <!-- Header Icon -->
            <div class="flex justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-14 h-14 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 20l9-5-9-5-9 5 9 5z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 12l9-5-9-5-9 5 9 5z" />
              </svg>
            </div>

            <!-- Judul -->
            <h3 class="font-bold text-2xl mb-2 text-blue-700 text-center font-poppins">Input Manual</h3>
            <p class="mb-6 text-gray-600 text-center text-sm">Silakan isi data berat dan intensitas secara manual.</p>

            <!-- Form -->
            <form @submit.prevent="submitManualData" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berat (gram)</label>
                <input 
                  v-model.number="form.berat"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-2 border text-gray-900 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Intensitas (lux)</label>
                <input 
                  v-model.number="form.intensitas"
                  type="number"
                  step="0.01"
                  required
                  class="w-full px-4 py-2 border text-gray-900 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
              </div>

              <!-- Tombol Aksi -->
              <div class="flex justify-end gap-3 pt-2">
                <button 
                  type="button"
                  @click="showModal = false"
                  class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition font-semibold"
                >
                  Batal
                </button>
                <button 
                  type="submit"
                  class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow transition"
                >
                  Simpan
                </button>
              </div>
            </form>

            <!-- Tombol Close (pojok kanan atas) -->
            <button
              class="absolute top-3 right-3 text-gray-400 hover:text-blue-600 transition"
              @click="showModal = false"
              aria-label="Tutup"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
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

@keyframes fadeInUp {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-card {
  animation: fadeInUp 0.4s ease-out;
}

</style>