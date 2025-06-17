<script setup lang="ts">
import { computed } from "vue";
interface Option {
  label: string;
  value: string;
}
const props = defineProps<{
  options: Option[];
  modelValue: string;
}>();
const emits = defineEmits(["update:modelValue"]);

const selectedLabel = computed(() =>
  props.options.find(opt => opt.value === props.modelValue)?.label || "Filter Data"
);
</script>

<template>
  <div class="dropdown dropdown-start">
    <div
      tabindex="0"
      role="button"
      class="px-3 py-2 bg-blue-500 text-white text-sm font-semibold rounded font-inter hover:bg-blue-600 flex items-center gap-2 w-[140px] justify-between transition-colors duration-200"
    >
      <span>{{ selectedLabel }}</span>
      <svg class="w-3 h-2 stroke-2 stroke-white ml-2" fill="none" viewBox="0 0 14 8">
        <path d="M1 1L7 7L13 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
    <ul
      tabindex="0"
      class="dropdown-content menu bg-white rounded-lg w-[140px] z-10 shadow border border-blue-400 mt-1 p-0"
    >
      <li v-for="option in options" :key="option.value">
        <a
          class="text-sm font-semibold font-inter px-3 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition-colors duration-150"
          :class="modelValue === option.value ? 'text-blue-600 bg-blue-50' : 'text-gray-700'"
          @click.prevent="$emit('update:modelValue', option.value)"
        >
          {{ option.label }}
        </a>
      </li>
    </ul>
  </div>
</template>
