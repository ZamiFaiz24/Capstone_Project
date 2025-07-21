<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Save, Eye, EyeOff } from 'lucide-vue-next'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const props = defineProps({
  role: String
})


const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)

const submit = () => {
  form.put(route(`${props.role}.password.update`), {
    onSuccess: () => {
      router.visit(route(`${props.role}.profile.edit`))
    },
    onError: (errors) => {
      console.error(errors)
    },
  })
}

</script>

<template>
  <AppLayout title="Ubah Password">
    <div class="max-w-md mx-auto bg-white shadow-md border border-blue-100 rounded-xl p-8 mt-12">
      <h1 class="text-2xl font-bold text-blue-700 mb-6 flex items-center gap-2">
        Ubah Password
      </h1>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Password Saat Ini -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Password Saat Ini</label>
          <div class="relative">
            <input
              v-model="form.current_password"
              :type="showCurrent ? 'text' : 'password'"
              autocomplete="current-password"
              class="input pr-10 text-black"
              placeholder="Password lama"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500"
              @click="showCurrent = !showCurrent"
              tabindex="-1"
            >
              <component :is="showCurrent ? Eye : EyeOff" class="w-5 h-5" />
            </button>
          </div>
          <div v-if="form.errors.current_password" class="text-red-500 text-sm mt-1">
            {{ form.errors.current_password }}
          </div>
        </div>

        <!-- Password Baru -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Password Baru</label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showNew ? 'text' : 'password'"
              autocomplete="new-password"
              class="input pr-10 text-black"
              placeholder="Password baru"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500"
              @click="showNew = !showNew"
              tabindex="-1"
            >
              <component :is="showNew ? Eye : EyeOff" class="w-5 h-5" />
            </button>
          </div>
          <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <!-- Konfirmasi Password Baru -->
        <div>
          <label class="block font-medium mb-1 text-gray-700">Konfirmasi Password Baru</label>
          <div class="relative">
            <input
              v-model="form.password_confirmation"
              :type="showConfirm ? 'text' : 'password'"
              autocomplete="new-password"
              class="input pr-10 text-black"
              placeholder="Ulangi password baru"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500"
              @click="showConfirm = !showConfirm"
              tabindex="-1"
            >
              <component :is="showConfirm ? Eye : EyeOff" class="w-5 h-5" />
            </button>
          </div>
        </div>

        <div class="pt-2">
          <button
            type="submit"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow transition"
            :disabled="form.processing"
          >
            <Save class="w-4 h-4" /> Simpan Password
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.input {
  width: 100%;
  border: 1.5px solid #cbd5e1;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 1rem;
  transition: border 0.2s;
  background: #f8fafc;
}
.input:focus {
  outline: none;
  border-color: #2563eb;
  background: #fff;
}
button[disabled] {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>