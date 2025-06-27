<script setup lang="ts">
import { ref, defineExpose } from 'vue'

const isOpen = ref(false)

const open = () => {
  isOpen.value = true
}
const close = () => {
  isOpen.value = false
}
defineExpose({ open })

defineProps<{ onConfirm: () => void }>()
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-white/30 backdrop-blur-sm transition-all"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 border border-blue-200 relative animate-fadeIn">
      <!-- Icon -->
      <div class="flex justify-center mb-4">
        <svg class="w-14 h-14 text-blue-600" fill="none" viewBox="0 0 48 48">
          <rect width="48" height="48" rx="24" fill="#e0e7ff"/>
          <path d="M24 14v14m0 0l-5-5m5 5l5-5" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          <rect x="14" y="32" width="20" height="2.5" rx="1.25" fill="#2563eb"/>
        </svg>
      </div>
      <h3 class="font-bold text-2xl mb-2 text-blue-700 text-center font-poppins">Konfirmasi Ekspor</h3>
      <p class="mb-8 text-gray-600 text-center text-base">Apakah Anda yakin ingin mengekspor data ini ke file Excel?</p>
      <div class="flex justify-end gap-3">
        <button
          class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition font-semibold"
          @click="close"
        >Batal</button>
        <button
          class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow transition flex items-center gap-2"
          @click="() => { onConfirm(); close(); }"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 4v16m0 0l-6-6m6 6l6-6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Ekspor
        </button>
      </div>
      <!-- Close button (optional) -->
      <button
        class="absolute top-3 right-3 text-gray-400 hover:text-blue-600 transition"
        @click="close"
        aria-label="Tutup"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M6 6l12 12M6 18L18 6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px);}
  to { opacity: 1; transform: translateY(0);}
}
.animate-fadeIn {
  animation: fadeIn 0.3s;
}
</style>
