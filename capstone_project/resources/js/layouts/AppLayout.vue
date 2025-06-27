<script setup lang="ts">
import { ref, provide } from 'vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppNavbar from '@/components/AppNavbar.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}
withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
})

// Sidebar open/close state
const isSidebarOpen = ref(true)
provide('isSidebarOpen', isSidebarOpen)
</script>

<template>
  <div class="flex">
    <!-- Sidebar -->
    <AppSidebar
      :class="[
        'fixed top-0 left-0 z-40 h-screen bg-white shadow-md transition-all duration-300',
        isSidebarOpen ? 'w-64' : 'w-16'
      ]"
      :is-open="isSidebarOpen"
      @toggle="isSidebarOpen = !isSidebarOpen"
    />

    <!-- Main content -->
    <div
      :class="[
        'flex flex-col flex-1 min-h-screen transition-all duration-300',
        isSidebarOpen ? 'ml-64' : 'ml-16'
      ]"
    >
      <!-- Navbar -->
      <AppNavbar
        :is-sidebar-open="isSidebarOpen"
        :class="[
          'fixed top-0 z-30 w-full bg-white shadow-md transition-all duration-300',
          isSidebarOpen ? 'left-64 w-[calc(100%-16rem)]' : 'left-16 w-[calc(100%-4rem)]'
        ]"
      />

      <!-- Page content -->
      <main class="flex-1 p-6 bg-gray-50 mt-[64px]">
        <slot />
      </main>
    </div>
  </div>
</template>
