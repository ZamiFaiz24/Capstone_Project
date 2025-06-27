<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { User, Bell, LogOut } from 'lucide-vue-next'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const user = page.props.auth.user
const showNotif = ref(false)
const notifDropdownRef = ref<HTMLElement | null>(null)
const notifikasi = ref<Notifikasi[]>([])
const unreadCount = ref(0)

interface Notifikasi {
  id: number
  judul: string
  pesan: string
  dibuat_pada: string
  sudah_dibaca: number
  tautan: string
}

defineProps<{ isSidebarOpen: boolean }>()
const showDropdown = ref(false)

function toggleDropdown() {
  showDropdown.value = !showDropdown.value
}

function handleLogout() {
  stopPolling()
  router.post(route('logout'))
}

const toggleNotif = () => {
  showNotif.value = !showNotif.value
}

// Fetch notifikasi dari server
const fetchNotifikasi = async () => {
  try {
    const res = await axios.get('/admin/notifikasi-baru')
    
    // Pastikan setiap 'sudah_dibaca' dikonversi ke number
    notifikasi.value = res.data.data.map((n: Notifikasi) => ({
      ...n,
      sudah_dibaca: Number(n.sudah_dibaca),
    }))
    
    unreadCount.value = res.data.jumlah
  } catch (e) {
    console.error('Gagal ambil notifikasi:', e)
  }
}


// Tandai satu notifikasi dibaca
const tandaiNotifDibaca = async (notifId: number) => {
  try {
    console.log('Menandai notif dibaca...')
    await axios.patch(`/admin/notifikasi/${notifId}/baca`)
    console.log('Berhasil ditandai dibaca.')
    const notif = notifikasi.value.find(n => n.id === notifId)
    if (notif) notif.sudah_dibaca = 1
    unreadCount.value = notifikasi.value.filter(n => n.sudah_dibaca === 0).length
  } catch (e) {
    console.error('Gagal tandai notif dibaca:', e)
  }
}

const bukaNotifikasi = async (notif: Notifikasi) => {
  if (notif.sudah_dibaca === 0) {
    await tandaiNotifDibaca(notif.id) // pastikan ini selesai dulu
  }
  router.visit(notif.tautan) // baru redirect ke halaman
}

// Tandai semua dibaca
const tandaiSemuaNotifDibaca = async () => {
  try {
    await axios.patch('/admin/notifikasi/baca-semua')
    notifikasi.value.forEach(n => n.sudah_dibaca = 1)
    unreadCount.value = 0
  } catch (e) {
    console.error('Gagal tandai semua notif dibaca:', e)
  }
}

let pollingId: number | null = null

const startPolling = () => {
  if (pollingId === null) {
    pollingId = setInterval(fetchNotifikasi, 10000)
  }
}

const stopPolling = () => {
  if (pollingId !== null) {
    clearInterval(pollingId)
    pollingId = null
  }
}

onMounted(() => {
  // fetchNotifikasi()
  // startPolling()
})

// Hentikan polling saat dropdown notifikasi dibuka
watch(showNotif, (isOpen) => {
  if (isOpen) {
    stopPolling()
  } else {
    startPolling()
  }
})


// Tutup notifikasi jika klik di luar
const handleClickOutside = (event: MouseEvent) => {
  if (
    showNotif.value &&
    notifDropdownRef.value &&
    !notifDropdownRef.value.contains(event.target as Node)
  ) {
    showNotif.value = false
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})
</script>

<template>
  <div
    :class=" [
      'h-[70px] fixed top-0 z-50 flex items-center justify-between pr-25 pl-6 transition-all duration-300 bg-white shadow-sm',
      isSidebarOpen ? 'left-[256px] w-[calc(100%-256px)] justify-end pr-75' : 'left-16 w-[calc(100%-64px)]'
    ]"
  >
    <!-- Logo kecil STECI saat sidebar tertutup -->
    <div v-if="!isSidebarOpen" class="text-xl font-bold text-blue-600 font-poppins">
      STECI
    </div>

    <!-- Ikon Notifikasi dan Profil -->
    <div class="flex items-center gap-5">
      <div class="relative">
        <button @click="toggleNotif" class="relative focus:outline-none">
          <Bell class="w-6 h-6 text-blue-600" />
          <span
            v-if="unreadCount > 0"
            class="absolute -top-2 -right-2 min-w-[18px] h-[18px] flex items-center justify-center bg-red-600 text-white text-xs font-bold rounded-full border-2 border-white"
          >
            {{ unreadCount }}
          </span>
        </button>
        <!-- Dropdown notifikasi -->
        <transition name="fade">
          <div
            v-if="showNotif"
            ref="notifDropdownRef"
            class="absolute right-0 mt-2 w-96 bg-white shadow-2xl rounded-xl border border-gray-200 z-50"
          >
            <div class="p-4 max-h-96 overflow-y-auto">
              <div
                v-for="notif in notifikasi"
                :key="notif.id"
                @click="bukaNotifikasi(notif)"
                class="relative flex gap-3 items-start p-3 mb-2 rounded-lg cursor-pointer border transition-all duration-150 group"
                :class="[
                  notif.sudah_dibaca === 0 
                    ? 'bg-blue-50 border-blue-200 hover:bg-blue-100 shadow-sm notif-fade-in'
                    : 'bg-white border-gray-100 hover:bg-gray-100'
                ]"
              >
                <!-- Timeline vertical line & dot -->
                <div class="relative flex flex-col items-center mr-2">
                  <!-- Garis timeline -->
                  <div class="absolute left-1/2 top-0 bottom-0 w-[2px] -translate-x-1/2 bg-blue-200"></div>
                  <!-- Titik timeline -->
                  <div
                    class="w-3 h-3 rounded-full border-2"
                    :class="notif.sudah_dibaca === 0 ? 'bg-blue-500 border-blue-500 shadow-blue-glow' : 'bg-gray-300 border-gray-300'"
                    style="z-index:2"
                  ></div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <p class="font-semibold text-base" :class="notif.sudah_dibaca === 0 ? 'text-blue-900' : 'text-gray-800'">
                      {{ notif.judul }}
                    </p>
                    <span
                      v-if="notif.sudah_dibaca === 0"
                      class="ml-1 px-2 py-0.5 text-[10px] bg-blue-500 text-white rounded-full font-bold animate-pulse"
                    >Baru</span>
                  </div>
                  <p class="text-sm text-gray-700 mt-0.5">{{ notif.pesan }}</p>
                  <p class="text-[11px] text-gray-400 italic mt-1">
                    {{ new Date(notif.dibuat_pada).toLocaleString('id-ID') }}
                  </p>
                </div>
              </div>
              <div v-if="notifikasi.length === 0" class="flex flex-col items-center justify-center py-8 text-gray-400">
                <Bell class="w-10 h-10 mb-2" />
                <span>Tidak ada notifikasi.</span>
              </div>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-t bg-gray-50 rounded-b-xl">
              <button
                v-if="unreadCount > 0"
                @click.stop="tandaiSemuaNotifDibaca"
                class="text-xs font-semibold text-blue-600 hover:underline transition"
              >
                Tandai semua dibaca
              </button>
              <div
                class="text-xs text-blue-600 hover:underline cursor-pointer ml-auto font-semibold"
                @click="router.visit('/admin/data_sensor')"
              >
                Lihat semua notifikasi
              </div>
            </div>
          </div>
        </transition>
      </div>

      <div class="relative">
        <div
          class="w-12 h-12 rounded-full border-2 border-blue-600 bg-white flex items-center justify-center transition hover:bg-blue-800 hover:border-blue-800 group cursor-pointer"
          @click="toggleDropdown"
          tabindex="0"
        >
          <User class="w-5 h-6 text-blue-600 transition group-hover:text-white" />
        </div>
        <transition name="fade">
          <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-50 text-sm"
          >
            <div class="px-4 py-3 border-b border-gray-200">
              <div class="font-semibold text-gray-800">{{ user.name }}</div>
              <div class="text-gray-500 text-xs">{{ user.email }}</div>
            </div>
            <button
              @click="router.visit('/admin/profile')"
              class="w-full flex items-center gap-2 px-4 py-3 hover:bg-blue-50 text-blue-600"
            >
              <User class="w-4 h-4" /> Profil
            </button>
            <button
              @click.prevent="handleLogout"
              class="w-full flex items-center gap-2 px-4 py-3 hover:bg-red-50 text-red-600"
            >
              <LogOut class="w-4 h-4" /> Logout
            </button>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Animasi fade-in untuk notifikasi baru */
.notif-fade-in {
  animation: notifFadeIn 0.5s;
}
@keyframes notifFadeIn {
  from { opacity: 0; transform: translateY(10px);}
  to { opacity: 1; transform: translateY(0);}
}
</style>
