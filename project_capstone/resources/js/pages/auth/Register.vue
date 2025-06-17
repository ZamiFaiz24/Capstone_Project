<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <div
    class="w-screen h-screen flex items-center justify-center bg-slate-100"
    style="background-image: url('/images/bg.jpg'); background-size: cover; background-position: center;"
  >
    <div class="w-[900px] h-[700px] rounded-[20px] bg-white shadow-[2px_6px_4px_0px_rgba(0,0,0,0.25)] flex">
    <!-- Left side image -->
    <img
      src="/images/telur2.jpg"
      alt="Fresh eggs in a basket"
      class="w-[446px] h-[700px] rounded-l-[20px] object-cover"
    />

    <!-- Right side form -->
    <div class="flex-1 relative px-[59px] py-[75px]">
      <!-- Title -->
      <h1 class="text-[36px] font-bold text-blue-600 font-poppins mb-4">
        Daftar
      </h1>

      <form @submit.prevent="submit" class="space-y-3">
        <!-- Name -->
        <div class="w-[300px] h-[80px] relative mb-3">
          <label
            for="name"
            class="absolute left-0 top-0 h-[22px] text-lg font-normal text-black font-inter"
          >
            Nama Pengguna
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-[300px] h-[50px] absolute left-0 top-[30px] rounded-[15px] border-2 border-blue-600 bg-white text-black shadow px-[15px] text-base font-inter outline-none focus:border-blue-600"
          />
        </div>

        <!-- Email -->
        <div class="w-[300px] h-[80px] relative mb-3">
          <label
            for="email"
            class="absolute left-0 top-0 h-[22px] text-lg font-normal text-black font-inter"
          >
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="w-[300px] h-[50px] absolute left-0 top-[30px] rounded-[15px] border-2 border-blue-600 bg-white text-black shadow px-[15px] text-base font-inter outline-none focus:border-blue-600"
          />
        </div>

        <!-- Password -->
        <div class="w-[300px] h-[80px] relative mb-3">
          <label
            for="password"
            class="absolute left-0 top-0 h-[22px] text-lg font-normal text-black font-inter"
          >
            Password
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="w-[300px] h-[50px] absolute left-0 top-[30px] rounded-[15px] border-2 border-blue-600 bg-white text-black shadow px-[15px] text-base font-inter outline-none focus:border-blue-600"
          />
        </div>

        <!-- Confirm Password -->
        <div class="w-[300px] h-[80px] relative mb-3">
          <label
            for="password_confirmation"
            class="absolute left-0 top-0 h-[22px] text-lg font-normal text-black font-inter"
          >
            Konfirmasi Password
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="w-[300px] h-[50px] absolute left-0 top-[30px] rounded-[15px] border-2 border-blue-600 bg-white text-black shadow px-[15px] text-base font-inter outline-none focus:border-blue-600"
          />
        </div>

        <!-- Register Button -->
        <div class="w-[200px] h-[50px] relative mx-auto mt-8 mb-4">
          <button
            type="submit"
            class="w-[200px] h-[50px] absolute left-0 top-0 rounded-[15px] bg-blue-600 border-none cursor-pointer flex items-center justify-center hover:bg-blue-700 transition-colors"
          >
            <span class="text-xl font-bold text-gray-100 font-inter">
              Daftar
            </span>
          </button>
        </div>

        <!-- Already have account link -->
        <div class="w-[116px] h-[17px] mx-auto">
          <Link
            href="/login"
            class="block text-sm font-bold text-blue-600 bg-transparent border-none cursor-pointer p-0 font-inter hover:text-blue-800 transition-colors w-[116px] h-[17px]"
          >
            Sudah ada akun?
          </Link>
        </div>
      </form>
    </div>
    </div>
  </div>
</template>
