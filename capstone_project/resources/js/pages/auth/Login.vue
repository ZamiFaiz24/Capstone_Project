<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const login = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}

const handleForgotPassword = () => {
  window.alert('Fitur lupa password akan segera hadir!');
};
</script>

<template>
  <Head title="Login" />

  <div
    class="w-screen h-screen flex items-center justify-center bg-slate-100"
    style="background-image: url('/images/bg.jpg'); background-size: cover; background-position: center;"
  >
    <div class="w-[900px] h-[700px] rounded-[20px] bg-white shadow-[2px_6px_4px_0px_rgba(0,0,0,0.25)] flex">
      <!-- Left image -->
      <img
        src="/images/telur2.jpg"
        alt="Fresh eggs in a basket"
        class="w-[446px] h-[700px] rounded-l-[20px] object-cover"
      />

      <!-- Right form -->
      <div class="flex-1 relative px-[59px] py-[75px]">
        <h1 class="absolute left-[59px] top-[75px] w-[125px] h-[54px] m-0 text-[36px] font-bold text-blue-600 font-poppins-bold">
          Masuk
        </h1>

        <form @submit.prevent="login" class="flex flex-col gap-6 mt-32 ml-4">
          <!-- Email -->
          <div>
            <label for="email" class="block mb-1 text-lg font-normal text-black font-inter">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-[300px] h-[50px] rounded-[15px] border-2 border-blue-600 bg-white text-black shadow px-[15px] text-base font-inter outline-none focus:border-blue-600"
            />
            <span v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</span>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block mb-1 text-lg font-normal text-black font-inter">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-[300px] h-[50px] rounded-[15px] border-2 border-blue-600 bg-white text-black shadow px-[15px] text-base font-inter outline-none focus:border-blue-600"
            />
            <span v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</span>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center gap-3">
            <input
              id="remember-me"
              v-model="form.remember"
              type="checkbox"
              class="w-4 h-4 accent-blue-600"
            />
            <label for="remember-me" class="text-sm text-black cursor-pointer font-inter">
              Ingatkan Saya!
            </label>
          </div>

          <!-- Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-[200px] h-[50px] rounded-[15px] bg-blue-600 border-none cursor-pointer flex items-center justify-center hover:bg-blue-700 transition-colors mx-auto"
          >
            <span class="text-xl font-bold text-gray-100 font-inter">
              {{ form.processing ? 'Memuat...' : 'Masuk' }}
            </span>
          </button>

          <!-- Links -->
          <div>
            <button
              type="button"
              @click=handleForgotPassword
              class="block text-sm font-bold text-blue-600 bg-transparent border-none cursor-pointer mb-2 p-0 font-inter hover:text-blue-800 transition-colors"
            >
              Lupa password kamu?
            </button>
            <Link
              href="/register"
              class="block text-sm font-bold text-blue-600 bg-transparent border-none cursor-pointer p-0 font-inter hover:text-blue-800 transition-colors"
            >
              Buat akun baru
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
