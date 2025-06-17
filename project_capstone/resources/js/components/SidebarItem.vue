<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue'; 

const props = defineProps<{
  label: string;
  href: string;
  icon: any;
}>();

const page = usePage();

// Aktif jika url sama, atau khusus beranda jika di dashboard
const isActive = computed(() =>
  page.url === props.href || (props.href === '/' && page.url === '/dashboard')
);
</script>

<template>
  <Link
    :href="href"
    class="group flex items-center gap-4 rounded-[10px] p-4 font-bold font-poppins cursor-pointer
      w-[256px] mx-0
      hover:w-[200px] hover:mx-auto
      transition-all duration-300 ease-in-out
    "
    :class="{
      'bg-blue-600 text-white w-[200px] shadow-md mx-auto': isActive,
      'text-blue-600 bg-white hover:bg-blue-600 hover:text-white': !isActive
    }"
  >
    <component
      :is="icon"
      class="w-5 h-5 transition-colors duration-300 ease-in-out"
      :class="isActive ? 'text-white' : 'text-blue-600 group-hover:text-white'"
    />
    <span class="text-lg ml-2 transition-colors duration-300 ease-in-out" :class="isActive ? 'text-white' : 'text-blue-600 group-hover:text-white'">
      {{ label }}
    </span>
  </Link>
</template>