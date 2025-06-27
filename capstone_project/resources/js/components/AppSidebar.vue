<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import SidebarItem from '@/components/SidebarItem.vue';
import {
  House, Cable, Database, Boxes, ChartNoAxesCombined, BookOpen, Menu
} from 'lucide-vue-next';

defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['toggle'])

interface AuthUser {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  role: string;
}

const page = usePage() as unknown as { props: { auth: { user: AuthUser } } };
const role = page.props.auth.user.role;

const icons = {
  home: House,
  perangkat: Cable,
  sensor: Database,
  klaster: Boxes,
  visualisasi: ChartNoAxesCombined,
  laporanBI: BookOpen,
};
</script>

<template>
  <div
    :class="[
      'h-screen bg-white fixed top-0 left-0 z-50 overflow-y-auto shadow-md flex flex-col transition-all duration-300',
      isOpen ? 'w-64 px-6' : 'w-16 px-2'
    ]"
  >
    <!-- Header: Logo & Toggle sejajar (logo kiri, toggle kanan) -->
    <div class="flex items-center justify-between pt-4 pb-2">
      <transition name="fade-slide" mode="out-in">
        <div v-if="isOpen" key="logo" class="transition-all duration-300">
          <h1 class="text-[28px] font-[800] text-blue-600 font-poppins leading-tight">STECI</h1>
        </div>
      </transition>
      <button @click="$emit('toggle')" class="p-2 hover:bg-gray-100 rounded transition-colors duration-200">
        <Menu class="w-5 h-5 text-gray-600" />
      </button>
    </div>
    <!-- Subjudul hanya saat open -->
    <transition name="fade-slide" mode="out-in">
      <div v-if="isOpen" key="desc" class="text-xs font-medium text-gray-500 font-poppins leading-snug transition-all duration-300 mb-2">
        Sistem Telur Elektronik<br />Cerdas dan Informatif
      </div>
    </transition>

    <!-- UTAMA -->
    <div :class="['pt-4', isOpen ? 'px-0' : '']">
      <div
        v-if="isOpen"
        class="text-sm font-semibold text-gray-800 border-b border-gray-800 pb-3"
      >
        Utama
      </div>
    </div>
    <SidebarItem :href="role === 'admin' ? '/admin/dashboard' : '/user/dashboard'" label="Dashboard" :icon="icons.home" :is-open="isOpen" />

    <!-- MANAJEMEN - admin only -->
    <template v-if="role === 'admin'">
      <div :class="['pt-4', isOpen ? 'px-0' : '']">
        <div
          v-if="isOpen"
          class="text-sm font-semibold text-black border-b border-black pb-3"
        >
          Manajemen
        </div>
      </div>
      <SidebarItem href="/admin/perangkat" label="Perangkat" :icon="icons.perangkat" :is-open="isOpen" />
      <SidebarItem href="/admin/data_sensor" label="Data Sensor" :icon="icons.sensor" :is-open="isOpen" />
    </template>

    <!-- ANALISIS -->
    <template v-if="role === 'admin'">
      <div :class="['pt-4', isOpen ? 'px-0' : '']">
        <div
          v-if="isOpen"
          class="text-sm font-semibold text-black border-b border-black pb-3"
        >
          Analisis
        </div>
      </div>
      <SidebarItem href="/admin/klaster" label="Klasterisasi" :icon="icons.klaster" :is-open="isOpen" />
      <SidebarItem href="/admin/visualisasi" label="Visualisasi" :icon="icons.visualisasi" :is-open="isOpen" />
    </template>

    <!-- LAPORAN -->
    <div :class="['pt-4', isOpen ? 'px-0' : '']">
      <div
        v-if="isOpen"
        class="text-sm font-semibold text-black border-b border-black pb-3"
      >
        Laporan
      </div>
    </div>
    <SidebarItem :href="role === 'admin' ? '/admin/laporan' : '/user/laporan'" label="Laporan BI" :icon="icons.laporanBI" :is-open="isOpen" />
  </div>
</template>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.fade-slide-enter-from, .fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
}
.fade-slide-enter-to, .fade-slide-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}
</style>
