<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Table from '@/components/Table.vue';
import DropdownFilter from '@/components/DropdownFilter.vue';
import { ref, computed, onMounted, watch } from 'vue'
import { CirclePower, Microchip, RefreshCcw, FileDown, Radio, Wifi, MonitorCog } from 'lucide-vue-next';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

axios.defaults.withCredentials = true;
dayjs.extend(relativeTime);

// Data dan state
const deviceStatus = ref(false);
const isRefreshing = ref(false);
const deviceLogs = ref<DeviceLog[]>([]);
const selectedFilter = ref('');
const notifications = ref<any[]>([]); // notifikasi dari API

// Interface log untuk tabel
interface DeviceLog {
  waktu: string;
  perangkat: string;
  status: string;
  catatan: string;
}

// Format waktu
function formatTime(date: string) {
  const now = dayjs()
  const input = dayjs(date)
  const diffMinutes = now.diff(input, 'minute')
  if (diffMinutes <= 5) {
    return input.fromNow()
  } else {
    return input.format('HH:mm')
  }
}

// Kolom tabel
const columns = [
  { key: 'waktu', label: 'Waktu', headerClass: 'flex-1 p-3 border-r border-blue-600 text-xs font-semibold text-white font-inter', cellClass: 'flex-1 p-3 border-r border-blue-600 text-xs text-black font-inter' },
  { key: 'perangkat', label: 'Perangkat', headerClass: 'flex-1 p-3 border-r border-blue-600 text-xs font-semibold text-white font-inter', cellClass: 'flex-1 p-3 border-r border-blue-600 text-xs text-black font-inter' },
  { 
    key: 'status', 
    label: 'Status', 
    headerClass: 'flex-1 p-3 border-r border-blue-600 text-xs font-semibold text-white font-inter', 
    cellClass: 'flex-1 p-3 border-r border-blue-600 text-xs text-black font-inter',
    render: (row: any) => `<span class="${row.status.includes('Aktif') ? 'text-green-600' : 'text-red-600'}">${row.status}</span>`
  },
  { key: 'catatan', label: 'Catatan', headerClass: 'flex-1 p-3 text-xs font-semibold text-white font-inter', cellClass: 'flex-1 p-3 text-xs text-black font-inter' },
];

// Filter dropdown (kalau kamu ingin pakai)
const filterOptions = [
  { label: 'Hari Ini', value: 'today' },
  { label: 'Kemarin', value: 'yesterday' },
  { label: 'Seminggu', value: 'week' },
  { label: 'Sebulan', value: 'month' },
];

// Hitung terakhir aktif
const lastActive = computed(() =>
  deviceLogs.value.find(log => log.status.includes('Aktif'))?.waktu || '-'
);

// Fetch dari backend
const fetchNotifications = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); 
    const userId = 1; // Ganti sesuai ID user yang sedang login
    const res = await axios.get(`/api/notifikasi/user/${userId}`);
    notifications.value = res.data;

    console.log('🔥 Data notifikasi dari API:', res.data); // cek struktur di sini
  } catch (error) {
    console.error('Gagal mengambil notifikasi:', error);
  }
};


const fetchDeviceStatus = async () => {
  try {
    const res = await axios.get('/api/device/status');
    deviceStatus.value = res.data.status === 'on';
  } catch (error) {
    console.error('Gagal mengambil status perangkat:', error);
    deviceStatus.value = false;
  }
};

const fetchLogs = async () => {
  const res = await axios.get('/api/device-logs');
  deviceLogs.value = res.data;
};

// Convert notifications → deviceLogs
const convertNotificationsToLogs = () => {
  deviceLogs.value = notifications.value
    .filter(item => item.type === 'device')
    .map(item => ({
      waktu: formatTime(item.created_at),
      perangkat: item.device_name || 'Perangkat 1', // fallback jika nama tidak tersedia
      status: item.message.includes('nonaktif') ? 'Nonaktif' : 'Aktif',
      catatan: item.message,
    }));
};

// Toast
const toast = useToast();

// Toggle perangkat dan simpan log
const toggleDeviceStatus = async (status: boolean) => {
  isRefreshing.value = true;
  const newStatus = status ? 'Aktif' : 'Nonaktif';

  try {
    const response = await axios.post('/api/device/log', {
      perangkat: 'Sensor 1',
      status: newStatus,
      catatan: `Perangkat ${newStatus.toLowerCase()}`
    });

    if (response.status === 201 || response.status === 200) {
      deviceStatus.value = status;
      toast.success(`Perangkat ${newStatus.toLowerCase()}!`);
      await fetchLogs(); 
      await fetchNotifications();
      convertNotificationsToLogs(); // proses ke deviceLogs
    } else {
      toast.error('Gagal menyimpan log.');
    }
  } catch (error) {
    console.error(error);
    toast.error('Terjadi kesalahan saat mengubah status perangkat.');
  } finally {
    isRefreshing.value = false;
  }
};

const markAllAsRead = async () => {
  try {
    await axios.post('/api/notifikasi/mark-read-all');
    await fetchNotifications(); 
    convertNotificationsToLogs();
  } catch (error) {
    console.error('Gagal menandai sebagai dibaca:', error);
  }
};

const refreshData = async () => {
  isRefreshing.value = true;
  await fetchLogs();
  isRefreshing.value = false;
};

const exportData = () => {
  alert('Export data functionality would be implemented here');
};

// Jalankan saat halaman dibuka
onMounted(async () => {
  await fetchDeviceStatus();
  await fetchLogs();
  await fetchNotifications();
  convertNotificationsToLogs();
});

// Optional: pakai watch jika `notifications` bisa berubah dari luar
watch(notifications, convertNotificationsToLogs);
</script>

<template>
  <AppLayout>
    <div class="w-full min-h-screen p-16">
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Perangkat</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
        <div class="w-full h-[225px] bg-white rounded-[15px] border border-gray-300 hover:border-blue-600 hover:shadow-xl p-6 relative">
          <div class="absolute right-6 top-11">
            <MonitorCog class="w-20 h-24 text-black" />
          </div>

          <h2 class="text-xl font-semibold text-black mb-8 font-poppins">Status Perangkat Saat Ini</h2>
          <div :class="deviceStatus ? 'text-green-600' : 'text-red-600'" class="text-sm font-semibold mb-3 font-poppins">
            {{ deviceStatus ? 'Aktif / ON' : 'Tidak Aktif / OFF' }}
          </div>
          <div class="text-sm font-semibold text-gray-800 mb-8 font-poppins">
            Terakhir Aktif: {{ lastActive }}
          </div>

          <div class="flex gap-8">
            <div class="flex items-center gap-3">
              <Radio class="w-5 h-4 text-blue-600" />
              <span class="text-sm font-semibold text-gray-800 font-poppins">Sensor : Load Cell</span>
            </div>
            <div class="flex items-center gap-3">
              <Microchip class="w-5 h-5 text-blue-600" />
              <span class="text-sm font-semibold text-gray-800 font-poppins">Modul ESP 32</span>
            </div>
            <div class="flex items-center gap-3">
              <Wifi class="w-5 h-4 text-blue-600" />
              <span class="text-sm font-semibold text-gray-800 font-poppins">Sinyal: Bagus</span>
            </div>
          </div>
        </div>

        <div class="w-full h-[225px] bg-white rounded-[15px] border border-gray-300 hover:border-blue-600 hover:shadow-xl p-6 relative">
          <h2 class="text-xl font-semibold text-gray-800 mb-8 font-poppins">Kontrol Perangkat</h2>
          <div class="flex justify-center gap-12 mb-6">
            <button @click="toggleDeviceStatus(true)" :class="['w-12 h-12 rounded-full border-2', deviceStatus ? 'border-green-500 bg-green-50' : 'border-gray-400']">
              <CirclePower class="w-10 h-10 mx-auto" :class="deviceStatus ? 'text-green-500' : 'text-gray-400'" />
            </button>
            <button @click="toggleDeviceStatus(false)" :class="['w-12 h-12 rounded-full border-2', !deviceStatus ? 'border-red-500 bg-red-50' : 'border-gray-400']">
              <CirclePower class="w-10 h-10 mx-auto" :class="!deviceStatus ? 'text-red-500' : 'text-gray-400'" />
            </button>
          </div>
          <div class="flex justify-center gap-12">
            <span class="text-lg font-semibold text-black font-poppins">ON</span>
            <span class="text-lg font-semibold text-black font-poppins">OFF</span>
          </div>
        </div>
      </div>

      <div class="w-full bg-white rounded-[15px] border border-gray-300 hover:border-blue-600 hover:shadow-xl p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-black font-poppins">Tabel Log Perangkat</h2>
          <div class="flex gap-3">
            <DropdownFilter v-model="selectedFilter" :options="filterOptions" placeholder="Filter Data" class="w-[140px]" />
            <button @click="refreshData" :disabled="isRefreshing" class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2">
              <span>Refresh Data</span>
              <span :class="isRefreshing ? 'animate-spin' : ''">
                <RefreshCcw class="w-4 h-4" />
              </span>
            </button>
            <button @click="exportData" class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2">
              Ekspor Data
              <FileDown class="w-3 h-4" />
            </button>
          </div>
        </div>
        <div class="border border-blue-600 rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Waktu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Perangkat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Catatan</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(log, index) in deviceLogs" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ log.waktu }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ log.perangkat }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm" :class="log.status === 'Aktif' ? 'text-green-600' : 'text-red-600'">
                  {{ log.status }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ log.catatan }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
