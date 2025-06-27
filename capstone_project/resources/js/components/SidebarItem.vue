<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, required: true },
  label: { type: String, required: true },
  icon: { type: Object, required: true },
  isOpen: { type: Boolean, default: true }, // baru ditambah
});

const page = usePage();

const isActive = () => {
  return page.url === props.href;
};

</script>

<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-3 py-3 rounded-lg transition-all duration-200 font-poppins relative group',
      isOpen ? 'px-10' : 'px-4 justify-center',
      isActive()
        ? 'bg-blue-50 text-blue-700 font-semibold border-l-4 border-blue-600 shadow'
        : 'text-gray-700 hover:bg-blue-100 hover:text-blue-700 hover:font-semibold'
    ]"
  >
    <!-- Animated left border -->
    <span
      class="absolute left-0 top-0 h-full w-1 rounded-r bg-blue-600 transition-all duration-200"
      :class="isActive() ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'"
    ></span>

    <!-- Icon -->
    <component :is="icon" class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" />

    <!-- Label -->
    <transition name="fade">
      <span
        v-if="isOpen"
        class="transition-colors duration-200 whitespace-nowrap"
      >
        {{ label }}
      </span>
    </transition>
  </Link>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
