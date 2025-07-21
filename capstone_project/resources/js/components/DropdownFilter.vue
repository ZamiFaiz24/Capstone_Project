<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps<{
  modelValue: string;
  options: { label: string; value: string }[];
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void;
}>()

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const selectedLabel = computed(() => {
  const selected = props.options.find(opt => opt.value === props.modelValue);
  return selected ? selected.label : 'Pilih';
});

function selectOption(value: string) {
  emit('update:modelValue', value);
  isOpen.value = false;
}

// Tutup dropdown saat klik di luar komponen
function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <button
      type="button"
      class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2 w-[140px] justify-between transition-colors duration-200"
      @click="isOpen = !isOpen"
    >
      <span>{{ selectedLabel }}</span>
      <svg class="w-3 h-2 stroke-2 stroke-white ml-2" fill="none" viewBox="0 0 14 8">
        <path d="M1 1L7 7L13 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>

    <!-- Dropdown List -->
    <ul
      v-if="isOpen"
      class="absolute left-0 mt-1 bg-white rounded-lg w-[140px] z-10 shadow border border-blue-400 p-0"
    >
      <li
        v-for="option in props.options"
        :key="option.value"
      >
        <a
          class="block text-sm font-semibold font-inter px-3 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition-colors duration-150 cursor-pointer"
          :class="modelValue === option.value ? 'text-blue-600 bg-blue-50' : 'text-gray-700'"
          @click.prevent="selectOption(option.value)"
        >
          {{ option.label }}
        </a>
      </li>
    </ul>
  </div>
</template>
