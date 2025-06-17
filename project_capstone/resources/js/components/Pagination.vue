<script setup lang="ts">
import { computed, defineProps, defineEmits } from 'vue'

const props = defineProps<{
  totalItems: number,
  itemsPerPage: number,
  currentPage: number
}>()

const emit = defineEmits(['update:currentPage'])

const totalPages = computed(() =>
  Math.ceil(props.totalItems / props.itemsPerPage)
)

const startItem = computed(() =>
  (props.currentPage - 1) * props.itemsPerPage + 1
)

const endItem = computed(() =>
  Math.min(props.currentPage * props.itemsPerPage, props.totalItems)
)

const pageNumbers = computed(() => {
  const pages: (number | string)[] = [];
  const maxVisiblePages = 5;
  if (totalPages.value <= maxVisiblePages) {
    for (let i = 1; i <= totalPages.value; i++) {
      pages.push(i);
    }
  } else {
    if (props.currentPage <= 3) {
      pages.push(1, 2, 3, 4, "...", totalPages.value);
    } else if (props.currentPage >= totalPages.value - 2) {
      pages.push(1, "...", totalPages.value - 3, totalPages.value - 2, totalPages.value - 1, totalPages.value);
    } else {
      pages.push(1, "...", props.currentPage - 1, props.currentPage, props.currentPage + 1, "...", totalPages.value);
    }
  }
  return pages;
})

function goToPage(page: number) {
  if (typeof page === 'number' && page >= 1 && page <= totalPages.value) {
    emit('update:currentPage', page)
  }
}
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-6 gap-4">
    <div class="text-xs font-bold text-gray-500">
      Tampilkan {{ startItem }}-{{ endItem }} dari {{ props.totalItems }} data
    </div>
    <div class="flex items-center gap-2">
      <button
        @click="goToPage(props.currentPage - 1)"
        :disabled="props.currentPage === 1"
        class="text-xs font-bold text-gray-500 hover:text-gray-700 disabled:opacity-30 px-2 py-1"
      >
        Sebelumnya
      </button>

      <div class="flex items-center gap-1">
        <template v-for="(page, index) in pageNumbers" :key="index">
          <span
            v-if="page === '...'"
            class="text-xs font-bold text-gray-500 px-2"
          >...</span>
          <button
            v-else
            @click="typeof page === 'number' && goToPage(page)"
            :class="[
              'w-7 h-7 rounded text-xs font-bold flex items-center justify-center',
              currentPage === page
                ? 'bg-blue-600 text-white'
                : 'bg-white text-gray-500 border border-gray-400 hover:bg-gray-50',
            ]"
          >
            {{ page }}
          </button>
        </template>
      </div>

      <button
        @click="goToPage(props.currentPage + 1)"
        :disabled="props.currentPage === totalPages"
        class="text-xs font-bold text-gray-500 hover:text-gray-700 disabled:opacity-30 px-2 py-1"
      >
        Selanjutnya
      </button>
    </div>
  </div>
</template>
