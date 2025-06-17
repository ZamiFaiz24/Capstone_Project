<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import AppNavbar from '@/components/AppNavbar.vue'; // import navbar
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="flex">
      <!-- Sidebar -->
      <AppSidebar class="fixed top-0 left-0 z-40 w-64 h-screen bg-white shadow-md" />
  
      <!-- Konten utama -->
      <div class="flex flex-col flex-1 min-h-screen ml-64">
        <!-- Navbar -->
        <AppNavbar class="fixed top-0 left-64 right-0 z-30 w-[calc(100%-16rem)] bg-white shadow-md" />
  
        <!-- Konten -->
        <main class="flex-1 p-6 bg-gray-50">
          <slot />
        </main>
      </div>
    </div>
</template>
  