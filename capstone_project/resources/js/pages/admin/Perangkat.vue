<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import Pagination from '@/components/Pagination.vue'
import Table from '@/components/Table.vue'
import ExportModal from '@/components/ExportModal.vue'
import { ref, computed, onMounted } from 'vue'
import { CirclePower, Microchip, RefreshCcw, FileDown, Radio, Wifi, MonitorCog } from 'lucide-vue-next';
import dayjs from 'dayjs'
import { useToast, POSITION } from "vue-toastification"
import * as XLSX from 'xlsx'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)

const toast = useToast()

// Tipe data log
interface LogItem {
  id: number
  judul: string
  pesan: string
  tipe: string
  tautan: string | null
  sudah_dibaca: boolean
  dibuat_pada: string
  user_id: number
}

const props = defineProps<{
  logs: LogItem[]
  status: boolean
}>()

const status = ref<boolean>(props.status)
const deviceStatus = computed(() => status.value)
const logs = ref<LogItem[]>(props.logs ?? [])

// Hitung waktu aktif terakhir
const lastActive = computed(() => {
  const aktif = logs.value.find(log =>
    log.pesan?.toLowerCase().includes('dinyalakan')
  )
  return aktif ? dayjs(aktif.dibuat_pada).format('DD MMM YYYY, HH:mm') : '-'
})

const deviceLogs = computed(() =>
  logs.value.map(log => ({
    waktu: formatTime(log.dibuat_pada), 
    perangkat: log.judul,
    status: log.pesan.toLowerCase().includes('nyala') || log.pesan.toLowerCase().includes('aktif')
      ? 'Aktif'
      : 'Tidak Aktif',
    catatan: log.pesan,
  }))
)


const formatTime = (date: string) => {
  return dayjs(date).format('DD MMM YYYY, HH:mm')
}

// Form untuk toggle status
const form = useForm({ status: '' })

const toggleDeviceStatus = async (on: boolean) => {
  const iotStatus = on ? 'ON' : 'OFF'

  try {
    // Kirim status ke perangkat IoT (API dari kode pertama)
    const res = await fetch('/api/status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ status: iotStatus }),
    })

    const data = await res.json()

    // Jika sukses, baru lanjut simpan log ke Laravel
    if (data.status === iotStatus) {
      form.status = on ? 'on' : 'off'
      form.post(route('admin.perangkat.toggle'), {
        preserveScroll: true,
        onSuccess: () => {
          status.value = on
          toast.success(`Perangkat berhasil ${on ? 'diaktifkan' : 'dinonaktifkan'}`, {
            position: POSITION.TOP_CENTER,
            timeout: 3000,
          })
        },
        onError: () => {
          toast.error('Gagal mencatat status perangkat di sistem', {
            position: POSITION.TOP_CENTER,
            timeout: 3000,
          })
        },
      })
    } else {
      toast.error('Gagal mengubah status perangkat IoT', {
        position: POSITION.TOP_CENTER,
        timeout: 3000,
      })
    }

  } catch (error) {
    console.error('Error toggle IoT:', error)
    toast.error('Gagal menghubungi perangkat IoT', {
      position: POSITION.TOP_CENTER,
      timeout: 3000,
    })
  }
}


onMounted(() => {
  window.Echo.channel('log-perangkat')
  .listen('LogPerangkatUpdated', (e: any) => {
    console.log('DATA DARI BROADCAST:', e)
      logs.value.unshift(e.log)
    })
})

const columns = [
  { label: 'Waktu', key: 'waktu' },
  { label: 'Perangkat', key: 'perangkat' },
  {
    label: 'Status',
    key: 'status',
    render: (row: any) => {
      const color = row.status === 'Aktif' ? 'bg-green-500' : 'bg-red-500'
      return `
        <span class="px-2 py-1 rounded-full text-white text-xs font-semibold ${color}">
          ${row.status}
        </span>
      `
    }
  },
  { label: 'Catatan', key: 'catatan' },
]

const currentPage = ref(1)
const itemsPerPage = ref(10)

const totalItems = computed(() => deviceLogs.value.length)

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return deviceLogs.value.slice(start, start + itemsPerPage.value)
})

function updatePage(page: number) {
  currentPage.value = page
}

const exportModalRef = ref()

function openExportModal() {
  exportModalRef.value?.open()
}

function exportToExcel() {
  const exportData = deviceLogs.value.map((log) => ({
    Waktu: log.waktu,
    Perangkat: log.perangkat,
    Status: log.status,
    Catatan: log.catatan
  }))

  const worksheet = XLSX.utils.json_to_sheet(exportData)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Log Perangkat')

  XLSX.writeFile(workbook, 'log_perangkat.xlsx')
}

const showConfirmModal = ref(false)
const confirmAction = ref<null | (() => void)>(null)
const confirmMessage = ref('')

function openConfirmModal(message: string, action: () => void) {
  confirmMessage.value = message
  confirmAction.value = action
  showConfirmModal.value = true
}
function closeConfirmModal() {
  showConfirmModal.value = false
  confirmAction.value = null
  confirmMessage.value = ''
}
function confirmModalYes() {
  if (confirmAction.value) confirmAction.value()
  closeConfirmModal()
}

const isRefreshing = ref(false)

function refreshLog() {
  isRefreshing.value = true
  router.reload({
    only: ['logs'],
    onSuccess: (page) => {
      logs.value = (page.props.logs as LogItem[])
      toast.success('Data log berhasil diperbarui!', {
        position: POSITION.TOP_CENTER,
        timeout: 1000,
      })
      isRefreshing.value = false
    },
    onError: () => {
      isRefreshing.value = false
    }
  })
}
</script>


<template>
  <AppLayout>
    <transition name="fade-up" appear>
      <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
        <!-- Judul Halaman -->
        <div class="mb-2">
          <h1 class="text-xl font-bold text-blue-600 font-poppins">Perangkat</h1>
        </div>
        <div class="w-30 h-px bg-gray-400 mb-12"></div>

        <!-- Kartu Status & Kontrol -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
          <!-- Kartu Status -->
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

          <!-- Kartu Kontrol -->
          <div class="w-full h-[225px] bg-white rounded-[15px] border border-gray-300 hover:border-blue-600 hover:shadow-xl p-6 relative">
            <h2 class="text-xl font-semibold text-gray-800 mb-8 font-poppins">Kontrol Perangkat</h2>
            <div class="flex justify-center gap-12 mb-6">
              <button
                @click="openConfirmModal('Aktifkan perangkat?', () => toggleDeviceStatus(true))"
                :class="['w-12 h-12 rounded-full border-2', deviceStatus ? 'border-green-500 bg-green-50' : 'border-gray-400']"
              >
                <CirclePower class="w-10 h-10 mx-auto cursor-pointer" :class="deviceStatus ? 'text-green-500' : 'text-gray-400'" />
              </button>
              <button
                @click="openConfirmModal('Nonaktifkan perangkat?', () => toggleDeviceStatus(false))"
                :class="['w-12 h-12 rounded-full border-2', !deviceStatus ? 'border-red-500 bg-red-50' : 'border-gray-400']"
              >
                <CirclePower class="w-10 h-10 mx-auto cursor-pointer" :class="!deviceStatus ? 'text-red-500' : 'text-gray-400'" />
              </button>
            </div>
            <div class="flex justify-center gap-12">
              <span class="text-lg font-semibold text-black font-poppins">ON</span>
              <span class="text-lg font-semibold text-black font-poppins">OFF</span>
            </div>
          </div>
        </div>

        <!-- Tabel Log -->
        <div class="w-full bg-white rounded-[15px] border border-gray-300 hover:border-blue-600 hover:shadow-xl p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-black font-poppins">Tabel Log Perangkat</h2>
            <!-- Tombol Filter & Ekspor: masih dummy -->
            <div class="flex justify-end gap-3">
              <!-- Optional: kamu bisa aktifkan ini nanti -->
              <!-- <DropdownFilter v-model="selectedFilter" :options="filterOptions" placeholder="Filter Data" class="w-[140px]" /> -->
              <button 
              @click="refreshLog"
              :disabled="isRefreshing"
              class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2"
              >
                <span>Refresh Data</span>
                <span :class="isRefreshing ? 'animate-spin' : ''">
                  <RefreshCcw class="w-4 h-4" />
                </span>
              </button>
              <button @click="openExportModal" 
              class="flex items-center justify-center gap-2 w-full sm:w-[140px] h-10 px-3 text-xs font-semibold text-white bg-blue-800 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-700">
                Ekspor Data
                <FileDown class="w-4 h-4" />
              </button>
            </div>
          </div>
          <ExportModal ref="exportModalRef" :onConfirm="exportToExcel" />

          <!-- Table -->
          <div class="overflow-x-auto border border-blue-800 rounded-2xl">
            <Table :columns="columns" :rows="paginatedData" />
          </div>

          <!-- Pagination -->
          <div>
            <Pagination
              :total-items="totalItems"
              :items-per-page="itemsPerPage"
              :current-page="currentPage"
              @update:currentPage="updatePage"
            />
          </div>
        </div>

        <!-- Modal Konfirmasi -->
        <div v-if="showConfirmModal"  class="fixed inset-0 z-50 flex items-center justify-center bg-white/30 backdrop-blur-sm transition-all">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 border border-blue-200 relative animate-fadeIn">
            <div class="flex justify-center mb-4">
              <CirclePower
                class="w-14 h-14 transition-colors duration-200"
                :class="confirmMessage.toLowerCase().includes('nonaktif') ? 'text-red-500' : 'text-green-500'"
              />
            </div>
            <h3 class="font-bold text-2xl mb-2 text-blue-700 text-center font-poppins">Konfirmasi</h3>
            <p class="mb-8 text-gray-600 text-center text-base">{{ confirmMessage }}</p>
            <div class="flex justify-end gap-3">
              <button
                class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition font-semibold"
                @click="closeConfirmModal"
              >Batal</button>
              <button
                class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow transition"
                @click="confirmModalYes"
              >Ya, Lanjutkan</button>
            </div>
            <!-- Close button -->
            <button
              class="absolute top-3 right-3 text-gray-400 hover:text-blue-600 transition"
              @click="closeConfirmModal"
              aria-label="Tutup"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
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

table th,
table td {
  white-space: nowrap;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px);}
  to { opacity: 1; transform: translateY(0);}
}
.animate-fadeIn {
  animation: fadeIn 0.3s;
}
</style>
