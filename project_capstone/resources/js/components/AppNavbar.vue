<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from '@/lib/utils'; // Pastikan path-nya benar sesuai struktur folder
import { User, Bell, Settings, LogOut } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3'
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

const showDropdown = ref(false);
const showNotif = ref(false);
const notifications = ref<any[]>([]);
const userId = 1; // Ganti sesuai user aktif di aplikasi

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

function toggleDropdown() {
  showDropdown.value = !showDropdown.value;
}
function closeDropdown() {
  showDropdown.value = false;
}
function toggleNotif() {
  showNotif.value = !showNotif.value;
}

function goTo(link: string) {
  if (link) window.location.href = link;
}

async function fetchNotifications() {
  try {
    const res = await axios.get(`/notifikasi/user/${userId}`);
    notifications.value = res.data.data.map((notif: any) => ({
      ...notif,
      time: dayjs(notif.created_at).fromNow() // atau .format('DD MMM YYYY HH:mm')
    }));
  } catch (err) {
    console.error('Gagal fetch notifikasi:', err);
  }
}

async function markAllRead() {
  const unread = notifications.value.filter(n => !n.read);
  for (const notif of unread) {
    try {
      await axios.patch(`/notifikasi/${notif.id}/read`);
      notif.read = true;
    } catch (err) {
      console.error(`Gagal tandai notifikasi ${notif.id} sebagai dibaca`, err);
    }
  }
}

// Fetch notifikasi saat komponen dimount
onMounted(() => {
  fetchNotifications();
});

function handleLogout() {
  router.post(route('logout'))
}

</script>


<template>
  <div class="w-[calc(100%-256px)] h-[70px] bg-white fixed left-[256px] top-0 z-50 flex items-center justify-end pr-10 gap-5 shadow-sm">
    // Notification Bell 
    <div class="relative">
      <button @click="toggleNotif" class="relative focus:outline-none">
        <Bell class="w-6 h-6 text-blue-600" />
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1"
        >
          {{ unreadCount }}
        </span>
      </button>
      <transition name="fade">
        <div
          v-if="showNotif"
          class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="flex items-center justify-between px-4 py-2 border-b font-semibold text-sm text-gray-700">
            Log Notifikasi
            <button
              v-if="unreadCount > 0"
              @click="markAllRead"
              class="text-xs text-blue-600 hover:underline"
            >
              Tandai sudah dibaca
            </button>
          </div>
          <div v-if="notifications.length === 0" class="px-4 py-6 text-gray-400 text-center text-xs">
            Tidak ada notifikasi
          </div>
          <div v-else class="max-h-80 overflow-y-auto px-4 py-3">
            <div class="relative">
              <!-- Timeline line -->
              <div class="absolute left-3 top-0 w-0.5 h-full bg-black"></div>
              <!-- Timeline items -->
              <div
                v-for="(notif, idx) in notifications.slice(0, 5)"
                :key="notif.id"
                class="relative mb-4"
                @click="notif.link ? goTo(notif.link) : null"
                :class="notif.link ? 'cursor-pointer' : ''"
              >
                <div class="absolute left-0 w-6 h-6 bg-white rounded-full border-2 border-blue-600 flex items-center justify-center">
                  <div :class="notif.read ? 'bg-gray-400' : 'bg-blue-600'" class="w-2.5 h-2.5 rounded-full"></div>
                </div>
                <div class="ml-14">
                  <div class="text-xs font-bold text-gray-500 mb-1 font-poppins">{{ notif.time }}</div>
                  <div :class="notif.read ? 'text-gray-400' : 'text-gray-800'" class="text-sm font-bold font-poppins">{{ notif.title }}</div>
                </div>
              </div>
            </div>
            <button class="w-full py-2 text-blue-600 hover:underline text-xs mt-1">Lihat Semua</button>
          </div>
        </div>
      </transition>
    </div>
    <!-- User Profile with hover and dropdown -->
    <div class="relative">
      <div
        class="w-12 h-12 rounded-full border-2 border-blue-600 bg-white flex items-center justify-center transition hover:bg-blue-800 hover:border-blue-800 group cursor-pointer"
        @click="toggleDropdown"
        tabindex="0"
      >
        <User class="w-5 h-6 text-blue-600 transition group-hover:text-blue-50" />
      </div>
      <transition name="fade">
        <div
          v-if="showDropdown"
          class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <a href="/profile" class="flex items-center gap-2 px-4 py-3 hover:bg-blue-50 text-gray-700">
            <User class="w-4 h-4" /> Profil
          </a>
          <a href="/settings" class="flex items-center gap-2 px-4 py-3 hover:bg-blue-50 text-gray-700">
            <Settings class="w-4 h-4" /> Setting
          </a>
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
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

