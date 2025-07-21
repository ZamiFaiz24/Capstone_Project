<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { User, Mail, Lock, Edit, Save, X } from 'lucide-vue-next'

const props = defineProps({
  user: Object,
})

const editField = ref(null) // 'name' | 'email' | null

const form = useForm({
  name: props.user.name,
  email: props.user.email,
})

const updateField = (field) => {
  form.put(route('user.profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      editField.value = null
    }
  })
}

</script>

<template>
  <AppLayout title="Profil Saya">
    <div class="w-full min-h-screen px-4 md:px-8 lg:px-16 pt-4">
      <!-- Page Title -->
      <div class="mb-2">
        <h1 class="text-xl font-bold text-blue-600 font-poppins">Profil</h1>
      </div>
      <div class="w-30 h-px bg-gray-400 mb-12"></div>

      
      <div class="bg-white rounded-2xl border border-blue-700 p-6 mb-8">
        <h1 class="text-2xl font-bold mb-8 text-blue-700 flex items-center gap-2">
          <User class="w-6 h-6 text-blue-600" /> Informasi Pengguna
        </h1>

        <!-- Flash Success -->
        <transition name="fade">
          <div
            v-if="$page.props.flash?.success"
            class="bg-green-100 text-green-800 px-4 py-2 rounded-md mb-4 text-center font-semibold border border-green-300"
          >
            {{ $page.props.flash.success }}
          </div>
        </transition>

        <!-- Info tambahan -->
        <div class="mb-4">
          <div class="block text-sm text-gray-500 font-medium">Role Pengguna</div>
          <div class="font-semibold text-blue-700">
            {{ props.user.role === 'admin' ? 'Admin' : 'User Biasa' }}
          </div>
        </div>
        <div class="mb-4">
          <div class="block text-sm text-gray-500 font-medium">Tanggal Gabung</div>
          <div class="font-semibold text-gray-700">
            {{ new Date(props.user.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
          </div>
        </div>
        <div v-if="props.user.last_login_at">
          <div class="text-xs text-gray-500">Terakhir Login</div>
          <div class="font-semibold text-gray-700">
            {{ new Date(props.user.last_login_at).toLocaleString('id-ID') }}
          </div>
        </div>

        <!-- NAMA -->
        <div class="mb-4">
          <label class="block text-sm text-gray-500 font-medium">Nama</label>
          <div v-if="editField !== 'name'" class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ props.user.name }}</div>
            <button @click="editField = 'name'" class="text-blue-600 hover:underline flex items-center gap-1 text-sm">
              <Edit class="w-4 h-4" /> Ubah
            </button>
          </div>
          <form v-else @submit.prevent="updateField('name')" class="flex text-black items-center gap-2 mt-2">
            <input
              v-model="form.name"
              class="input"
              type="text"
              placeholder="Nama baru"
              autocomplete="name"
            />
            <button type="submit" class="btn-blue flex items-center gap-1">
              <Save class="w-4 h-4" /> Simpan
            </button>
            <button type="button" @click="editField = null" class="btn-gray flex items-center gap-1">
              <X class="w-4 h-4" /> Batal
            </button>
          </form>
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- EMAIL -->
        <div class="mb-6">
          <label class="block text-sm text-gray-500 font-medium">Email</label>
          <div v-if="editField !== 'email'" class="flex justify-between items-center">
            <div class="font-semibold text-gray-700">{{ props.user.email }}</div>
            <button @click="editField = 'email'" class="text-blue-600 hover:underline flex items-center gap-1 text-sm">
              <Edit class="w-4 h-4" /> Ubah
            </button>
          </div>
          <form v-else @submit.prevent="updateField('email')" class="flex text-black items-center gap-2 mt-2">
            <input
              v-model="form.email"
              class="input"
              type="email"
              placeholder="Email baru"
              autocomplete="email"
            />
            <button type="submit" class="btn-blue flex items-center gap-1">
              <Save class="w-4 h-4" /> Simpan
            </button>
            <button type="button" @click="editField = null" class="btn-gray flex items-center gap-1">
              <X class="w-4 h-4" /> Batal
            </button>
          </form>
          <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
        </div>

        <!-- UBAH PASSWORD -->
        <div class="pt-3">
          <button
            @click="$inertia.visit('/user/reset-password')"
            class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-blue-700 rounded font-semibold border border-blue-300 transition"
          >
            <Lock class="w-4 h-4" /> Ubah Password
          </button>
        </div>
      </div>
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
  transition: border 0.2s, background-color 0.2s;
  background-color: #f8fafc;
}
.input:focus {
  outline: none;
  border-color: #2563eb;
  background-color: #fff;
}

.btn-blue {
  background-color: #2563eb;
  color: white;
  padding: 0.4rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  transition: background-color 0.2s;
}
.btn-blue:hover {
  background-color: #1e40af;
}

.btn-gray {
  background-color: #f3f4f6;
  color: #374151;
  padding: 0.4rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  border: 1px solid #cbd5e1;
  transition: background-color 0.2s;
}
.btn-gray:hover {
  background-color: #e5e7eb;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
