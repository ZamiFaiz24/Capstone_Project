<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarItem from '@/components/SidebarItem.vue';
import { BookOpen, Folder, LayoutGrid, House, Cable, Database, Boxes, ChartNoAxesCombined } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

const icons = {
    home: House,
    perangkat: Cable,
    sensor: Database,
    klaster: Boxes,
    visualisasi: ChartNoAxesCombined,
    laporanBI: BookOpen,
};

const footerNavItems = [
  {
    title: 'Github Repo',
    href: 'https://github.com/laravel/vue-starter-kit',
    icon: Folder,
    external: true,
  },
  {
    title: 'Documentation',
    href: 'https://laravel.com/docs/starter-kits#vue',
    icon: BookOpen,
    external: true,
  },
];
</script>

<template>
    <div class="w-[256px] h-screen bg-white fixed left-0 top-0 z-200 overflow-y-auto shadow-lg">
        <!-- Logo -->
        <div class="pt-6 pb-2 px-12">
        <h1 class="text-[28px] font-[800] text-blue-600 font-poppins">Endog</h1>
        </div>
        <!-- Navigation -->
        <div class="mt-2"></div>
        <!-- Utama Section -->
        <div class="px-12 py-4">
            <div class="text-sm font-semibold text-gray-800 border-b border-gray-800 pb-3">Utama</div>
        </div>
        <SidebarItem href="/dashboard" label="Dashboard" :icon="icons.home" />

        <!-- Manajemen Section -->
        <div class="px-12 py-4">
            <div class="text-sm font-semibold text-black border-b border-black pb-3">Manajemen</div>
        </div>
        <SidebarItem href="/perangkat" label="Perangkat" :icon="icons.perangkat" />
        <SidebarItem href="/data_sensor" label="Data Sensor" :icon="icons.sensor" />

        <!-- Analisis Section -->
        <div class="px-12 py-4">
            <div class="text-sm font-semibold text-black border-b border-black pb-3">Analisis</div>
        </div>
        <SidebarItem href="/klaster" label="Klasterisasi" :icon="icons.klaster" />
        <SidebarItem href="/visualisasi" label="Visualisasi" :icon="icons.visualisasi" />

        <!-- Laporan Section -->
        <div class="px-12 py-4">
            <div class="text-sm font-semibold text-black border-b border-black pb-3">Laporan</div>
        </div>
        <SidebarItem href="/laporan" label="Laporan BI" :icon="icons.laporanBI" />
    </div>
</template>
